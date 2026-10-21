<?php

test('the legal pages show a fixed last updated date', function (string $page) {
    $this->get(url($page))
        ->assertOk()
        ->assertSee('Last updated: 10 October 2026')
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
