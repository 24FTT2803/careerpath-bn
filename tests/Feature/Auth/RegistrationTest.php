<?php

use Database\Seeders\OrganisationGroupSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    /*
     * Registering now attaches the student to a programme, so a
     * real group has to exist for the name to resolve to.
     */
    $this->seed(OrganisationGroupSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',

        /*
         * Only Politeknik Brunei and Gmail addresses are
         * accepted, so example.com no longer registers.
         */
        'email' => 'test.user@student.pb.edu.bn',

        'programme' => 'Diploma in ICT (Application Development)',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => 'on',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(
        route('student.dashboard', absolute: false)
    );
});

test('registration refuses an address from outside', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'programme' => 'Diploma in ICT (Application Development)',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => 'on',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});
