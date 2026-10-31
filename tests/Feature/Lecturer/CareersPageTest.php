<?php

use App\Models\BIICFCareer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a lecturer sees careers without any demand information', function () {
    $career = BIICFCareer::create([
        'job_title' => 'Network Engineer',
        'subsector' => 'Infrastructure',
        'technical_skills' => ['Routing'],
        'soft_skills' => ['Teamwork'],
        'job_description' => 'Keeps networks running.',
        'demand_level' => 'Very High',
    ]);

    $lecturer = User::factory()->create(['role' => 'lecturer']);

    $this->actingAs($lecturer)
        ->get(route('admin.careers.index'))
        ->assertOk()
        ->assertSee('Network Engineer')
        ->assertDontSeeText('Demand')
        ->assertDontSee('demand-badge');

    $this->actingAs($lecturer)
        ->get(route('admin.careers.index', ['demand' => 'Low']))
        ->assertOk()
        ->assertSee('Network Engineer');

    $this->actingAs($lecturer)
        ->get(route('admin.careers.show', $career))
        ->assertOk()
        ->assertSee('Network Engineer')
        ->assertDontSeeText('Demand');
});
