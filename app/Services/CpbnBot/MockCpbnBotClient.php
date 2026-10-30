<?php

namespace App\Services\CpbnBot;

use App\Contracts\CpbnBotClient;
use App\Support\CpbnBotKnowledge;
use Illuminate\Support\Str;

/**
 * Answers without an AI service, for development and tests.
 *
 * It finds the page a question is about by its name or a few
 * common words, and answers from that page's own description.
 */
class MockCpbnBotClient implements CpbnBotClient
{
    /**
     * Words that point to a page, beyond its name.
     *
     * @var array<string, array<int, string>>
     */
    private const KEYWORDS = [
        'profile_edit' => ['edit', 'fill', 'qualification', 'skill', 'competenc', 'interest', 'project', 'aspiration', 'complete'],
        'recommendations' => ['recommend', 'generate', 'match'],
        'career_adviser' => ['career', 'adviser', 'advice', 'job'],
        'milestones' => ['milestone', 'goal', 'proof'],
        'settings' => ['premium', 'upgrade', 'subscri', 'cancel', 'password', 'delete', 'advert', 'ads', 'plan'],
        'biicf_explorer' => ['biicf', 'explorer', 'role', 'sub-sector'],
        'history' => ['history', 'past'],
        'innovation_lab' => ['innovation', 'lab'],
        'notifications' => ['notification'],
        'dashboard' => ['dashboard', 'home', 'next'],
    ];

    public function ask(array $context, array $history, string $message): array
    {
        $question = Str::lower($message);
        $pages = CpbnBotKnowledge::pages();

        $key = collect(self::KEYWORDS)
            ->keys()
            ->first(fn (string $key) => Str::contains($question, Str::lower($pages[$key]['name']))
                || Str::contains($question, self::KEYWORDS[$key]));

        if ($key === null) {
            return $this->reply(
                'I can help you find your way around CareerPath BN: editing your profile, generating recommendations, milestones, your plan and settings. What would you like to do?',
                ['dashboard']
            );
        }

        $page = $pages[$key];

        if (preg_match('/\b(take me|go to|open|navigate|bring me|show me)\b/', $question)) {
            return $this->reply('Taking you to '.$page['name'].'.', [], $key);
        }

        $steps = collect($page['how_to'])
            ->map(fn (string $step, int $index) => ($index + 1).'. '.$step)
            ->implode("\n");

        return $this->reply(
            $page['name'].': '.$page['purpose']."\n\n".$steps,
            [$key]
        );
    }

    /**
     * @param  array<int, string>  $links
     * @return array<string, mixed>
     */
    private function reply(string $message, array $links, string $navigate = ''): array
    {
        return [
            'schema_version' => '1.0',
            'status' => 'completed',
            'message' => $message,
            'links' => $links,
            'navigate' => $navigate === '' ? null : $navigate,
            'usage' => null,
        ];
    }
}
