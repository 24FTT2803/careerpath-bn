<?php

use App\Models\User;

/*
 * The footer on every signed-in page.
 *
 * Its Resources column held four links: three to the BIICF
 * Explorer, which Quick Links already has, and "Career Guide",
 * which pointed nowhere. The Instagram row spelled out "CareerPath
 * BN on Instagram" beside an Instagram icon that already says so.
 */

function footerPage(): string
{
    return test()
        ->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.dashboard'))
        ->assertOk()
        ->getContent();
}

test('the footer has no Resources column', function () {
    $html = footerPage();

    expect($html)
        ->not->toContain('<h4>Resources</h4>')
        ->not->toContain('Career Guide')
        ->not->toContain('ICT Sub-Sectors');
});

test('the Instagram row is the handle and the icon, nothing more', function () {
    $html = footerPage();

    expect($html)
        ->toContain('@careerpathbn')
        ->toContain('fa-instagram')
        ->not->toContain('CareerPath BN on Instagram');
});

test('the columns that were kept are still there', function () {
    $html = footerPage();

    expect($html)
        ->toContain('<h4>Quick Links</h4>')
        ->toContain('<h4>Contact</h4>');
});

test('Innovation Lab footer links use its Instagram profile on public student and admin pages', function () {
    $pages = [$this->get('/')];
    $pages[] = $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.dashboard'));
    $pages[] = $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.dashboard'));

    foreach ($pages as $page) {
        $page->assertOk()
            ->assertSee('https://www.instagram.com/innovationlab.bn/', false)
            ->assertDontSee('https://www.facebook.com/innovationlab.bn/', false);
    }
});
