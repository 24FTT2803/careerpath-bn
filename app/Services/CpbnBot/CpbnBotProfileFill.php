<?php

namespace App\Services\CpbnBot;

use App\Http\Controllers\Student\ProfileController;
use App\Models\BiicfBdqfLevel;
use App\Models\BiicfCompetency;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Filling in Edit Profile from a conversation with the CPBN Bot.
 *
 * The bot collects answers in the chat and hands back what to put
 * in the form. Nothing here saves anything: the panel writes the
 * values into the Edit Profile form on the page, and the student
 * checks them and presses Save as they always would.
 *
 * What the bot hands back is checked against the framework and the
 * form's own limits, so the form is never given a competency that
 * does not exist, a level off its scale, or text it would refuse.
 * It only ever adds or changes: nothing is unticked or removed.
 */
class CpbnBotProfileFill
{
    /**
     * The sections the bot can fill, by key, with the heading the
     * student sees and the section's anchor on the page.
     *
     * @return array<string, array{title: string, anchor: string}>
     */
    public static function sections(): array
    {
        return [
            'personal' => ['title' => 'Personal Information', 'anchor' => 'ep-personal'],
            'qualifications' => ['title' => 'Qualifications', 'anchor' => 'ep-qualifications'],
            'skills' => ['title' => 'Skills & Competencies', 'anchor' => 'ep-skills'],
            'interests' => ['title' => 'Interests & Preferences', 'anchor' => 'ep-interests'],
            'projects' => ['title' => 'Projects & Experience', 'anchor' => 'ep-projects'],
            'certifications' => ['title' => 'Certifications', 'anchor' => 'ep-certifications'],
            'aspirations' => ['title' => 'Career Aspirations', 'anchor' => 'ep-aspirations'],
        ];
    }

    /**
     * The section a '?' guide belongs to, when the bot can fill it.
     */
    public static function sectionForGuide(string $guideKey): ?string
    {
        return [
            'profile.personal' => 'personal',
            'profile.qualifications' => 'qualifications',
            'profile.skills' => 'skills',
            'profile.interests' => 'interests',
            'profile.projects' => 'projects',
            'profile.certifications' => 'certifications',
            'profile.aspirations' => 'aspirations',
        ][$guideKey] ?? null;
    }

    /**
     * What the student already has in the sections the bot fills,
     * so it does not ask for what is there and knows what to add to.
     *
     * @return array<string, mixed>
     */
    public function current(User $student): array
    {
        $profile = $student->profile;
        $aspiration = $student->aspirations;

        return [
            'fillable_sections' => collect(self::sections())->pluck('title')->values()->all(),
            'interest_options' => ProfileController::PREDEFINED_INTERESTS,
            'personal' => [
                'phone' => filled($profile?->phone),
                'date_of_birth' => $profile?->date_of_birth !== null,
                'nationality' => $profile?->nationality,
            ],
            'competencies' => $student->competencies()
                ->get(['skill_name', 'proficiency_level'])
                ->map(fn ($competency) => [
                    'name' => $competency->skill_name,
                    'level' => $competency->proficiency_level,
                ])
                ->values()
                ->all(),
            'qualifications' => $student->academicRecords()
                ->get(['programme_name', 'level', 'bdqf_level', 'is_programme_record'])
                ->map(fn ($record) => array_filter([
                    'programme' => $record->programme_name,
                    'award' => $record->level,
                    'bdqf_level' => $record->bdqf_level,
                    'is_their_politeknik_programme' => (bool) $record->is_programme_record,
                ], fn ($value) => $value !== null && $value !== ''))
                ->values()
                ->all(),
            'interests' => $student->interests()->pluck('interest_name')->values()->all(),
            'projects' => $student->projects()->pluck('title')->values()->all(),
            'certifications' => $student->certifications()->pluck('certification_name')->values()->all(),
            'aspirations' => [
                'dream_career' => filled($aspiration?->career_goals) ? implode(', ', (array) $aspiration->career_goals) : null,
                'vision_statement' => $aspiration?->vision_statement,
                'long_term_goals' => $aspiration?->long_term_goals,
            ],
        ];
    }

    /**
     * What the bot asked to fill, checked and reduced to what the
     * form will accept. Null when there is nothing to fill.
     *
     * @return array<string, mixed>|null
     */
    public function normalise(mixed $fill): ?array
    {
        if (! is_array($fill)) {
            return null;
        }

        $normalised = array_filter([
            'competencies' => $this->competencies($fill['competencies'] ?? []),
            'interests' => $this->interests($fill['interests'] ?? []),
            'aspirations' => $this->texts($fill['aspirations'] ?? [], [
                'dream_career' => 100,
                'vision_statement' => 500,
                'long_term_goals' => 500,
            ]),
            'personal' => $this->personal($fill['personal'] ?? []),
            'projects' => $this->rows($fill['projects'] ?? [], 'title', [
                'title' => 150,
                'role' => 100,
                'project_url' => 255,
                'description' => 1000,
                'technologies_used' => 500,
                'start_date' => 10,
                'end_date' => 10,
                'achievements' => 500,
            ]),
            'certifications' => $this->rows($fill['certifications'] ?? [], 'certification_name', [
                'certification_name' => 150,
                'issuing_organization' => 150,
                'issue_date' => 10,
            ]),
            'qualifications' => $this->qualifications($fill['qualifications'] ?? []),
        ]);

        return $normalised === [] ? null : $normalised;
    }

    /**
     * Competencies by name, each with the id of the level chosen on
     * its own scale, as the form's checkbox and list use them.
     *
     * @return array<int, array{id: int, name: string, level_id: int|null, level: string|null}>
     */
    private function competencies(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $levels = BiicfProficiencyLevel::all()->groupBy('scale');

        return collect($items)
            ->filter(fn ($item) => is_array($item) && is_string($item['name'] ?? null))
            ->map(function (array $item) use ($levels) {
                $competency = BiicfCompetency::query()
                    ->whereRaw('LOWER(name) = ?', [Str::lower(trim($item['name']))])
                    ->first(['id', 'name', 'type']);

                if ($competency === null) {
                    return null;
                }

                $wanted = (int) ($item['level'] ?? 0);

                $level = ($levels[$competency->scale()] ?? collect())
                    ->firstWhere('level_number', $wanted);

                return [
                    'id' => $competency->id,
                    'name' => $competency->name,
                    'level_id' => $level?->id,
                    'level' => $level ? 'Level '.$level->level_number.' — '.$level->name : null,
                ];
            })
            ->filter()
            ->unique('id')
            ->take(15)
            ->values()
            ->all();
    }

    /**
     * Interests: the listed ones under their own spelling, anything
     * else as an additional interest.
     *
     * @return array<int, array{name: string, listed: bool}>
     */
    private function interests(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $listed = collect(ProfileController::PREDEFINED_INTERESTS)
            ->keyBy(fn (string $name) => Str::lower($name));

        return collect($items)
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->map(function (string $item) use ($listed) {
                $match = $listed->get(Str::lower(trim($item)));

                return [
                    'name' => $match ?? Str::limit(trim($item), 50, ''),
                    'listed' => $match !== null,
                ];
            })
            ->unique(fn (array $item) => Str::lower($item['name']))
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $limits
     * @return array<string, string>
     */
    private function texts(mixed $values, array $limits): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($limits)
            ->map(fn (int $limit, string $field) => is_string($values[$field] ?? null)
                ? Str::limit(trim($values[$field]), $limit, '')
                : '')
            ->filter(fn (string $value) => $value !== '')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function personal(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $personal = $this->texts($values, ['phone' => 20, 'nationality' => 100]);

        if (isset($personal['phone']) && ! preg_match('/^\+?[0-9 ()-]{6,20}$/', $personal['phone'])) {
            unset($personal['phone']);
        }

        if ($date = $this->pastDate($values['date_of_birth'] ?? null)) {
            $personal['date_of_birth'] = $date;
        }

        return $personal;
    }

    /**
     * New rows for a list section, each needing its first field.
     *
     * @param  array<string, int>  $limits
     * @return array<int, array<string, string>>
     */
    private function rows(mixed $items, string $required, array $limits): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) use ($limits) {
                $row = $this->texts($item, $limits);

                foreach (['start_date', 'end_date', 'issue_date'] as $dateField) {
                    if (isset($row[$dateField])) {
                        $date = $this->pastDate($row[$dateField], allowFuture: $dateField === 'end_date');

                        $date === null
                            ? $row = array_diff_key($row, [$dateField => true])
                            : $row[$dateField] = $date;
                    }
                }

                if (isset($row['project_url']) && ! filter_var($row['project_url'], FILTER_VALIDATE_URL)) {
                    unset($row['project_url']);
                }

                return $row;
            })
            ->filter(fn (array $row) => filled($row[$required] ?? null))
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * Other qualifications, each named by its programme. The
     * student's own Politeknik programme is never one of these: it
     * is filled in for them and is not the bot's to change.
     *
     * @return array<int, array<string, mixed>>
     */
    private function qualifications(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $levels = BiicfBdqfLevel::query()->pluck('level')->map(fn ($level) => (int) $level)->all();

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) use ($levels) {
                $row = $this->rows([$item], 'programme_name', [
                    'institution_name' => 150,
                    'programme_name' => 150,
                    'level' => 100,
                    'field_of_study' => 150,
                    'start_date' => 10,
                    'end_date' => 10,
                ])[0] ?? null;

                if ($row === null) {
                    return null;
                }

                $bdqf = (int) ($item['bdqf_level'] ?? 0);

                if (in_array($bdqf, $levels, true)) {
                    $row['bdqf_level'] = (string) $bdqf;
                }

                if (filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $row['is_current'] = true;
                    unset($row['end_date']);
                }

                return $row;
            })
            ->filter()
            ->take(3)
            ->values()
            ->all();
    }

    private function pastDate(mixed $value, bool $allowFuture = false): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value))) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', trim($value));
        } catch (Throwable) {
            return null;
        }

        if (! $allowFuture && $date->isFuture()) {
            return null;
        }

        return $date->toDateString();
    }
}
