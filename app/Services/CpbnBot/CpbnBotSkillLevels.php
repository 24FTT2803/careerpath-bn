<?php

namespace App\Services\CpbnBot;

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Rating competencies, one at a time, with buttons.
 *
 * The levels are a fixed list, so they are not left to the AI to
 * word or to read back. Every competency ticked on the form with
 * no level yet is waiting to be rated. The student's answer is
 * read here, however it is put ("2", "assist", the button's own
 * words, or "assist, advanced, intermediate" for several in
 * order), and the next one is asked about with its levels as
 * buttons.
 */
class CpbnBotSkillLevels
{
    /**
     * The competencies on the form still waiting for a level, in
     * the order they appear there.
     *
     * @param  array<string, mixed>|null  $formNow
     * @return array<int, string>
     */
    public function waiting(?array $formNow): array
    {
        return collect($formNow['competencies'] ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null))
            ->reject(fn (array $item) => (bool) preg_match('/^\s*level\s*\d/i', (string) ($item['level'] ?? '')))
            ->map(fn (array $item) => trim((string) $item['name']))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether the form already shows a level for this competency.
     *
     * @param  array<string, mixed>|null  $formNow
     */
    public function hasLevel(?array $formNow, string $name): bool
    {
        return collect($formNow['competencies'] ?? [])->contains(fn ($item) => is_array($item)
            && Str::lower(trim((string) ($item['name'] ?? ''))) === Str::lower(trim($name))
            && preg_match('/^\s*level\s*\d/i', (string) ($item['level'] ?? '')));
    }

    /**
     * Levels the student gave in this message for the competencies
     * waiting, as fill items: by name where they name one, and in
     * order where they do not.
     *
     * @param  array<int, string>  $waiting
     * @return array<int, array{name: string, level: string}>
     */
    public function answers(string $question, array $waiting): array
    {
        if ($waiting === []) {
            return [];
        }

        $competencies = $this->competencies($waiting);

        if ($competencies->isEmpty()) {
            return [];
        }

        $text = Str::lower($question);

        /* Where each waiting competency is named in the message, if it is. */
        $named = $competencies
            ->map(fn (BiicfCompetency $competency) => [
                'competency' => $competency,
                'at' => mb_strpos($text, Str::lower($competency->name)),
            ])
            ->filter(fn (array $place) => $place['at'] !== false)
            ->sortBy('at')
            ->values();

        $answers = [];

        if ($named->isNotEmpty()) {
            foreach ($named as $index => $place) {
                $start = $place['at'] + mb_strlen($place['competency']->name);
                $end = $named[$index + 1]['at'] ?? mb_strlen($text);
                $level = $this->levelIn(mb_substr($text, $start, max(0, $end - $start)), $place['competency']);

                if ($level !== null) {
                    $answers[] = ['name' => $place['competency']->name, 'level' => (string) $level->level_number];
                }
            }

            return $answers;
        }

        /* "assist, advanced, intermediate": one each, in the order asked. */
        $parts = collect(preg_split('/[,;\n\/]+|\s+\band\b\s+|\s+\bthen\b\s+/iu', $text) ?: [])
            ->map(fn (string $part) => trim($part))
            ->filter()
            ->values();

        foreach ($competencies->values() as $index => $competency) {
            $part = $parts[$index] ?? null;

            if ($part === null) {
                break;
            }

            $level = $this->levelIn($part, $competency);

            if ($level === null) {
                break;
            }

            $answers[] = ['name' => $competency->name, 'level' => (string) $level->level_number];
        }

        return $answers;
    }

    /**
     * The question about the next competency, and its levels as
     * buttons. Null when none is waiting.
     *
     * @param  array<int, string>  $waiting
     * @return array{name: string, message: string, options: array<int, string>}|null
     */
    public function nextQuestion(array $waiting): ?array
    {
        $competency = $this->competencies($waiting)->first();

        if ($competency === null) {
            return null;
        }

        $levels = BiicfProficiencyLevel::query()
            ->where('scale', $competency->scale())
            ->orderBy('level_number')
            ->get();

        if ($levels->isEmpty()) {
            return null;
        }

        $more = count($waiting) - 1;

        $lines = $levels
            ->map(fn (BiicfProficiencyLevel $level) => '- **'.$level->name.'**: '.$this->gist($level->description))
            ->implode("\n");

        $message = 'How well can you do **'.$competency->name.'**?'
            .($more > 0 ? ' ('.$more.' more after this)' : '')
            ."\n\n".$lines
            ."\n\nPick one below, or just type it.";

        return [
            'name' => $competency->name,
            'message' => $message,
            'options' => $levels
                ->map(fn (BiicfProficiencyLevel $level) => 'Level '.$level->level_number.' — '.$level->name)
                ->values()
                ->all(),
        ];
    }

    /**
     * The competencies the student's target role needs, to offer
     * first: the role named as their dream career, or else their
     * top career match. Ones they already have are left out.
     *
     * @return array{role: string, from: string, competencies: array<int, string>}|null
     */
    public function suggestions(User $student): ?array
    {
        [$role, $from] = $this->targetRole($student);

        if ($role === null) {
            return null;
        }

        $held = $student->competencies()
            ->pluck('skill_name')
            ->map(fn ($name) => Str::lower((string) $name))
            ->all();

        $names = $role->competencies()
            ->get(['biicf_competencies.id', 'biicf_competencies.name'])
            ->unique('id')
            ->pluck('name')
            ->reject(fn (string $name) => in_array(Str::lower($name), $held, true))
            ->take(10)
            ->values()
            ->all();

        return $names === []
            ? null
            : ['role' => $role->title, 'from' => $from, 'competencies' => $names];
    }

    /**
     * @return array{0: BiicfJobRole|null, 1: string}
     */
    private function targetRole(User $student): array
    {
        $goals = collect((array) ($student->aspirations?->career_goals ?? []))
            ->filter(fn ($goal) => is_string($goal) && trim($goal) !== '')
            ->map(fn (string $goal) => Str::lower(trim($goal)));

        if ($goals->isNotEmpty()) {
            $roles = BiicfJobRole::query()->get(['id', 'title', 'alternative_titles']);

            foreach ($goals as $goal) {
                $role = $roles->first(function (BiicfJobRole $role) use ($goal) {
                    $titles = collect([$role->title])
                        ->merge((array) ($role->alternative_titles ?? []))
                        ->filter(fn ($title) => is_string($title) && $title !== '')
                        ->map(fn (string $title) => Str::lower($title));

                    return $titles->contains(fn (string $title) => $title === $goal
                        || Str::contains($goal, $title)
                        || Str::contains($title, $goal));
                });

                if ($role !== null) {
                    return [$role, 'their dream career'];
                }
            }
        }

        $top = $student->currentRecommendations()
            ->whereNotNull('biicf_job_role_id')
            ->orderBy('rank')
            ->first();

        $role = $top ? BiicfJobRole::find($top->biicf_job_role_id) : null;

        return [$role, $role ? 'their top career match' : ''];
    }

    /**
     * Waiting competencies as framework records, in the same order.
     *
     * @param  array<int, string>  $names
     * @return Collection<int, BiicfCompetency>
     */
    private function competencies(array $names): Collection
    {
        $found = BiicfCompetency::query()
            ->whereIn(DB::raw('LOWER(name)'), array_map(fn ($name) => Str::lower($name), $names))
            ->get(['id', 'name', 'type'])
            ->keyBy(fn (BiicfCompetency $competency) => Str::lower($competency->name));

        return collect($names)
            ->map(fn (string $name) => $found[Str::lower($name)] ?? null)
            ->filter()
            ->values();
    }

    /**
     * The level a piece of text gives on this competency's scale:
     * a number ("2", "level 2", the button's "Level 2 — Assist") or
     * a level's name ("assist").
     */
    private function levelIn(string $text, BiicfCompetency $competency): ?BiicfProficiencyLevel
    {
        $levels = BiicfProficiencyLevel::query()
            ->where('scale', $competency->scale())
            ->get();

        if (preg_match('/\b(?:level\s*)?(\d)\b/iu', $text, $match)) {
            $level = $levels->firstWhere('level_number', (int) $match[1]);

            if ($level !== null) {
                return $level;
            }
        }

        return $levels->first(fn (BiicfProficiencyLevel $level) => (bool) preg_match(
            '/\b'.preg_quote(Str::lower($level->name), '/').'\b/u',
            Str::lower($text)
        ));
    }

    /** The first sentence of a level's description, kept short. */
    private function gist(?string $description): string
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $description) ?? '');
        $sentence = preg_split('/(?<=[.!?])\s/', $text)[0] ?? $text;

        return Str::limit($sentence, 110);
    }
}
