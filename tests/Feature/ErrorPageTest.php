<?php

use App\Models\User;

/*
 * Being refused a page is a normal thing to happen — a bookmark
 * kept after a role change, a link passed between students — so
 * what the person sees at that moment should look like the rest
 * of the site and tell them where to go instead of leaving them
 * on Laravel's default page.
 */

test(
    'a refused page explains itself and offers a way on',
    function () {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get(route('admin.business.advertisements.index'));

        $response->assertForbidden();

        /*
         * The reason comes from whichever check refused the
         * request, so it can name the part of the site involved
         * rather than only saying no.
         */
        $response->assertSee('administrator tools', false);

        /*
         * One link for every role: the dashboard route works out
         * where this person belongs.
         */
        $response->assertSee(route('dashboard'), false);
    }
);

test('a lecturer is told which tools these are', function () {
    $lecturer = User::factory()->create(['role' => 'lecturer']);

    $this->actingAs($lecturer)
        ->get(route('admin.business.advertisements.index'))
        ->assertForbidden()
        ->assertSee('administrator tools', false);
});
