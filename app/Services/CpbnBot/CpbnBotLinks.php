<?php

namespace App\Services\CpbnBot;

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Support\CpbnBotKnowledge;
use Illuminate\Support\Str;

/**
 * The buttons beneath a CPBN Bot reply.
 *
 * A link is a page, and for the BIICF Explorer it can go further:
 * a tab, a sub-sector, a job role, one of its tiers, a competency
 * and a proficiency level. The bot names these as they are
 * written; here they are checked against the framework and turned
 * into an address, so a link can only ever point at something
 * that exists. Anything that does not match is dropped rather
 * than guessed at.
 */
class CpbnBotLinks
{
    /**
     * A link as the bot gave it, checked and reduced to what it
     * actually points at. Null when the page is not one the bot
     * knows.
     *
     * Accepts a bare page key, which is what links were before
     * they could reach inside the Explorer.
     *
     * @param  string|array<string, mixed>  $link
     * @return array<string, mixed>|null
     */
    public function normalise(string|array $link): ?array
    {
        $link = is_string($link) ? ['page' => $link] : $link;

        $page = $link['page'] ?? null;

        if (! is_string($page) || ! array_key_exists($page, CpbnBotKnowledge::pages())) {
            return null;
        }

        if ($page !== 'biicf_explorer') {
            return ['page' => $page];
        }

        $normalised = ['page' => $page];

        if ($subSector = $this->subSector($link['sub_sector'] ?? null)) {
            $normalised['sub_sector'] = $subSector->slug;
        }

        $role = $this->jobRole($link['job_role'] ?? null);

        if ($role) {
            $normalised['role'] = $role->slug;

            if ($tier = $this->tier($role, $link['tier'] ?? null)) {
                $normalised['tier'] = $tier;
            }
        }

        if ($competency = $this->competency($link['competency'] ?? null)) {
            $normalised['competency'] = $competency->id;

            $level = (int) ($link['proficiency_level'] ?? 0);

            $rungs = $competency->scale() === BiicfProficiencyLevel::SCALE_SOFT ? 4 : 5;

            if ($level >= 1 && $level <= $rungs) {
                $normalised['level'] = $level;
            }
        }

        $section = $link['explorer_section'] ?? null;

        if (array_key_exists((string) $section, CpbnBotKnowledge::explorerSections())
            && ($role || ! in_array($section, CpbnBotKnowledge::roleSections(), true))) {
            $normalised['section'] = $section;
        } elseif ($role && ! isset($normalised['competency'])) {
            /* A role on its own opens where the Explorer opens one. */
            $normalised['section'] = 'career-paths';
        }

        return $normalised;
    }

    /**
     * Whether a link adds nothing for a student on this page: a
     * plain link to where they already are.
     *
     * @param  array<string, mixed>  $link
     */
    public function isOnlyThePage(array $link, ?string $currentPage): bool
    {
        return $currentPage !== null
            && $link['page'] === $currentPage
            && count($link) === 1;
    }

    /**
     * A stored link as a button: its label and address.
     *
     * @param  string|array<string, mixed>  $link
     * @return array{key: string, label: string, url: string}|null
     */
    public function present(string|array $link): ?array
    {
        $link = is_string($link) ? ['page' => $link] : $link;

        $pages = CpbnBotKnowledge::pages();
        $page = $pages[$link['page'] ?? ''] ?? null;

        if ($page === null) {
            return null;
        }

        if ($link['page'] !== 'biicf_explorer' || count($link) === 1) {
            return ['key' => $link['page'], 'label' => $page['name'], 'url' => route($page['route'])];
        }

        $role = isset($link['role'])
            ? BiicfJobRole::where('slug', $link['role'])->first(['id', 'title', 'slug'])
            : null;

        $subSector = isset($link['sub_sector'])
            ? BiicfSubSector::where('slug', $link['sub_sector'])->first(['id', 'name', 'slug'])
            : null;

        $competency = isset($link['competency'])
            ? BiicfCompetency::find($link['competency'], ['id', 'name', 'type'])
            : null;

        $sections = CpbnBotKnowledge::explorerSections();

        $label = collect([
            $role?->title ?? $subSector?->name,
            isset($link['section']) && ! $competency ? $sections[$link['section']] : null,
            $link['tier'] ?? null,
            $competency?->name,
            isset($link['level']) ? 'Level '.$link['level'] : null,
        ])->filter()->implode(' › ');

        $query = array_filter([
            'section' => $link['section'] ?? null,
            'sub_sector' => $subSector?->slug,
            'role' => $role?->slug,
            'tier' => $link['tier'] ?? null,
            'competency' => $competency?->id,
            'level' => $link['level'] ?? null,
        ]);

        return [
            'key' => 'biicf_explorer',
            'label' => $label !== '' ? $label : $page['name'],
            'url' => route($page['route'], $query),
        ];
    }

    private function subSector(mixed $name): ?BiicfSubSector
    {
        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        return BiicfSubSector::query()
            ->where('slug', Str::slug($name))
            ->orWhereRaw('LOWER(name) = ?', [Str::lower(trim($name))])
            ->first(['id', 'name', 'slug']);
    }

    private function jobRole(mixed $title): ?BiicfJobRole
    {
        if (! is_string($title) || trim($title) === '') {
            return null;
        }

        return BiicfJobRole::query()
            ->where('slug', Str::slug($title))
            ->orWhereRaw('LOWER(title) = ?', [Str::lower(trim($title))])
            ->with('tiers:id,job_role_id,label')
            ->first(['id', 'title', 'slug']);
    }

    /**
     * A tier of the role by its label, exactly or, failing that,
     * the one tier whose label contains what was asked for, so
     * "Expert" finds "Expert/Management".
     */
    private function tier(BiicfJobRole $role, mixed $label): ?string
    {
        if (! is_string($label) || trim($label) === '') {
            return null;
        }

        $wanted = Str::lower(trim($label));
        $labels = $role->tiers->pluck('label')->filter();

        $exact = $labels->first(fn (string $tier) => Str::lower($tier) === $wanted);

        if ($exact) {
            return $exact;
        }

        $partial = $labels->filter(fn (string $tier) => Str::contains(Str::lower($tier), $wanted));

        return $partial->count() === 1 ? $partial->first() : null;
    }

    private function competency(mixed $name): ?BiicfCompetency
    {
        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        return BiicfCompetency::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim($name))])
            ->first(['id', 'name', 'type']);
    }
}
