<?php

namespace Database\Seeders;

use App\Models\BiicfCompetency;
use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobDescriptionPoint;
use App\Models\BiicfJobFamily;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\CareerRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Load the job roles as the reference book states them.
 *
 * Extracted from the second edition: 38 roles over 39 listings,
 * since Chief Technology Officer is printed under two
 * sub-sectors. Every role's description, duties, trainings,
 * alternate titles, functional groups, job families, job levels
 * and tiers come from the book.
 *
 * The competency tables come with it: one column per tier, so a
 * competency can be required at a different proficiency at each
 * tier of the role. Where the book's table spells a competency
 * differently from its own glossary — "Creativity & Innovation"
 * against "Creativity and Innovation" — the glossary entry is
 * what the proficiency is recorded against, since the glossary
 * is the list of competencies.
 *
 * Entry requirements arrive as the book's own wording rather
 * than split per tier, which is why the model carries raw_text:
 * a requirement that cannot be reduced to a level is shown as
 * written and reported as undetermined.
 *
 * Existing roles are matched by title so the recommendations and
 * competency requirements already pointing at them survive.
 *
 * Safe to re-run. It must not be called from a seeder using
 * WithoutModelEvents, including DatabaseSeeder: a removed tier is
 * cleared by a model hook rather than a database cascade, and an
 * entry requirement takes its role from the tier the same way.
 */
class BiicfJobRoleSeeder extends Seeder
{
    /**
     * The label every role's tier was given when requirements
     * and proficiencies moved onto tiers, before the book's own
     * columns were available to name them.
     */
    private const PLACEHOLDER_TIER_LABEL = 'All levels';

    /** @var Collection<string, BiicfCompetency>|null */
    private $competencies = null;

    /** @var Collection<string, BiicfProficiencyLevel>|null */
    private $levels = null;

    public function run(): void
    {
        $this->call(BiicfReferenceDataSeeder::class);
        /*
         * The competency tables name the book's glossary entries,
         * so the glossary has to be in place before they are read.
         */
        $this->call(BiicfCompetencySeeder::class);

        foreach ($this->load() as $entry) {
            $role = $this->upsertRole($entry);
            $isNew = $role->wasRecentlyCreated;

            $this->syncFunctionalGrouping($role, $entry, $isNew);
            $this->syncTiers($role, $entry);
            $this->syncContent($role, $entry);
            $this->syncTrainings($role, $entry);
            $this->syncCompetencies($role, $entry);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function load(): array
    {
        $path = database_path('data/biicf-job-roles.json');

        if (! is_file($path)) {
            throw new RuntimeException("The job role data file is missing: {$path}");
        }

        $decoded = json_decode(
            (string) file_get_contents($path),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        if (! is_array($decoded) || $decoded === []) {
            throw new RuntimeException('The job role data file holds no roles.');
        }

        $this->check($decoded);

        return $decoded;
    }

    /**
     * Refuse data that reads like a mis-parsed page.
     *
     * A training course name is a short phrase. One running to
     * hundreds of characters means the extraction has taken in
     * the column beside it, which is how a duty sentence came to
     * be filed as a course. The database would otherwise report
     * only that a string was too long for a column, naming no
     * role, which is a poor thing to debug an edition import by.
     *
     * @param  array<int, array<string, mixed>>  $roles
     */
    private function check(array $roles): void
    {
        $limit = 255;

        foreach ($roles as $role) {
            /*
             * Every competency carries a proficiency for every
             * tier. A gap means a column of the table went
             * unread, which would show as a role simply not
             * needing that competency at that tier.
             */
            foreach ($role['competencies'] as $competency) {
                $missing = array_diff(
                    $role['tiers'],
                    array_keys($competency['levels'])
                );

                if ($missing !== []) {
                    throw new RuntimeException(
                        "{$role['title']}: {$competency['name']} has no "
                        .'proficiency for '.implode(', ', $missing).'.'
                    );
                }
            }

            foreach ($role['critical_work_functions'] as $function) {
                if (! array_key_exists('children', $function)) {
                    throw new RuntimeException(
                        "{$role['title']}: a duty is recorded without its "
                        .'nesting. The data file predates the columns being '
                        .'read apart, and reading it flat is what filed one '
                        ."role's duties under another."
                    );
                }
            }

            foreach ($role['trainings'] as $training) {
                if (mb_strlen($training) <= $limit) {
                    continue;
                }

                throw new RuntimeException(
                    "{$role['title']}: a training course name runs to "
                    .mb_strlen($training)." characters, over the {$limit} a "
                    .'name can hold. The extraction has most likely taken in '
                    .'the column beside it: '.mb_substr($training, 0, 80).'…'
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function upsertRole(array $entry): BiicfJobRole
    {
        $subSector = BiicfSubSector::firstOrCreate(
            ['slug' => $this->slug($entry['sub_sector'])],
            ['name' => $entry['sub_sector']]
        );

        /*
         * Matched on the title rather than the slug: the earlier
         * seeder slugged "IT Administrator/Manager" with the
         * slash dropped, so the slugs do not agree even where the
         * roles do. Matching by title keeps the recommendations
         * and competency requirements attached.
         */
        $role = $this->existingRole($entry['title'], $subSector->id)
            ?? new BiicfJobRole;

        $role->fill([
            'sub_sector_id' => $subSector->id,
            'title' => $entry['title'],
            'slug' => $this->slug($entry['title']),
            /*
             * A role can sit in several functional groups — the
             * book prints three for IT Technician. This is a
             * single column, so they are joined; the job families
             * beside them are a relation and keep their shape.
             */
            'functional_group' => implode(' / ', $entry['functional_groups']) ?: null,
            'alternative_titles' => $entry['alternate_titles'],
        ]);

        $role->save();

        return $role;
    }

    /**
     * The row to update for a title, merging away any others.
     *
     * The earlier seeder created a row per listing, so Chief
     * Technology Officer and Business Intelligence Director each
     * exist twice. The book states one role each — Chief
     * Technology Officer is simply printed under two sub-sectors
     * — so the extras are merged rather than left behind to show
     * up in the explorer as hollow copies with no tiers.
     *
     * The row kept is the one already filed under the sub-sector
     * the book prints it in, and otherwise the oldest, which is
     * the one most likely to be referenced.
     */
    private function existingRole(string $title, int $subSectorId): ?BiicfJobRole
    {
        $rows = BiicfJobRole::where('title', $title)->orderBy('id')->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $keeper = $rows->firstWhere('sub_sector_id', $subSectorId) ?? $rows->first();

        foreach ($rows->reject(fn (BiicfJobRole $row) => $row->is($keeper)) as $duplicate) {
            $this->repoint($duplicate, $keeper);

            /*
             * Tiers go through the model so the hook clearing
             * their requirements and competency rows runs. Those
             * rows carry no foreign key to the tier, so nothing
             * else would clear them.
             */
            $duplicate->tiers->each->delete();
            $duplicate->delete();
        }

        return $keeper;
    }

    /**
     * Move what points at a duplicate role onto the kept one.
     *
     * Both references cascade on delete, so skipping this would
     * take a student's saved recommendation with the duplicate.
     */
    private function repoint(BiicfJobRole $from, BiicfJobRole $to): void
    {
        CareerRecommendation::where('biicf_job_role_id', $from->id)
            ->update(['biicf_job_role_id' => $to->id]);

        foreach ([
            'from_job_role_id' => 'to_job_role_id',
            'to_job_role_id' => 'from_job_role_id',
        ] as $column => $other) {
            foreach (
                DB::table('biicf_career_path_edges')->where($column, $from->id)->get() as $edge
            ) {
                /*
                 * Moving the edge would either duplicate one the
                 * kept role already has — the pair is unique — or
                 * point the role at itself. Either way it is
                 * dropped rather than moved.
                 */
                $clashes = (int) $edge->{$other} === $to->id
                    || DB::table('biicf_career_path_edges')
                        ->where($column, $to->id)
                        ->where($other, $edge->{$other})
                        ->exists();

                $edges = DB::table('biicf_career_path_edges')->where('id', $edge->id);

                if ($clashes) {
                    $edges->delete();
                } else {
                    $edges->update([$column => $to->id]);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function syncFunctionalGrouping(
        BiicfJobRole $role,
        array $entry,
        bool $isNew
    ): void {
        $families = collect($entry['job_families'])
            ->map(fn (string $name) => BiicfJobFamily::firstOrCreate(
                ['slug' => $this->slug($name)],
                ['name' => $name]
            ))
            ->values();

        $role->jobFamilies()->sync(
            $families
                ->mapWithKeys(fn ($family, $index) => [
                    $family->id => ['sort_order' => $index + 1],
                ])
                ->all()
        );

        $levels = collect($entry['job_levels'])
            ->map(fn (string $name) => BiicfJobLevel::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => $this->slug($name),
                    /*
                     * Every level the book's role pages use is
                     * seeded ahead of this, with a position. This
                     * is the safety net for a later edition
                     * naming one more: it lands at the end rather
                     * than being silently dropped.
                     */
                    'sort_order' => 90,
                ]
            ));

        $role->jobLevels()->sync($levels->pluck('id')->all());

        /*
         * career_path_level positions a role vertically on the
         * explorer's map. The book gives no such number — it
         * draws the progression as a diagram — so a role arriving
         * for the first time is placed by the seniority of its
         * job level, which is the closest thing the book states.
         *
         * Only a new role. A role already in the database keeps
         * its position, including zero, which the earlier seeder
         * uses for an entry-level role rather than as "unset";
         * renumbering those would redraw maps that already read
         * correctly.
         */
        if ($isNew) {
            $role->update([
                'career_path_level' => (int) $levels->max('sort_order'),
            ]);
        }
    }

    /**
     * Replace a role's tiers and the entry requirement on each.
     *
     * @param  array<string, mixed>  $entry
     */
    private function syncTiers(BiicfJobRole $role, array $entry): void
    {
        $this->adoptPlaceholderTier($role, $entry['tiers'][0]);

        $keep = [];

        foreach ($entry['tiers'] as $index => $label) {
            $tier = BiicfJobRoleTier::updateOrCreate(
                ['job_role_id' => $role->id, 'label' => $label],
                [
                    'sort_order' => $index + 1,
                    'job_level_id' => BiicfJobLevel::where('name', $label)->value('id'),
                ]
            );

            $keep[] = $tier->id;
        }

        /*
         * Through the model, so the hook clearing a tier's
         * requirements and competency rows runs.
         */
        $role->tiers()
            ->whereNotIn('id', $keep ?: [0])
            ->get()
            ->each
            ->delete();

        $this->syncEntryRequirements($role, $entry);
    }

    /**
     * Write each tier's ways in, as the book separates them.
     *
     * A requirement is one paragraph on the page but a structure
     * underneath: the ways in are separated by OR, each opening
     * with a qualification level and the fields it accepts, then
     * saying what else it wants. Two roles write a requirement
     * per tier into that one paragraph, naming the tier in front
     * of each.
     *
     * Each alternative keeps its own sentence, so a student
     * reads the book's wording and a match can still be computed
     * against the level.
     *
     * @param  array<string, mixed>  $entry
     */
    private function syncEntryRequirements(BiicfJobRole $role, array $entry): void
    {
        if ($entry['entry_requirements'] === []) {
            return;
        }

        $tiers = $role->tiers()->get()->keyBy('label');

        foreach ($entry['entry_requirements'] as $label => $alternatives) {
            $tier = $tiers->get($label);

            if ($tier === null) {
                throw new RuntimeException(
                    "{$role->title}: an entry requirement names a tier "
                    ."\"{$label}\" the role does not have."
                );
            }

            /*
             * Only the alternatives the book supplies. An
             * administrator can add a way in the framework does
             * not list, and it takes the next number along;
             * clearing the tier outright would delete it on
             * every reload.
             */
            $tier->entryRequirements()
                ->where('alternative_number', '<=', count($alternatives))
                ->delete();

            foreach ($alternatives as $index => $alternative) {
                BiicfEntryRequirement::create([
                    'job_role_tier_id' => $tier->id,
                    'alternative_number' => $index + 1,
                    ...$alternative,
                ]);
            }
        }
    }

    /**
     * Rename a role's placeholder tier to the book's first one.
     *
     * Every role was given a tier labelled "All levels" when
     * requirements and competency proficiencies moved onto tiers,
     * and those rows hang off it. Creating the book's tiers
     * alongside it and then deleting it would take the role's
     * competency requirements with it — the only ones in the
     * database until the book's own tables are read — so the
     * placeholder becomes the first real tier instead, keeping
     * what is attached to it.
     */
    private function adoptPlaceholderTier(BiicfJobRole $role, string $firstLabel): void
    {
        $placeholder = $role->tiers()
            ->where('label', self::PLACEHOLDER_TIER_LABEL)
            ->first();

        if ($placeholder === null) {
            return;
        }

        $first = $role->tiers()->where('label', $firstLabel)->first();

        /*
         * On a re-run the book's own tier is already there, so
         * the placeholder cannot simply take its name. What hangs
         * off it moves across instead, and the cleanup that
         * follows can then have the empty tier.
         */
        if ($first !== null) {
            $this->moveTierContents($placeholder, $first);

            return;
        }

        $placeholder->update(['label' => $firstLabel]);
    }

    /**
     * Move a tier's requirements and proficiencies onto another.
     *
     * Where the destination already records something for the
     * same competency, or already has a requirement, the row
     * being moved is dropped: the destination is the tier the
     * book names, so what it holds wins.
     */
    private function moveTierContents(
        BiicfJobRoleTier $from,
        BiicfJobRoleTier $to
    ): void {
        foreach (
            DB::table('biicf_job_role_competency')
                ->where('job_role_tier_id', $from->id)
                ->get() as $row
        ) {
            $rows = DB::table('biicf_job_role_competency')->where('id', $row->id);

            $taken = DB::table('biicf_job_role_competency')
                ->where('job_role_tier_id', $to->id)
                ->where('competency_id', $row->competency_id)
                ->exists();

            if ($taken) {
                $rows->delete();
            } else {
                $rows->update(['job_role_tier_id' => $to->id]);
            }
        }

        $from->entryRequirements()->get()->each(function (BiicfEntryRequirement $requirement) use ($to): void {
            $taken = $to->entryRequirements()
                ->where('alternative_number', $requirement->alternative_number)
                ->exists();

            if ($taken) {
                $requirement->delete();
            } else {
                $requirement->update(['job_role_tier_id' => $to->id]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function syncContent(BiicfJobRole $role, array $entry): void
    {
        $role->descriptionPoints()->delete();

        foreach ($entry['description'] as $index => $text) {
            BiicfJobDescriptionPoint::create([
                'job_role_id' => $role->id,
                'text' => $text,
                'sort_order' => $index + 1,
            ]);
        }

        $role->criticalWorkFunctions()->delete();

        $this->writeWorkFunctions($role, $entry['critical_work_functions']);
    }

    /**
     * Write a role's duties, keeping the book's nesting.
     *
     * Most roles group their duties under headings of their own
     * — "Website Design", "Research and Insight" — and two roles
     * nest a third level under a duty. A flat list would read as
     * though a heading were itself something the role does.
     *
     * @param  array<int, array<string, mixed>>  $functions
     */
    private function writeWorkFunctions(
        BiicfJobRole $role,
        array $functions,
        ?int $parentId = null
    ): void {
        foreach ($functions as $index => $function) {
            $row = BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
                'parent_id' => $parentId,
                'text' => $function['text'],
                'sort_order' => $index + 1,
            ]);

            if ($function['children'] !== []) {
                $this->writeWorkFunctions($role, $function['children'], $row->id);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function syncTrainings(BiicfJobRole $role, array $entry): void
    {
        $trainings = collect($entry['trainings'])
            ->map(fn (string $name) => BiicfTraining::firstOrCreate(['name' => $name]))
            ->pluck('id')
            ->all();

        $role->trainings()->sync($trainings);
    }

    /**
     * Write each tier's column of the role's competency table.
     *
     * The book prints one column per tier, so a competency can be
     * required at different proficiencies at different tiers —
     * Application Development at 1, 2 and 2 across IT
     * Technician's three levels. The proficiency is on the pivot,
     * which is why it hangs off the tier rather than the role.
     *
     * Everything the book lists for a role is marked core: the
     * table is the role's requirement and draws no distinction
     * between core and supporting. That column kept the earlier
     * seeder's invented split, which said nothing the book says.
     *
     * @param  array<string, mixed>  $entry
     */
    private function syncCompetencies(BiicfJobRole $role, array $entry): void
    {
        $tiers = $role->tiers()->get()->keyBy('label');
        $columns = [];

        foreach ($entry['competencies'] as $row) {
            $competency = $this->competency($row['name'], $row['type']);

            foreach ($row['levels'] as $label => $number) {
                $tier = $tiers->get($label);

                if ($tier === null) {
                    throw new RuntimeException(
                        "{$role->title}: the competency table names a tier "
                        ."\"{$label}\" the role does not have."
                    );
                }

                $columns[$label][$competency->id] = [
                    'proficiency_level_id' => $this->proficiency(
                        $row['type'] === 'technical'
                            ? BiicfProficiencyLevel::SCALE_TECHNICAL
                            : BiicfProficiencyLevel::SCALE_SOFT,
                        $number
                    )->id,
                    'is_core' => true,
                ];
            }
        }

        foreach ($columns as $label => $rows) {
            $tiers->get($label)->competencies()->sync($rows);
        }
    }

    /**
     * A competency by the name the glossary gives it.
     *
     * Matched on name and type together: the two lists are
     * separate in the book and a name can sit in either.
     */
    private function competency(string $name, string $type): BiicfCompetency
    {
        $this->competencies ??= BiicfCompetency::all()
            ->keyBy(fn (BiicfCompetency $competency) => $competency->name.'|'.$competency->type);

        $competency = $this->competencies->get($name.'|'.$type);

        if ($competency === null) {
            throw new RuntimeException(
                "The competency library has no {$type} competency "
                ."called \"{$name}\". Load it first: the competency "
                .'seeder is what puts the book\'s glossary in place.'
            );
        }

        return $competency;
    }

    private function proficiency(string $scale, int $number): BiicfProficiencyLevel
    {
        $this->levels ??= BiicfProficiencyLevel::all()
            ->keyBy(fn (BiicfProficiencyLevel $level) => $level->scale.'|'.$level->level_number);

        $level = $this->levels->get($scale.'|'.$number);

        if ($level === null) {
            throw new RuntimeException(
                "The {$scale} scale has no level {$number}."
            );
        }

        return $level;
    }

    /**
     * Slug a name that may contain a slash or an ampersand.
     *
     * Str::slug drops both rather than treating them as word
     * breaks, which would turn "IT Administrator/Manager" into
     * one run of letters.
     */
    private function slug(string $name): string
    {
        return Str::slug(str_replace(['/', '&'], '-', $name));
    }
}
