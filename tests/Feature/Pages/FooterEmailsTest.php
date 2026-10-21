<?php

use App\Models\User;

test('the homepage footer explains which email is which', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee("This is Politeknik Brunei's official email.", false)
        ->assertSee("This is the CareerPath BN team's business email.", false)
        ->assertSee('data-email-title="Email Politeknik Brunei"', false)
        ->assertSee('data-email-title="Email CareerPath BN"', false);
});

test('the student footer explains which email is which', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee("Politeknik Brunei's official email, for questions about your studies", false)
        ->assertSee("CareerPath BN's business email, for help with this website", false);
});

test('the privacy and terms pages label both emails', function (string $page) {
    $this->get(url($page))
        ->assertOk()
        ->assertSee('(Politeknik Brunei, for study and school matters)')
        ->assertSee('(CareerPath BN business email, for help with this website)');
})->with(['/privacy', '/terms']);
