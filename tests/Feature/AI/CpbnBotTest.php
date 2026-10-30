<?php

use App\Contracts\CpbnBotClient;
use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\CareerAdviserConversation;
use App\Models\CpbnBotConversation;
use App\Models\CpbnBotMessage;
use App\Models\FeatureUsage;
use App\Models\Plan;
use App\Models\User;
use App\Services\Business\FeatureUsageService;
use App\Services\CpbnBot\CpbnBotContextBuilder;
use Database\Seeders\BiicfReferenceDataSeeder;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

/*
 * The CPBN Bot: the guide to the website, in a panel on every
 * student page. Separate from the Career Adviser, with its own
 * conversations and a token allowance per plan.
 */

const CPBN_TOKENS = 'cpbn_bot.token_quota';

/**
 * A bot that answers at once and reports the tokens it used, as
 * Groq does. Records what it was given.
 */
function cpbnClient(?int $tokens = 1234, array $links = ['profile_edit'], string|array|null $navigate = null): CpbnBotClient
{
    $client = new class($tokens, $links, $navigate) implements CpbnBotClient
    {
        public int $calls = 0;

        public array $lastContext = [];

        public array $lastHistory = [];

        public function __construct(private ?int $tokens, private array $links, private string|array|null $navigate) {}

        public function ask(array $context, array $history, string $message): array
        {
            $this->calls++;
            $this->lastContext = $context;
            $this->lastHistory = $history;

            return [
                'schema_version' => '1.0',
                'status' => 'completed',
                'message' => 'Open Edit Profile and fill in Qualifications.',
                'links' => $this->links,
                'navigate' => $this->navigate,
                'usage' => $this->tokens === null ? null : ['total_tokens' => $this->tokens],
            ];
        }
    };

    app()->instance(CpbnBotClient::class, $client);

    return $client;
}

function cpbnStudent(bool $withHistory = false): User
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);

    if ($withHistory) {
        Plan::where('code', 'free')->sole()
            ->features()
            ->where('key', 'cpbn_bot.history.enabled')
            ->update(['value' => json_encode(true)]);
    }

    return User::factory()->create(['role' => 'student']);
}

function setCpbnTokenLimit(array $quota): void
{
    Plan::where('code', 'free')->sole()
        ->features()
        ->where('key', CPBN_TOKENS)
        ->update(['value' => json_encode($quota)]);
}

function askCpbn(User $student, string $message = 'How do I fill in my profile?', array $extra = [])
{
    return test()->actingAs($student)
        ->postJson(route('student.cpbn-bot.ask'), ['message' => $message] + $extra);
}

test('the admin can set each plan\'s CPBN Bot token limit', function () {
    cpbnStudent();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.business.plans.index'))
        ->assertOk()
        ->assertSee('CPBN Bot Token Limit')
        ->assertSee('CPBN Bot History');
});

test('a reply is charged the tokens the AI reported', function () {
    $student = cpbnStudent();
    cpbnClient(1234);

    askCpbn($student)
        ->assertOk()
        ->assertJsonPath('tokens_used', 1234)
        ->assertJsonPath('tokens.used', 1234)
        ->assertJsonPath('tokens.limit', 50000)
        ->assertJsonPath('tokens.period', 'the last day');

    expect((int) FeatureUsage::where('feature_key', CPBN_TOKENS)->sum('quantity'))->toBe(1234)
        ->and(CpbnBotConversation::where('user_id', $student->id)->sole()->messages()->get()->last()->tokens)->toBe(1234);
});

test('without a reported count the tokens are estimated', function () {
    $student = cpbnStudent();
    cpbnClient(null);

    askCpbn($student)->assertOk();

    expect((int) FeatureUsage::where('feature_key', CPBN_TOKENS)->sum('quantity'))->toBeGreaterThan(800);
});

test('a reply links to the pages it mentions, and only real ones', function () {
    $student = cpbnStudent();
    cpbnClient(links: ['profile_edit', 'nowhere', 'settings']);

    askCpbn($student)
        ->assertOk()
        ->assertJsonCount(2, 'links')
        ->assertJsonPath('links.0.label', 'Edit Profile')
        ->assertJsonPath('links.0.url', route('student.profile.edit'))
        ->assertJsonPath('links.1.key', 'settings');
});

test('a student over the token limit is refused without asking the AI', function () {
    $student = cpbnStudent();
    $client = cpbnClient();

    setCpbnTokenLimit(['mode' => 'recurring', 'amount' => 1000, 'period_value' => 1, 'period_unit' => 'day']);

    app(FeatureUsageService::class)->recordUsage($student, CPBN_TOKENS, 1000);

    askCpbn($student)
        ->assertStatus(429)
        ->assertJsonPath('tokens.allowed', false)
        ->assertJsonPath('tokens.percent_used', 100);

    expect($client->calls)->toBe(0);
});

test('the allowance comes back as old usage leaves the window', function () {
    $student = cpbnStudent();
    cpbnClient();

    setCpbnTokenLimit(['mode' => 'recurring', 'amount' => 1000, 'period_value' => 1, 'period_unit' => 'day']);

    app(FeatureUsageService::class)->recordUsage($student, CPBN_TOKENS, 1200);

    $status = app(FeatureUsageService::class)->status($student, CPBN_TOKENS);

    expect($status['allowed'])->toBeFalse()
        ->and($status['next_available_at']->toDateString())->toBe(now()->addDay()->toDateString());

    $this->travel(25)->hours();

    askCpbn($student)->assertOk();
});

test('quotas counted one per use are unchanged', function () {
    $student = cpbnStudent();

    $usage = app(FeatureUsageService::class);
    $usage->recordUsage($student, 'career_recommendations.generation_quota');

    expect($usage->status($student, 'career_recommendations.generation_quota')['used'])->toBe(1)
        ->and(FeatureUsage::sole()->quantity)->toBe(1);
});

test('the bot is separate from the Career Adviser', function () {
    $student = cpbnStudent();
    cpbnClient();

    askCpbn($student)->assertOk();

    expect(CpbnBotConversation::count())->toBe(1)
        ->and(CareerAdviserConversation::count())->toBe(0)
        ->and(FeatureUsage::where('feature_key', 'like', 'career_adviser.%')->count())->toBe(0);
});

test('the bot is given the website, the guides and the student\'s progress', function () {
    $student = cpbnStudent();
    $client = cpbnClient();

    askCpbn($student)->assertOk();

    $context = $client->lastContext;

    expect(collect($context['pages'])->pluck('key'))->toContain('profile_edit', 'settings', 'career_adviser')
        ->and(collect($context['section_guides'])->pluck('section'))->toContain('Skills & Competencies')
        ->and($context['rules'])->toHaveKeys(['registration', 'premium', 'advertising'])
        ->and($context['student']['plan']['name'])->toBe('Free')
        ->and($context['student']['profile']['sections_still_to_fill'])->toContain('Competencies');
});

test('the bot knows a subscriber\'s end date', function () {
    $student = cpbnStudent();

    $this->actingAs($student)->post(route('student.settings.upgrade'));

    $progress = app(CpbnBotContextBuilder::class)->build($student->fresh())['student'];

    expect($progress['plan']['premium_from'])->toBe('their own monthly subscription')
        ->and($progress['plan']['subscription_ends'])
        ->toBe(now()->addMonth()->timezone(config('app.business_timezone'))->format('j M Y'));
});

test('earlier messages in the conversation are replayed', function () {
    $student = cpbnStudent();
    $client = cpbnClient();

    askCpbn($student, 'First?');
    askCpbn($student, 'And then?');

    expect($client->lastHistory)->toHaveCount(2)
        ->and($client->lastHistory[0]['content'])->toBe('First?');
});

test('the panel reports the token ring', function () {
    $student = cpbnStudent();
    cpbnClient(12500);

    askCpbn($student);

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.panel'))
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('history_enabled', false)
        ->assertJsonPath('tokens.used', 12500)
        ->assertJsonPath('tokens.percent_used', 25)
        ->assertJsonPath('conversation', null);
});

test('a new conversation is titled after its first question', function () {
    $student = cpbnStudent();
    $client = cpbnClient();

    askCpbn($student, 'First topic?');
    askCpbn($student, 'A different topic entirely?', ['new_conversation' => true])->assertOk();

    $conversations = CpbnBotConversation::where('user_id', $student->id)->orderBy('id')->get();

    expect($conversations)->toHaveCount(2)
        ->and($conversations[1]->title)->toBe('A different topic entirely?')
        ->and($client->lastHistory)->toBe([]);
});

test('with history a student lists and reopens past conversations', function () {
    $student = cpbnStudent(withHistory: true);
    cpbnClient();

    $first = askCpbn($student, 'First topic?')->json('conversation_id');
    askCpbn($student, 'Second topic?', ['new_conversation' => true]);

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.conversations'))
        ->assertOk()
        ->assertJsonCount(2, 'conversations')
        ->assertJsonPath('conversations.0.title', 'Second topic?');

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.conversation', $first))
        ->assertOk()
        ->assertJsonPath('conversation.title', 'First topic?')
        ->assertJsonCount(2, 'conversation.messages')
        ->assertJsonPath('conversation.messages.1.links.0.key', 'profile_edit');

    askCpbn($student, 'Back to the first?', ['conversation_id' => $first])
        ->assertJsonPath('conversation_id', $first);

    expect(CpbnBotConversation::find($first)->message_count)->toBe(4);
});

test('the panel opens a history student on their current conversation', function () {
    $student = cpbnStudent(withHistory: true);
    cpbnClient();

    askCpbn($student, 'Where do I start?');

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.panel'))
        ->assertJsonPath('history_enabled', true)
        ->assertJsonPath('conversation.title', 'Where do I start?')
        ->assertJsonCount(2, 'conversation.messages');
});

test('without history past conversations cannot be listed or reopened', function () {
    $student = cpbnStudent();
    cpbnClient();

    $first = askCpbn($student, 'First topic?')->json('conversation_id');
    $second = askCpbn($student, 'Second topic?', ['new_conversation' => true])->json('conversation_id');

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.conversations'))
        ->assertForbidden();

    /* The question goes into the current conversation instead. */
    askCpbn($student, 'Third?', ['conversation_id' => $first])
        ->assertJsonPath('conversation_id', $second);

    expect(CpbnBotConversation::find($first)->message_count)->toBe(2);
});

test('a student cannot open someone else\'s conversation', function () {
    $owner = cpbnStudent(withHistory: true);
    cpbnClient();

    $theirs = askCpbn($owner, 'Private question?')->json('conversation_id');

    $other = User::factory()->create(['role' => 'student']);

    $this->actingAs($other)
        ->getJson(route('student.cpbn-bot.conversation', $theirs))
        ->assertNotFound();

    askCpbn($other, 'Sneaky?', ['conversation_id' => $theirs])
        ->assertNotFound();
});

test('the CPBN Bot button uses the CareerPath badge', function () {
    $student = cpbnStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('id="cpbnBotToggle"', false)
        ->assertSee('images/careerpath-badge.png', false)
        ->assertSee(route('student.cpbn-bot.ask'), false);
});

test('the Career Adviser keeps its own page', function () {
    $student = cpbnStudent();

    $this->actingAs($student)
        ->get(route('student.career-adviser'))
        ->assertOk()
        ->assertDontSee('id="cpbnBotToggle"', false);
});

test('the mock bot answers from the page a question is about', function () {
    $student = cpbnStudent();

    askCpbn($student, 'How do I upgrade to Premium?')
        ->assertOk()
        ->assertJsonPath('links.0.key', 'settings');
});

test('a reply never links to the page the student is already on', function () {
    $student = cpbnStudent();
    $client = cpbnClient(links: ['profile_edit', 'settings']);

    askCpbn($student, 'How do I fill in competencies?', ['current_page' => 'profile_edit'])
        ->assertOk()
        ->assertJsonCount(1, 'links')
        ->assertJsonPath('links.0.key', 'settings');

    expect($client->lastContext['current_page'])->toBe(['key' => 'profile_edit', 'name' => 'Edit Profile']);
});

test('the bot knows the BIICF and the BDQF, not only the pages', function () {
    $student = cpbnStudent();
    $client = cpbnClient();

    askCpbn($student, 'What are BDQF levels?')->assertOk();

    expect($client->lastContext['biicf'])->toHaveKeys(['bdqf_levels', 'proficiency_scales', 'sub_sectors', 'competencies'])
        ->and($client->lastContext['rules'])->toHaveKey('framework')
        ->and(implode(' ', collect($client->lastContext['pages'])->firstWhere('key', 'profile_edit')['how_to']))
        ->toContain('What are BDQF levels?');
});

test('the panel knows which page it was opened on', function () {
    $student = cpbnStudent();

    $this->actingAs($student)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('data-current-page="profile_edit"', false);

    $this->actingAs($student)
        ->get(route('student.milestones'))
        ->assertOk()
        ->assertSee('data-current-page="milestones"', false);
});

test('the button is the badge alone', function () {
    $this->actingAs(cpbnStudent())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('class="cpbn-bot-fab"', false)
        ->assertDontSee('<span>CPBN Bot</span>', false);
});

test('the plan summary describes the token limit in tokens', function () {
    $this->actingAs(cpbnStudent())
        ->get(route('student.settings'))
        ->assertOk()
        ->assertSeeText('50,000 tokens every 1 day(s)');
});

/**
 * A small slice of the framework to link into: IT Technician in
 * IT Services, with two tiers, and one competency.
 */
function cpbnExplorerFixture(): array
{
    $subSector = BiicfSubSector::create(['name' => 'IT Services', 'slug' => 'it-services']);

    $role = BiicfJobRole::create([
        'title' => 'IT Technician',
        'slug' => 'it-technician',
        'sub_sector_id' => $subSector->id,
    ]);

    foreach (['Associate', 'Expert/Management'] as $order => $label) {
        BiicfJobRoleTier::create(['job_role_id' => $role->id, 'label' => $label, 'sort_order' => $order + 1]);
    }

    $competency = BiicfCompetency::create([
        'name' => 'Business Agility',
        'slug' => 'business-agility',
        'type' => 'technical',
    ]);

    return [$subSector, $role, $competency];
}

function explorerLink(array $fields): array
{
    return array_merge([
        'page' => 'biicf_explorer',
        'explorer_section' => '',
        'sub_sector' => '',
        'job_role' => '',
        'tier' => '',
        'competency' => '',
        'proficiency_level' => '',
    ], $fields);
}

test('a link can open a role\'s competencies at one tier', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();

    cpbnClient(links: [explorerLink([
        'explorer_section' => 'competencies',
        'job_role' => 'IT Technician',
        'tier' => 'expert',
    ])]);

    $link = askCpbn($student, 'IT Technician competencies at expert level?')->assertOk()->json('links.0');

    expect($link['label'])->toBe('IT Technician › Competencies › Expert/Management')
        ->and($link['url'])->toBe(route('student.biicf-explorer.index', [
            'section' => 'competencies',
            'role' => 'it-technician',
            'tier' => 'Expert/Management',
        ]));
});

test('a link can open one competency, at a level', function () {
    $student = cpbnStudent();
    [, , $competency] = cpbnExplorerFixture();

    cpbnClient(links: [explorerLink(['competency' => 'business agility', 'proficiency_level' => '3'])]);

    askCpbn($student, 'Where is Business Agility?')
        ->assertOk()
        ->assertJsonPath('links.0.label', 'Business Agility › Level 3')
        ->assertJsonPath('links.0.url', route('student.biicf-explorer.index', ['competency' => $competency->id, 'level' => 3]));
});

test('a role\'s entry requirements and a sub-sector\'s roles can be linked', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();

    cpbnClient(links: [
        explorerLink(['explorer_section' => 'entry-requirements', 'job_role' => 'IT Technician']),
        explorerLink(['explorer_section' => 'job-roles', 'sub_sector' => 'IT Services']),
    ]);

    askCpbn($student)
        ->assertOk()
        ->assertJsonPath('links.0.label', 'IT Technician › Entry Requirements')
        ->assertJsonPath('links.1.label', 'IT Services › Job Roles')
        ->assertJsonPath('links.1.url', route('student.biicf-explorer.index', ['section' => 'job-roles', 'sub_sector' => 'it-services']));
});

test('anything a link names that does not exist is dropped, not guessed', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();

    cpbnClient(links: [explorerLink([
        'explorer_section' => 'competencies',
        'job_role' => 'Chief Wizard',
        'tier' => 'Grandmaster',
        'competency' => 'Telepathy',
    ])]);

    /* No role, so the role-only tab falls away too: the glossary. */
    askCpbn($student)
        ->assertOk()
        ->assertJsonPath('links.0.label', 'Competencies')
        ->assertJsonPath('links.0.url', route('student.biicf-explorer.index', ['section' => 'competencies']));
});

test('a deep link is kept even on the BIICF Explorer itself', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();

    cpbnClient(links: [
        'biicf_explorer',
        explorerLink(['explorer_section' => 'competencies', 'job_role' => 'IT Technician']),
    ]);

    askCpbn($student, 'Show me its competencies', ['current_page' => 'biicf_explorer'])
        ->assertOk()
        ->assertJsonCount(1, 'links')
        ->assertJsonPath('links.0.label', 'IT Technician › Competencies');
});

test('the bot knows each role\'s tiers', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();
    $client = cpbnClient();

    askCpbn($student)->assertOk();

    $role = collect($client->lastContext['biicf']['sub_sectors'])->firstWhere('name', 'IT Services')['job_roles'][0];

    expect($role)->toBe(['title' => 'IT Technician', 'tiers' => ['Associate', 'Expert/Management']]);
});

test('the explorer opens the place a link names', function () {
    $this->actingAs(cpbnStudent())
        ->get(route('student.biicf-explorer.index', ['section' => 'competencies', 'role' => 'it-technician']))
        ->assertOk()
        ->assertSee('openFromLink()', false)
        ->assertSee('BIICF_SUB_SECTORS', false);
});

test('a student can delete one of their conversations', function () {
    $student = cpbnStudent(withHistory: true);
    cpbnClient();

    $id = askCpbn($student, 'Delete me?')->json('conversation_id');

    $this->actingAs($student)
        ->deleteJson(route('student.cpbn-bot.conversation.destroy', $id))
        ->assertOk();

    expect(CpbnBotConversation::find($id))->toBeNull()
        ->and(CpbnBotMessage::where('cpbn_bot_conversation_id', $id)->count())->toBe(0)
        ->and((int) FeatureUsage::where('feature_key', CPBN_TOKENS)->sum('quantity'))->toBe(1234);
});

test('a student cannot delete someone else\'s conversation', function () {
    $owner = cpbnStudent(withHistory: true);
    cpbnClient();

    $id = askCpbn($owner, 'Mine?')->json('conversation_id');

    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->deleteJson(route('student.cpbn-bot.conversation.destroy', $id))
        ->assertNotFound();

    expect(CpbnBotConversation::find($id))->not->toBeNull();
});

test('asking to go somewhere returns where to take the student', function () {
    $student = cpbnStudent();
    cpbnClient(links: [], navigate: 'milestones');

    askCpbn($student, 'Take me to my milestones')
        ->assertOk()
        ->assertJsonPath('navigate.label', 'Milestones')
        ->assertJsonPath('navigate.url', route('student.milestones'))
        ->assertJsonPath('links.0.key', 'milestones');
});

test('the bot can take the student to an exact place in the explorer', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();

    cpbnClient(links: [], navigate: explorerLink([
        'explorer_section' => 'competencies',
        'job_role' => 'IT Technician',
        'tier' => 'Expert/Management',
    ]));

    askCpbn($student, 'Open IT Technician competencies at expert level', ['current_page' => 'biicf_explorer'])
        ->assertOk()
        ->assertJsonPath('navigate.label', 'IT Technician › Competencies › Expert/Management')
        ->assertJsonPath('navigate.url', route('student.biicf-explorer.index', [
            'section' => 'competencies',
            'role' => 'it-technician',
            'tier' => 'Expert/Management',
        ]));
});

test('a how-to answer does not move the student', function () {
    $student = cpbnStudent();
    cpbnClient(navigate: explorerLink(['page' => '']));

    askCpbn($student, 'How do I add a milestone?')
        ->assertOk()
        ->assertJsonPath('navigate', null);
});

test('the student is not sent to the page they are already on', function () {
    $student = cpbnStudent();
    cpbnClient(links: [], navigate: 'settings');

    askCpbn($student, 'Open settings', ['current_page' => 'settings'])
        ->assertOk()
        ->assertJsonPath('navigate', null)
        ->assertJsonCount(0, 'links');
});

test('a place that does not exist is not navigated to', function () {
    $student = cpbnStudent();
    cpbnClient(links: [], navigate: ['page' => 'secret_admin_page']);

    askCpbn($student, 'Take me to the secret page')
        ->assertOk()
        ->assertJsonPath('navigate', null);
});

test('the way there stays in the conversation as a button', function () {
    $student = cpbnStudent(withHistory: true);
    cpbnClient(links: [], navigate: 'milestones');

    $id = askCpbn($student, 'Take me to my milestones')->json('conversation_id');

    $this->actingAs($student)
        ->getJson(route('student.cpbn-bot.conversation', $id))
        ->assertJsonPath('conversation.messages.1.links.0.key', 'milestones');
});

test('the mock bot takes the student where they ask', function () {
    $student = cpbnStudent();

    askCpbn($student, 'Take me to my milestones')
        ->assertOk()
        ->assertJsonPath('navigate.url', route('student.milestones'));

    askCpbn($student, 'How do I add a milestone?')
        ->assertOk()
        ->assertJsonPath('navigate', null);
});

test('the panel checks for unsaved changes before taking the student away', function () {
    $this->actingAs(cpbnStudent())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('function hasUnsavedChanges()', false)
        ->assertSee('leaveWithConfirmation(place, target)', false);
});

test('a role the student names comes with its competencies per tier', function () {
    $student = cpbnStudent();
    $this->seed(BiicfReferenceDataSeeder::class);
    [, $role, $competency] = cpbnExplorerFixture();

    $tier = $role->tiers()->where('label', 'Expert/Management')->sole();
    $level = BiicfProficiencyLevel::where('scale', 'technical')->where('level_number', 4)->sole();

    $tier->competencies()->attach($competency->id, [
        'job_role_id' => $role->id,
        'proficiency_level_id' => $level->id,
        'is_core' => true,
    ]);

    $client = cpbnClient();

    askCpbn($student, 'What are all the IT Technician competencies?')->assertOk();

    $focus = $client->lastContext['focus'];

    expect($focus['job_roles'])->toHaveCount(1)
        ->and($focus['job_roles'][0]['title'])->toBe('IT Technician')
        ->and($focus['job_roles'][0]['tiers'][1]['label'])->toBe('Expert/Management')
        ->and($focus['job_roles'][0]['tiers'][1]['competencies'][0])->toBe([
            'name' => 'Business Agility',
            'type' => 'technical',
            'required_level' => $level->name.' (4 of 5)',
        ]);
});

test('a follow-up still finds the role named earlier', function () {
    $student = cpbnStudent();
    cpbnExplorerFixture();
    $client = cpbnClient();

    askCpbn($student, 'What does an IT Technician need?');
    askCpbn($student, 'Can you take me there?');

    expect(collect($client->lastContext['focus']['job_roles'])->pluck('title')->all())->toBe(['IT Technician']);
});

test('a competency the student names comes with its meaning', function () {
    $student = cpbnStudent();
    [, , $competency] = cpbnExplorerFixture();
    $competency->update(['short_definition' => 'Responding quickly to change.']);

    $client = cpbnClient();

    askCpbn($student, 'what is business agility?')->assertOk();

    expect($client->lastContext['focus']['competencies'][0])->toMatchArray([
        'name' => 'Business Agility',
        'in_short' => 'Responding quickly to change.',
    ])->and($client->lastContext['focus']['job_roles'])->toBe([]);
});
