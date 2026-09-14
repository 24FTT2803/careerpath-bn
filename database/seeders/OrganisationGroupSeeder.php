<?php

namespace Database\Seeders;

use App\Models\GroupMembership;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganisationGroupSeeder extends Seeder
{
    public function run(): void
    {
        $organisation = Organisation::updateOrCreate(
            [
                'code' => 'PB',
            ],
            [
                'name' => 'Politeknik Brunei',
                'is_active' => true,
            ]
        );

        $schoolType = OrganisationGroupType::firstOrCreate([
            'organisation_id' => $organisation->id,
            'name' => 'School',
        ]);

        $programmeType = OrganisationGroupType::firstOrCreate([
            'organisation_id' => $organisation->id,
            'name' => 'Programme',
        ]);

        OrganisationGroupType::firstOrCreate([
            'organisation_id' => $organisation->id,
            'name' => 'Intake',
        ]);

        OrganisationGroupType::firstOrCreate([
            'organisation_id' => $organisation->id,
            'name' => 'Intake Session',
        ]);

        $classType = OrganisationGroupType::firstOrCreate([
            'organisation_id' => $organisation->id,
            'name' => 'Class / Group',
        ]);

        $sict = OrganisationGroup::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'code' => 'SICT',
            ],
            [
                'group_type_id' => $schoolType->id,
                'name' => 'School of Information and Communication Technology',
                'is_active' => true,
            ]
        );

        $applicationDevelopment = OrganisationGroup::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'code' => 'DADT',
            ],
            [
                'group_type_id' => $programmeType->id,
                'name' => 'Diploma in ICT (Application Development)',
                'is_active' => true,
            ]
        );

        /*
         * The other SICT programmes. Students choose from these
         * rather than from a list written into the code.
         */
        foreach ([
            'DDAT' => 'Diploma in ICT (Data Analytics)',
            'DCNG' => 'Diploma in ICT (Cloud Networking)',
            'DBIS' => 'Diploma in Business Information Systems',
        ] as $code => $name) {
            $programme = OrganisationGroup::updateOrCreate(
                [
                    'organisation_id' => $organisation->id,
                    'code' => $code,
                ],
                [
                    'group_type_id' => $programmeType->id,
                    'name' => $name,
                    'is_active' => true,
                ]
            );

            $programme->parents()->syncWithoutDetaching([
                $sict->id => ['is_primary' => true],
            ]);
        }

        $dadt04 = OrganisationGroup::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'code' => 'DADT04',
            ],
            [
                'group_type_id' => $classType->id,
                'name' => 'DADT04',
                'is_active' => true,
            ]
        );

        $student = User::query()
            ->where(
                'email',
                '1508user@example.com'
            )
            ->first();

        if ($student) {
            GroupMembership::firstOrCreate([
                'user_id' => $student->id,
                'organisation_group_id' => $dadt04->id,
            ]);
        }

        $applicationDevelopment->parents()->syncWithoutDetaching([
            $sict->id => ['is_primary' => true],
        ]);

        $dadt04->parents()->syncWithoutDetaching([
            $applicationDevelopment->id => ['is_primary' => true],
        ]);
    }
}
