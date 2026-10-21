<?php

namespace Database\Seeders;

use App\Models\BiicfCompetency;
use App\Models\BiicfCompetencyLevelDescriptor;
use App\Models\BiicfProficiencyLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Load the competencies as the reference book states them.
 *
 * The data file is extracted from the second edition: the
 * glossary for the list and the definitions, the descriptor
 * section for what each level requires. The book's two sections
 * name four competencies differently and the extraction
 * reconciles that, so the file — not this seeder — is where the
 * content is reviewed.
 *
 * Replaces the approximate set that was seeded before. Existing
 * competencies are matched by slug and type and updated in
 * place, so the roles already pointing at them keep their
 * requirements.
 *
 * Safe to re-run.
 */
class BiicfCompetencySeeder extends Seeder
{
    /**
     * Names the earlier seeder used that the book spells
     * differently.
     *
     * Renaming rather than creating afresh means the job roles
     * already requiring these keep doing so, instead of being
     * left pointing at an entry the framework no longer lists.
     *
     * @var array<string, string>
     */
    private const RENAMED = [
        'application-integration' => 'applications-integration',
        'media-and-platform-management' => 'media-platform-management',
    ];

    public function run(): void
    {
        $this->call(BiicfReferenceDataSeeder::class);

        $competencies = $this->load();

        $this->applyRenames();

        $levels = BiicfProficiencyLevel::all()
            ->groupBy('scale')
            ->map(fn ($scaleLevels) => $scaleLevels->keyBy('level_number'));

        foreach ($competencies as $entry) {
            $competency = BiicfCompetency::updateOrCreate(
                [
                    'slug' => $this->slug($entry['name']),
                    'type' => $entry['type'],
                ],
                [
                    'name' => $entry['name'],
                    'description' => $entry['definition'],
                ]
            );

            $this->writeDescriptors($competency, $entry, $levels);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function load(): array
    {
        $path = database_path('data/biicf-competencies.json');

        if (! is_file($path)) {
            throw new RuntimeException(
                "The competency data file is missing: {$path}"
            );
        }

        $decoded = json_decode(
            (string) file_get_contents($path),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        if (! is_array($decoded) || $decoded === []) {
            throw new RuntimeException(
                'The competency data file holds no competencies.'
            );
        }

        return $decoded;
    }

    /**
     * Point the earlier seeder's spellings at the book's.
     */
    private function applyRenames(): void
    {
        foreach (self::RENAMED as $from => $to) {
            foreach (BiicfCompetency::where('slug', $from)->get() as $old) {
                $existing = BiicfCompetency::where('slug', $to)
                    ->where('type', $old->type)
                    ->first();

                /*
                 * The name under the book's spelling can already
                 * be there — the earlier seeder uses both, and
                 * this seeder creates it on every run — and slug
                 * and type are unique together, so renaming onto
                 * it would fail. What points at the old entry
                 * moves across and the old entry goes.
                 */
                if ($existing === null) {
                    $old->update(['slug' => $to]);

                    continue;
                }

                $this->absorb($old, $existing);
            }
        }
    }

    /**
     * Fold one competency into another.
     *
     * The proficiencies roles require of it move across, except
     * where the tier already records one for the destination:
     * the pair is unique, and the destination is the entry the
     * book names, so what it already holds wins.
     */
    private function absorb(BiicfCompetency $old, BiicfCompetency $keep): void
    {
        foreach (
            DB::table('biicf_job_role_competency')
                ->where('competency_id', $old->id)
                ->get() as $row
        ) {
            $rows = DB::table('biicf_job_role_competency')->where('id', $row->id);

            $taken = DB::table('biicf_job_role_competency')
                ->where('competency_id', $keep->id)
                ->where('job_role_tier_id', $row->job_role_tier_id)
                ->exists();

            if ($taken) {
                $rows->delete();
            } else {
                $rows->update(['competency_id' => $keep->id]);
            }
        }

        $old->delete();
    }

    /**
     * Replace one competency's level descriptors.
     *
     * A competency the book describes per role gets one set per
     * scope; everything else gets a single unscoped set. Levels
     * the book marks N/A are left absent rather than stored
     * empty, which is how the schema records that a competency
     * does not reach them.
     *
     * @param  array<string, mixed>  $entry
     * @param  Collection<string, Collection<int, BiicfProficiencyLevel>>  $levels
     */
    private function writeDescriptors(
        BiicfCompetency $competency,
        array $entry,
        $levels
    ): void {
        $competency->levelDescriptors()->delete();

        $scale = $levels->get($competency->scale());

        if ($scale === null) {
            throw new RuntimeException(
                "No proficiency scale is seeded for {$competency->scale()} competencies."
            );
        }

        foreach ($entry['descriptors'] as $descriptor) {
            foreach ($descriptor['levels'] as $level) {
                if ($level['statements'] === []) {
                    continue;
                }

                $proficiency = $scale->get($level['level_number']);

                if ($proficiency === null) {
                    throw new RuntimeException(
                        "{$competency->name} refers to level {$level['level_number']}, "
                        ."which the {$competency->scale()} scale does not have."
                    );
                }

                BiicfCompetencyLevelDescriptor::create([
                    'competency_id' => $competency->id,
                    'proficiency_level_id' => $proficiency->id,
                    'statements' => $level['statements'],
                    'scope_label' => $descriptor['scope'],
                ]);
            }
        }
    }

    /**
     * Slug a name that may contain a slash or an ampersand.
     *
     * Str::slug drops both rather than treating them as word
     * breaks, so "Media & Platform Management" would otherwise
     * collapse to one run of letters.
     */
    private function slug(string $name): string
    {
        return Str::slug(str_replace(['/', '&'], '-', $name));
    }
}
