<?php

use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('career AI payload contains relevant student profile data', function () {
    $student = User::factory()->create([
        'first_name' => 'PrivateFirstName',
        'last_name' => 'PrivateLastName',
        'name' => 'Private Student Name',
        'email' => 'private-student@example.test',
        'student_id' => 'PRIVATE-STUDENT-ID',
        'programme' => 'Diploma in ICT',
        'cgpa' => 3.75,
        'role' => 'student',
        'avatar' => 'private-avatar.jpg',
    ]);

    $now = now();

    DB::table('academic_records')->insert([
        'user_id' => $student->id,
        'institution_name' => 'Test Institution',
        'programme_name' => 'Diploma in ICT',
        'level' => 'Diploma',
        'start_date' => '2025-01-01',
        'end_date' => null,
        'cgpa' => 3.75,
        'subjects' => json_encode([
            'Programming',
            'Database Systems',
        ]),
        'grades' => json_encode([
            'Programming' => 'A',
            'Database Systems' => 'B+',
        ]),
        'achievements' => 'Academic test achievement.',
        'is_current' => true,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_competencies')->insert([
        'user_id' => $student->id,
        'skill_name' => 'Laravel',
        'category' => 'technical',
        'proficiency_level' => 'intermediate',
        'evidence' => json_encode([
            'Built a Laravel application',
        ]),
        'description' => 'Web application development competency.',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_interests')->insert([
        'user_id' => $student->id,
        'interest_name' => 'Software Development',
        'category' => 'career',
        'priority' => 1,
        'description' => 'Interested in building software applications.',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_projects')->insert([
        'user_id' => $student->id,
        'title' => 'Career Guidance Application',
        'description' => 'A test Laravel project.',
        'technologies_used' => json_encode([
            'Laravel',
            'PHP',
            'MySQL',
        ]),
        'team_members' => json_encode([
            'Private Team Member',
        ]),
        'role' => 'Developer',
        'project_url' => 'https://private-project.example.test',
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-01',
        'achievements' => 'Completed the project.',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_certifications')->insert([
        'user_id' => $student->id,
        'certification_name' => 'Test Certification',
        'issuing_organization' => 'Test Organisation',
        'issue_date' => '2026-02-01',
        'expiry_date' => null,
        'credential_id' => 'PRIVATE-CREDENTIAL-ID',
        'credential_url' =>
            'https://private-credential.example.test',

        'description' =>
            'Certification used for payload testing.',

        'certificate_file_path' =>
            'certificates/private-evidence.pdf',

        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_aspirations')->insert([
        'user_id' => $student->id,

        'career_goals' => json_encode([
            'Become a software engineer',
        ]),

        'preferred_industries' => json_encode([
            'Technology',
        ]),

        'preferred_work_activities' => json_encode([
            'Programming',
            'Problem Solving',
        ]),

        'vision_statement' =>
            'Build useful software solutions.',

        'mission_statement' =>
            'Continue developing technical skills.',

        'long_term_goals' =>
            'Progress into a senior development role.',

        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $builder = new CareerAiPayloadBuilder();

    $payload = $builder->build(
        $student
    );

    expect($payload['schema_version'])
        ->toBe('1.0');

    $profile = $payload['student_profile'];

    /*
     * General academic information.
     */
    expect($profile['programme'])
        ->toBe('Diploma in ICT')
        ->and($profile['cgpa'])
        ->toBe(3.75);

    /*
     * Academic records.
     */
    expect($profile['academic_records'])
        ->toHaveCount(1);

    $academicRecord =
        $profile['academic_records'][0];

    expect($academicRecord['institution_name'])
        ->toBe('Test Institution')
        ->and($academicRecord['programme_name'])
        ->toBe('Diploma in ICT')
        ->and($academicRecord['level'])
        ->toBe('Diploma')
        ->and($academicRecord['cgpa'])
        ->toBe(3.75)
        ->and($academicRecord['subjects'])
        ->toBe([
            'Programming',
            'Database Systems',
        ]);

    /*
     * Competencies.
     */
    expect($profile['competencies'])
        ->toHaveCount(1);

    $competency =
        $profile['competencies'][0];

    expect($competency['skill_name'])
        ->toBe('Laravel')
        ->and($competency['category'])
        ->toBe('technical')
        ->and($competency['proficiency_level'])
        ->toBe('intermediate')
        ->and($competency['evidence'])
        ->toBe([
            'Built a Laravel application',
        ]);

    /*
     * Interests.
     */
    expect($profile['interests'])
        ->toHaveCount(1);

    $interest =
        $profile['interests'][0];

    expect($interest['interest_name'])
        ->toBe('Software Development')
        ->and($interest['category'])
        ->toBe('career')
        ->and($interest['priority'])
        ->toBe(1);

    /*
     * Projects.
     */
    expect($profile['projects'])
        ->toHaveCount(1);

    $project =
        $profile['projects'][0];

    expect($project['title'])
        ->toBe('Career Guidance Application')
        ->and($project['technologies_used'])
        ->toBe([
            'Laravel',
            'PHP',
            'MySQL',
        ])
        ->and($project['role'])
        ->toBe('Developer');

    /*
     * Certifications.
     */
    expect($profile['certifications'])
        ->toHaveCount(1);

    $certification =
        $profile['certifications'][0];

    expect($certification['certification_name'])
        ->toBe('Test Certification')
        ->and($certification['issuing_organization'])
        ->toBe('Test Organisation')
        ->and($certification['issue_date'])
        ->toBe('2026-02-01');

    /*
     * Aspirations.
     */
    expect($profile['aspirations'])
        ->not->toBeNull()
        ->and(
            $profile['aspirations']['career_goals']
        )
        ->toBe([
            'Become a software engineer',
        ])
        ->and(
            $profile[
                'aspirations'
            ]['preferred_work_activities']
        )
        ->toBe([
            'Programming',
            'Problem Solving',
        ]);
});

test('career AI payload excludes identity and private file data', function () {
    $student = User::factory()->create([
        'first_name' => 'SECRET-FIRST-NAME',
        'last_name' => 'SECRET-LAST-NAME',
        'name' => 'SECRET-FULL-NAME',
        'email' => 'secret-email@example.test',
        'student_id' => 'SECRET-STUDENT-ID',
        'programme' => 'Diploma in ICT',
        'cgpa' => 3.50,
        'role' => 'student',
        'avatar' => 'SECRET-AVATAR.jpg',
    ]);

    $now = now();

    DB::table('student_certifications')->insert([
        'user_id' => $student->id,

        'certification_name' =>
            'Safe Certification Name',

        'issuing_organization' =>
            'Safe Organisation',

        'issue_date' => '2026-01-01',
        'expiry_date' => null,

        'credential_id' =>
            'SECRET-CREDENTIAL-ID',

        'credential_url' =>
            'https://secret-credential.example.test',

        'description' =>
            'Safe certification description.',

        'certificate_file_path' =>
            'certificates/SECRET-EVIDENCE-FILE.pdf',

        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('student_projects')->insert([
        'user_id' => $student->id,
        'title' => 'Safe Project',
        'description' => 'Safe project description.',
        'technologies_used' => json_encode([
            'Laravel',
        ]),

        'team_members' => json_encode([
            'SECRET-TEAM-MEMBER',
        ]),

        'role' => 'Developer',

        'project_url' =>
            'https://secret-project.example.test',

        'start_date' => null,
        'end_date' => null,
        'achievements' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $payload = (
        new CareerAiPayloadBuilder()
    )->build(
        $student
    );

    /*
     * The API payload itself should only contain the
     * schema version and anonymous student profile.
     */
    $this->assertSame(
        [
            'schema_version',
            'student_profile',
        ],
        array_keys($payload)
    );

    $profile = $payload['student_profile'];

    /*
     * Direct identity fields must not be included.
     */
    foreach ([
        'id',
        'user_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'student_id',
        'password',
        'avatar',
        'remember_token',
    ] as $privateKey) {
        $this->assertArrayNotHasKey(
            $privateKey,
            $profile
        );
    }

    $certification =
        $profile['certifications'][0];

    /*
     * Certification proof and credential identifiers
     * are stored by Laravel but are not sent to AI.
     */
    foreach ([
        'certificate_file_path',
        'credential_id',
        'credential_url',
    ] as $privateKey) {
        $this->assertArrayNotHasKey(
            $privateKey,
            $certification
        );
    }

    $project =
        $profile['projects'][0];

    /*
     * Team-member identities and project URLs are not
     * currently required by the Career AI payload.
     */
    $this->assertArrayNotHasKey(
        'team_members',
        $project
    );

    $this->assertArrayNotHasKey(
        'project_url',
        $project
    );

    /*
     * Also verify that the actual sensitive test values
     * cannot be found anywhere in the resulting payload.
     */
    $encodedPayload = json_encode(
        $payload
    );

    foreach ([
        'SECRET-FIRST-NAME',
        'SECRET-LAST-NAME',
        'SECRET-FULL-NAME',
        'secret-email@example.test',
        'SECRET-STUDENT-ID',
        'SECRET-AVATAR.jpg',
        'SECRET-CREDENTIAL-ID',
        'secret-credential.example.test',
        'SECRET-EVIDENCE-FILE.pdf',
        'SECRET-TEAM-MEMBER',
        'secret-project.example.test',
    ] as $privateValue) {
        $this->assertStringNotContainsString(
            $privateValue,
            $encodedPayload
        );
    }
});

test('career AI payload handles an incomplete profile safely', function () {
    $student = User::factory()->create([
        'programme' => null,
        'cgpa' => null,
        'role' => 'student',
    ]);

    $payload = (
        new CareerAiPayloadBuilder()
    )->build(
        $student
    );

    $profile = $payload['student_profile'];

    expect($profile['programme'])
        ->toBeNull()
        ->and($profile['cgpa'])
        ->toBeNull()
        ->and($profile['academic_records'])
        ->toBe([])
        ->and($profile['competencies'])
        ->toBe([])
        ->and($profile['interests'])
        ->toBe([])
        ->and($profile['projects'])
        ->toBe([])
        ->and($profile['certifications'])
        ->toBe([])
        ->and($profile['aspirations'])
        ->toBeNull();
});