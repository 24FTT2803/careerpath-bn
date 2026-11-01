<?php

use App\Models\User;

/*
 * The admin and lecturer side menu can be resized by its edge,
 * narrowed to icons, and hidden completely: by its own buttons,
 * which work without a keyboard, by dragging, and by Ctrl+B or
 * Cmd+B. Its state is set in the page's head, so the menu never
 * opens and then snaps shut as a page loads.
 */
test('the side menu can be resized and hidden by admins and lecturers', function (string $role, string $route) {
    $user = User::factory()->create(['role' => $role]);

    $html = $this->actingAs($user)
        ->get(route($route))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('id="sidebarToggle"')
        ->toContain('id="sidebarOpen"')
        ->toContain('id="sidebarResizer"')
        ->toContain('id="sidebarEdge"')
        ->toContain('aria-controls="adminSidebar"')
        ->toContain('id="adminSidebar"')
        ->toContain("localStorage.getItem('cpbn.sidebar')")
        /* Nothing on the page announces the shortcut. */
        ->not->toContain('Ctrl+B')
        ->not->toContain('⌘B');

    /* Remembered before the page draws: the head script comes before the menu. */
    expect(strpos($html, "setProperty('--sidebar-width'"))->toBeLessThan(strpos($html, 'id="adminSidebar"'));
})->with([
    'admin' => ['admin', 'admin.dashboard'],
    'lecturer' => ['lecturer', 'lecturer.dashboard'],
]);
