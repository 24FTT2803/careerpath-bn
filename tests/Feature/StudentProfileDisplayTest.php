<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->student = User::factory()->create([
        'role' => 'student',
        'student_id' => '24FTT2803',
    ]);
});

test('project descriptions retain their full escaped text with an accessible read more control', function () {
    $description = str_repeat('Built an application for our community. ', 8).'<script>alert(1)</script>';
    $project = $this->student->projects()->create([
        'title' => 'Community application',
        'description' => $description,
    ]);

    $this->actingAs($this->student)->get(route('student.profile'))
        ->assertOk()
        ->assertSee($description)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('aria-controls="project-description-'.$project->id.'"', false)
        ->assertSeeText('Read more');
});

test('career aspirations use the same stacked label and value layout', function () {
    $this->student->aspirations()->create([
        'career_goals' => ['Application Developer'],
        'vision_statement' => 'Build useful applications.',
        'long_term_goals' => 'Lead a development team.',
    ]);

    $html = $this->actingAs($this->student)->get(route('student.profile'))->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    foreach (['Dream Career', 'Vision Statement', 'Long Term Goals'] as $label) {
        expect($xpath->query('//div[contains(@class,"info-row-long")]/span[normalize-space(.)="'.$label.'"]')->length)->toBe(1);
    }
});

test('certification evidence is linked from the profile and opens through its protected route', function () {
    Storage::fake('local');
    Storage::disk('local')->put('certificates/course.pdf', "%PDF-1.4\nCourse certificate");
    $certificate = $this->student->certifications()->create([
        'certification_name' => 'Software Development',
        'certificate_file_path' => 'certificates/course.pdf',
        'certificate_original_name' => 'Course certificate.pdf',
    ]);
    $url = route('student.certifications.evidence', $certificate);

    $this->actingAs($this->student)->get(route('student.profile'))
        ->assertOk()->assertSee('href="'.$url.'"', false)->assertSeeText('View evidence');
    $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toStartWith('inline;');

    $this->actingAs(User::factory()->create(['role' => 'student']))->get($url)->assertNotFound();
});

test('the date input keeps the saved birthday after refresh', function () {
    $this->student->profile()->create(['date_of_birth' => '2006-03-09']);

    $this->actingAs($this->student);
    for ($refresh = 0; $refresh < 2; $refresh++) {
        $this->get(route('student.profile.edit'))->assertOk()->assertSee('value="2006-03-09"', false);
    }
});

test('the date input prefers submitted old input after validation errors', function (string $value) {
    $this->student->profile()->create(['date_of_birth' => '2006-03-09']);

    $html = $this->actingAs($this->student)
        ->withSession(['_old_input' => ['date_of_birth' => $value]])
        ->get(route('student.profile.edit'))->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    expect($xpath->query('//input[@name="date_of_birth"]')->item(0)->getAttribute('value'))->toBe($value);
})->with(['2007-04-10', '']);

test('birth dates require an age of at least fifteen using the business date', function (string $birthday, bool $valid) {
    $this->travelTo(now()->setTimezone(config('app.business_timezone'))->setDate(2026, 10, 31)->startOfDay());

    $response = $this->actingAs($this->student)
        ->from(route('student.profile.edit'))
        ->put(route('student.profile.update'), [
            'name' => $this->student->name,
            'student_id' => $this->student->student_id,
            'date_of_birth' => $birthday,
        ]);

    if ($valid) {
        $response->assertSessionHasNoErrors();
        expect($this->student->fresh()->profile->date_of_birth?->format('Y-m-d'))->toBe($birthday ?: null);
    } else {
        $response->assertSessionHasErrors('date_of_birth');
    }
})->with([
    ['2011-10-31', true],
    ['2011-10-30', true],
    ['2011-11-01', false],
    ['2026-10-31', false],
    ['2027-01-01', false],
    ['', true],
]);

test('the birth date picker uses the same fifteen year cutoff as validation', function () {
    $this->travelTo(now()->setTimezone(config('app.business_timezone'))->setDate(2026, 10, 31)->startOfDay());

    $html = $this->actingAs($this->student)->get(route('student.profile.edit'))->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    expect($xpath->query('//input[@name="date_of_birth"]')->item(0)->getAttribute('max'))->toBe('2011-10-31');
});

test('field of study has its own question mark help for saved and newly added qualifications', function () {
    $this->student->academicRecords()->create([
        'institution_name' => 'Example School',
        'programme_name' => 'School Certificate',
        'level' => 'Certificate',
        'field_of_study' => 'Science',
        'start_date' => '2020-01-01',
    ]);

    $html = $this->actingAs($this->student)->get(route('student.profile.edit'))->assertOk()->getContent();
    expect(substr_count($html, 'aria-label="Help: Field of Study"'))->toBeGreaterThanOrEqual(3);
    expect($html)->toContain('id="field-of-study-help-template"', 'The main subject area of your qualification');
});
