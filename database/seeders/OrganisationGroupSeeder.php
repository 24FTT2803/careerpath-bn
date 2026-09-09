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
                'parent_id' => null,
                'name' => 'School of Information and Communication Technology',
                'is_active' => true,
            ]
        );

        $applicationDevelopment = OrganisationGroup::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'code' => 'APP-DEV',
            ],
            [
                'group_type_id' => $programmeType->id,
                'parent_id' => $sict->id,
                'name' => 'Diploma in ICT (Application Development)',
                'is_active' => true,
            ]
        );

        $dadt04 = OrganisationGroup::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'code' => 'DADT04',
            ],
            [
                'group_type_id' => $classType->id,
                'parent_id' => $applicationDevelopment->id,
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
    }
}