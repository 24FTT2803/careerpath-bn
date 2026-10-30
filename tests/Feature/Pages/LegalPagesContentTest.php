<?php

test('the legal pages show a fixed last updated date', function (string $page) {
    $this->get(url($page))
        ->assertOk()
        ->assertSee('Last updated: 30 October 2026')
        ->assertSee('The short version')
        ->assertSee('href="'.route('terms').'"', false)
        ->assertSee('href="'.route('privacy').'"', false);
})->with(['/privacy', '/terms']);

test('the privacy policy describes how the platform works today', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('OpenRouter, Inc.')
        ->assertSee('Groq, Inc.')
        ->assertSee('noreply@careerpathbn.tech')
        ->assertSee('Only students in the classes they are assigned to.')
        ->assertSee('cannot read your')
        ->assertSee('advertising helps keep free access free, so it is shown and cannot be switched off.')
        ->assertSee('IP address and browser type')
        ->assertSee('"Remember me" cookie', false)
        ->assertDontSee('preferred industries');
});

test('the terms describe plans, sponsorship and verification', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('No payment is requested or taken')
        ->assertSee('Sponsored access is always Premium')
        ->assertSee('must confirm it before using the Platform')
        ->assertSee('once it is verified it is locked')
        ->assertSee('Governing Law');
});

test('terms explain demo premium and eligibility for graduates', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSeeText('graduates (alumni)')
        ->assertSeeText('postgraduate students')
        ->assertSeeText('we do not accept payments or collect payment details for this demonstration.')
        ->assertSeeText('Activating Premium does not charge you or create a paid subscription.')
        ->assertSeeText('@student.pb.edu.bn')
        ->assertSeeText('@pb.edu.bn')
        ->assertSeeText('Personal Gmail addresses are not accepted for student registration.')
        ->assertDontSeeText('You must be a current student or member of staff of Politeknik Brunei.')
        ->assertDontSeeText('no longer belongs to a current student or staff member');
});

test('both legal pages label the institutional telephone number', function (string $page) {
    $this->get(url($page))
        ->assertOk()
        ->assertSeeText('+673 2234630')
        ->assertSeeText('Politeknik Brunei official contact number');
})->with(['/terms', '/privacy']);

test('privacy no longer lists the preferences card', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertDontSee('<strong><i class="fas fa-rectangle-ad"></i> Preferences</strong>', false)
        ->assertDontSeeText('Your advertising choice, where your plan lets you choose.')
        ->assertSeeText('Information you give us')
        ->assertSeeText('Certifications');
});
