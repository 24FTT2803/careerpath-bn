<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use Database\Seeders\BiicfExplorerSeeder;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * Entry requirements and required proficiencies hanging off a
 * tier rather than a role.
 *
 * The book gives a role a column per tier, each with its own
 * entry requirement and its own levels. Holding one of each per
 * role meant only one column of that table could ever be
 * stored, so these tests are mostly about the shapes that were
 * previously impossible: two tiers wanting the same competency
 * at different levels, and one tier accepting either of two
 * qualifications.
 */

function tieredRole(string $title = 'IT Technician'): BiicfJobRole
{
    $subSector = BiicfSubSector::firstOrCreate(
        ['slug' => 'it-services'],
        ['name' => 'IT Services']
    );

    return BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => $title,
        'slug' => Str::slug($title),
    ]);
}

function tier(BiicfJobRole $role, string $label, int $order): BiicfJobRoleTier
{
    return BiicfJobRoleTier::create([
        'job_role_id' => $role->id,
        'label' => $label,
        'sort_order' => $order,
    ]);
}

describe('tiered competency requirements', function () {
    test('two tiers can want the same competency at different levels', function () {
        /*
         * This is the book's own table for IT Technician:
         * Application Development at 1, then 2, then 2. The old
         * unique key on role and competency made it unstorable.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = tieredRole();
        $competency = BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        foreach ([1 => 1, 2 => 2, 3 => 2] as $tierNumber => $levelNumber) {
            tier($role, "Level {$tierNumber}", $tierNumber)
                ->competencies()
                ->attach($competency->id, [
                    'proficiency_level_id' => BiicfProficiencyLevel::technical()
                        ->where('level_number', $levelNumber)
                        ->sole()
                        ->id,
                    'is_core' => true,
                ]);
        }

        $levelsByTier = $role->tiers
            ->mapWithKeys(fn ($tier) => [
                $tier->label => $tier->competencies->first()
                    ->pivot
                    ->proficiency_level_id,
            ]);

        expect($levelsByTier)->toHaveCount(3)
            ->and($levelsByTier['Level 1'])
            ->not->toBe($levelsByTier['Level 2'])
            ->and($levelsByTier['Level 2'])
            ->toBe($levelsByTier['Level 3']);
    });

    test('attaching through a tier still records the role', function () {
        /*
         * The pivot keeps the role alongside the tier so the
         * role's own competencies relation goes on working. A
         * caller going through a tier should not have to know
         * that.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = tieredRole();
        $competency = BiicfCompetency::create([
            'name' => 'Cloud Computing',
            'slug' => 'cloud-computing',
            'type' => 'technical',
        ]);

        tier($role, 'Entrant', 1)
            ->competencies()
            ->attach($competency->id, [
                'proficiency_level_id' => BiicfProficiencyLevel::technical()
                    ->where('level_number', 2)
                    ->sole()
                    ->id,
                'is_core' => true,
            ]);

        $this->assertDatabaseHas('biicf_job_role_competency', [
            'job_role_id' => $role->id,
            'competency_id' => $competency->id,
        ]);

        expect($role->competencies)->toHaveCount(1);
    });

    test('one tier cannot want the same competency twice', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = tieredRole();
        $competency = BiicfCompetency::create([
            'name' => 'Data Analytics',
            'slug' => 'data-analytics',
            'type' => 'technical',
        ]);

        $tier = tier($role, 'Entrant', 1);
        $level = BiicfProficiencyLevel::technical()
            ->where('level_number', 2)
            ->sole();

        $tier->competencies()->attach($competency->id, [
            'proficiency_level_id' => $level->id,
            'is_core' => true,
        ]);

        expect(fn () => $tier->competencies()->attach($competency->id, [
            'proficiency_level_id' => $level->id,
            'is_core' => true,
        ]))->toThrow(QueryException::class);
    });
});

describe('tiered entry requirements', function () {
    test('one tier can accept either of two qualifications', function () {
        /*
         * Level 3 IT Technician: BDQF 4 in IT, or BDQF 3 in IT
         * with an industry certification. Meeting either is
         * enough, which one row per role could not express.
         */
        $tier = tier(tieredRole(), 'Level 3', 3);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => 1,
            'bdqf_level' => 4,
            'field_of_study' => 'IT or related field',
            'raw_text' => 'BDQF Level 4 in IT or related field.',
        ]);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => 2,
            'bdqf_level' => 3,
            'field_of_study' => 'IT or related field',
            'requires_industry_certification' => true,
            'raw_text' => 'BDQF Level 3 in IT or related field with relevant industry certification.',
        ]);

        expect($tier->entryRequirements)->toHaveCount(2)
            ->and($tier->entryRequirements->pluck('bdqf_level')->all())
            ->toBe([4, 3]);
    });

    test('a tier cannot have two of the same alternative', function () {
        $tier = tier(tieredRole(), 'Level 1', 1);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => 1,
            'bdqf_level' => 2,
        ]);

        expect(fn () => BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => 1,
            'bdqf_level' => 3,
        ]))->toThrow(QueryException::class);
    });

    test('a level that names one number is shown as that level', function () {
        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier(tieredRole(), 'Level 1', 1)->id,
            'bdqf_level' => 3,
            'raw_text' => 'BDQF Level 3 in IT or related field.',
        ]);

        expect($requirement->level_label)->toBe('BDQF Level 3');
    });

    test('wording that names no single level is shown as written', function () {
        /*
         * "BDQF Level 2-4 (by seniority)" cannot be reduced to a
         * number, so it is displayed as the book wrote it rather
         * than silently becoming level 2.
         */
        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier(tieredRole(), 'All levels', 1)->id,
            'bdqf_level' => null,
            'raw_text' => 'BDQF Level 2-4 (by seniority)',
        ]);

        expect($requirement->level_label)
            ->toBe('BDQF Level 2-4 (by seniority)')
            ->and($requirement->isMeasurable())
            ->toBeFalse();
    });

    test('a requirement resting on experience says what else it wants', function () {
        /*
         * Nothing in the system records work experience or
         * verifies a certification, so these are reported as
         * what the role asks for beyond the qualification rather
         * than passing or failing a student on something unseen.
         * The level itself can still be checked.
         */
        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier(tieredRole(), 'Level 2', 2)->id,
            'bdqf_level' => 3,
            'requires_industry_experience' => true,
        ]);

        expect($requirement->isMeasurable())->toBeTrue()
            ->and($requirement->hasConditionsBeyondQualification())->toBeTrue()
            ->and($requirement->conditions())->toBe(['relevant industry experience']);
    });

    test('a requirement with nothing to compare cannot be measured', function () {
        /*
         * One alternative in the book names no level at all:
         * "any relevant Project Management Certification with
         * experience".
         */
        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier(tieredRole(), 'Level 3', 3)->id,
            'raw_text' => 'Any relevant Project Management Certification with experience',
            'requires_industry_certification' => true,
        ]);

        expect($requirement->isMeasurable())->toBeFalse();
    });

    test('a plain qualification requirement can be measured', function () {
        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier(tieredRole(), 'Level 1', 1)->id,
            'bdqf_level' => 4,
        ]);

        expect($requirement->isMeasurable())->toBeTrue();
    });
});

describe('keeping the role in step with the tier', function () {
    test('a requirement created on a tier knows its role', function () {
        /*
         * The role is required and derivable, so callers working
         * in terms of tiers should not have to supply it.
         */
        $role = tieredRole();

        $requirement = BiicfEntryRequirement::create([
            'job_role_tier_id' => tier($role, 'Level 1', 1)->id,
            'bdqf_level' => 2,
        ]);

        expect($requirement->fresh()->job_role_id)->toBe($role->id);
    });

    test('deleting a tier clears what hung off it', function () {
        /*
         * Neither table carries a database key to the tier, so
         * the cascade is the model's job.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = tieredRole();
        $tier = tier($role, 'Level 1', 1);

        $competency = BiicfCompetency::create([
            'name' => 'Service Management',
            'slug' => 'service-management',
            'type' => 'technical',
        ]);

        $tier->competencies()->attach($competency->id, [
            'proficiency_level_id' => BiicfProficiencyLevel::technical()
                ->where('level_number', 2)
                ->sole()
                ->id,
            'is_core' => true,
        ]);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'bdqf_level' => 2,
        ]);

        $tier->delete();

        expect(BiicfEntryRequirement::count())->toBe(0)
            ->and(
                DB::table('biicf_job_role_competency')->count()
            )->toBe(0);
    });
});

describe('reading a role as a whole', function () {
    test('the entry tier is the lowest one', function () {
        $role = tieredRole();

        tier($role, 'Level 3', 3);
        tier($role, 'Level 1', 1);
        tier($role, 'Level 2', 2);

        expect($role->entryTier->label)->toBe('Level 1');
    });

    test('the role entry requirement is the lowest tier first alternative', function () {
        $role = tieredRole();

        $lower = tier($role, 'Level 1', 1);
        $higher = tier($role, 'Level 2', 2);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $higher->id,
            'alternative_number' => 1,
            'bdqf_level' => 3,
        ]);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $lower->id,
            'alternative_number' => 2,
            'bdqf_level' => 9,
        ]);

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $lower->id,
            'alternative_number' => 1,
            'bdqf_level' => 2,
        ]);

        expect($role->entryRequirement->bdqf_level)->toBe(2);
    });
});

describe('existing seeding', function () {
    test('every seeded role has a tier', function () {
        $this->seed(BiicfExplorerSeeder::class);

        $withoutTier = BiicfJobRole::query()
            ->whereDoesntHave('tiers')
            ->count();

        expect($withoutTier)->toBe(0)
            ->and(BiicfJobRole::count())->toBeGreaterThan(30);
    });

    test('seeded entry requirements hang off a tier', function () {
        $this->seed(BiicfExplorerSeeder::class);

        expect(BiicfEntryRequirement::count())->toBeGreaterThan(0)
            ->and(
                BiicfEntryRequirement::whereNull('job_role_tier_id')->count()
            )->toBe(0);
    });

    test('a seeded role still reports its entry requirement', function () {
        $this->seed(BiicfExplorerSeeder::class);

        $role = BiicfJobRole::where('slug', 'it-technician')->sole();

        expect($role->entryRequirement)->not->toBeNull()
            ->and($role->entryRequirement->level_label)
            ->toBe('BDQF Level 2-4 (by seniority)');
    });
});
