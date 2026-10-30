<?php

namespace App\Services\CpbnBot;

use App\Models\BiicfBdqfLevel;
use App\Models\BiicfCompetency;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\SectionGuide;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use App\Services\Business\FeatureUsageService;
use App\Support\CpbnBotKnowledge;
use Carbon\CarbonInterface;

/**
 * Everything the CPBN Bot is given to answer a question.
 *
 * The website itself (its pages, the '?' guides an administrator
 * writes, the account rules) and where this student has got to,
 * so "what should I do next?" has a real answer. It deliberately
 * leaves out the career detail the Career Adviser is given.
 */
class CpbnBotContextBuilder
{
    public function __construct(
        private EntitlementService $entitlements,
        private FeatureUsageService $featureUsage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $student, ?string $currentPage = null): array
    {
        $pages = CpbnBotKnowledge::pages();

        return [
            'schema_version' => '1.1',
            'today' => now()->timezone(config('app.business_timezone'))->toDateString(),

            /* Where the student is asking from, so the bot does not
             * send them to the page they are already on. */
            'current_page' => isset($pages[$currentPage])
                ? ['key' => $currentPage, 'name' => $pages[$currentPage]['name']]
                : null,
            'pages' => collect(CpbnBotKnowledge::pages())
                ->map(fn (array $page, string $key) => [
                    'key' => $key,
                    'name' => $page['name'],
                    'purpose' => $page['purpose'],
                    'how_to' => $page['how_to'],
                ])
                ->values()
                ->all(),
            'section_guides' => SectionGuide::query()
                ->orderBy('id')
                ->get()
                ->map(fn (SectionGuide $guide) => [
                    'page' => $guide->pageLabel(),
                    'section' => $guide->title,
                    'help' => $guide->body,
                ])
                ->all(),
            'rules' => CpbnBotKnowledge::rules(),
            'biicf' => $this->framework(),
            'student' => $this->progress($student),
        ];
    }

    /**
     * The BIICF and BDQF themselves, so the bot can explain them
     * rather than only point at the pages that do.
     *
     * Kept to names and short definitions: it explains the
     * framework, it does not advise on a career within it.
     *
     * @return array<string, mixed>
     */
    private function framework(): array
    {
        return [
            'bdqf_levels' => BiicfBdqfLevel::query()
                ->orderBy('level')
                ->get()
                ->map(fn (BiicfBdqfLevel $level) => array_filter([
                    'level' => $level->level,
                    'schools' => $level->schools_sector,
                    'technical_and_vocational' => $level->tvet_sector,
                    'higher_education' => $level->higher_education_sector,
                    'notes' => $level->notes,
                ], fn ($value) => filled($value)))
                ->values()
                ->all(),
            'proficiency_scales' => BiicfProficiencyLevel::query()
                ->orderBy('scale')
                ->orderBy('level_number')
                ->get()
                ->groupBy('scale')
                ->map(fn ($levels) => $levels->map(fn (BiicfProficiencyLevel $level) => [
                    'level' => $level->level_number,
                    'name' => $level->name,
                    'description' => $level->description,
                ])->values())
                ->all(),
            'sub_sectors' => BiicfSubSector::query()
                ->with([
                    'jobRoles:id,sub_sector_id,title,career_path_level',
                    'jobRoles.tiers:id,job_role_id,label,sort_order',
                ])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (BiicfSubSector $subSector) => [
                    'name' => $subSector->name,
                    'job_roles' => $subSector->jobRoles
                        ->map(fn ($role) => [
                            'title' => $role->title,

                            /* The "Required at" buttons a link can open. */
                            'tiers' => $role->tiers->pluck('label')->filter()->values()->all(),
                        ])
                        ->values()
                        ->all(),
                ])
                ->all(),
            'competencies' => BiicfCompetency::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['name', 'type', 'short_definition'])
                ->map(fn (BiicfCompetency $competency) => array_filter([
                    'name' => $competency->name,
                    'type' => $competency->type,
                    'in_short' => $competency->short_definition,
                ]))
                ->values()
                ->all(),
        ];
    }

    /**
     * Where the student has got to.
     *
     * @return array<string, mixed>
     */
    private function progress(User $student): array
    {
        $access = $this->entitlements->accessFor($student);

        $subscription = $student->planGrants()
            ->currentlyActive()
            ->where('source', 'self')
            ->latest('id')
            ->first();

        $generations = $this->featureUsage->status(
            $student,
            'career_recommendations.generation_quota'
        );

        $milestones = $student->milestones()->get();

        return [
            'first_name' => strtok((string) $student->name, ' '),
            'programme' => $student->programme,
            'student_id' => [
                'entered' => filled($student->student_id),
                'verified' => $student->hasVerifiedStudentId(),
            ],
            'plan' => [
                'name' => $access['plan']?->name ?? 'Free',
                'premium_from' => $this->premiumSource($access, $subscription),
                'subscription_ends' => $this->day($subscription?->ends_at),
            ],
            'profile' => [
                'completion_percent' => (int) $student->profile_completion,
                'sections_still_to_fill' => $student->unfinishedProfileSections(),
                'projects' => $student->projects()->count(),
                'certifications' => $student->certifications()->count(),
            ],
            'recommendations' => [
                'current_matches' => $student->currentRecommendations()->count(),
                'can_generate_now' => $generations['allowed'],
                'generations_left' => $generations['remaining'],
                'generation_limit' => $generations['amount'],
            ],
            'milestones' => [
                'total' => $milestones->count(),
                'completed' => $milestones->where('is_completed', true)->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $access
     */
    private function premiumSource(array $access, ?UserPlanGrant $subscription): ?string
    {
        if (($access['plan']?->code ?? 'free') !== 'premium') {
            return null;
        }

        if ($subscription !== null) {
            return 'their own monthly subscription';
        }

        return $access['source'] === 'sponsored'
            ? 'a sponsor'
            : 'an administrator or trial';
    }

    private function day(?CarbonInterface $date): ?string
    {
        return $date?->copy()->timezone(config('app.business_timezone'))->format('j M Y');
    }
}
