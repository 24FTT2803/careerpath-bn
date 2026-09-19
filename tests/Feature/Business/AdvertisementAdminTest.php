<?php

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

test(
    'an admin can create an advertisement',
    function () {
        Storage::fake('public');

        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Open day',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'asset' => UploadedFile::fake()
                        ->image('open-day.jpg'),
                    'click_url' => 'https://example.com/open-day',
                    'alt_text' => 'Open day poster',
                    'is_active' => '1',
                ]
            )
            ->assertRedirect(
                route('admin.business.advertisements.index')
            );

        $advertisement = Advertisement::firstOrFail();

        expect($advertisement->title)
            ->toBe('Open day')
            ->and($advertisement->is_active)
            ->toBeTrue()
            ->and($advertisement->asset_path)
            ->not->toBeNull();

        Storage::disk('public')->assertExists(
            $advertisement->asset_path
        );
    }
);

test(
    'an advertisement can point at an external address instead',
    function () {
        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Network slot',
                    'type' => Advertisement::TYPE_NETWORK,
                    'position' => Advertisement::POSITION_TWO,
                    'external_url' => 'https://ads.example.com/slot',
                    'is_active' => '1',
                ]
            )
            ->assertRedirect();

        expect(
            Advertisement::firstOrFail()->external_url
        )->toBe('https://ads.example.com/slot');
    }
);

test(
    'dates are read in the institution timezone',
    function () {
        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Starting today',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'external_url' => 'https://example.com/a.png',
                    'starts_at' => now()
                        ->timezone(config('app.business_timezone'))
                        ->toDateString(),
                    'ends_at' => now()
                        ->timezone(config('app.business_timezone'))
                        ->toDateString(),
                    'is_active' => '1',
                ]
            )
            ->assertRedirect();

        /*
         * An advertisement set to run today must be running
         * today, at whatever hour an administrator happens to
         * create it.
         */
        expect(
            Advertisement::currentlyRunning()->count()
        )->toBe(1);
    }
);

test(
    'an end date before the start date is rejected',
    function () {
        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Backwards',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'external_url' => 'https://example.com/a.png',
                    'starts_at' => now()->addWeek()->toDateString(),
                    'ends_at' => now()->toDateString(),
                ]
            )
            ->assertSessionHasErrors('ends_at');

        expect(Advertisement::count())->toBe(0);
    }
);

test(
    'editing without a new upload keeps the existing file',
    function () {
        Storage::fake('public');

        $advertisement = Advertisement::create([
            'title' => 'Original',
            'type' => Advertisement::TYPE_IMAGE,
            'asset_path' => 'advertisements/original.png',
            'position' => Advertisement::POSITION_ONE,
            'is_active' => true,
        ]);

        $this->actingAs(adminUser())
            ->put(
                route(
                    'admin.business.advertisements.update',
                    $advertisement
                ),
                [
                    'title' => 'Renamed',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'is_active' => '1',
                ]
            )
            ->assertRedirect();

        $advertisement->refresh();

        expect($advertisement->title)
            ->toBe('Renamed')
            ->and($advertisement->asset_path)
            ->toBe('advertisements/original.png');
    }
);

test(
    'deleting an advertisement removes its uploaded file',
    function () {
        Storage::fake('public');

        Storage::disk('public')->put(
            'advertisements/doomed.png',
            'x'
        );

        $advertisement = Advertisement::create([
            'title' => 'Doomed',
            'type' => Advertisement::TYPE_IMAGE,
            'asset_path' => 'advertisements/doomed.png',
            'position' => Advertisement::POSITION_ONE,
            'is_active' => true,
        ]);

        $this->actingAs(adminUser())
            ->delete(
                route(
                    'admin.business.advertisements.destroy',
                    $advertisement
                )
            )
            ->assertRedirect();

        expect(Advertisement::count())->toBe(0);

        Storage::disk('public')->assertMissing(
            'advertisements/doomed.png'
        );
    }
);

test(
    'a student cannot reach the advertisement screens',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->get(route('admin.business.advertisements.index'))
            ->assertForbidden();
    }
);

test(
    'a lecturer cannot manage advertisements',
    function () {
        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        /*
         * Advertising is a commercial setting rather than part
         * of the teaching role.
         */
        $this->actingAs($lecturer)
            ->get(route('admin.business.advertisements.index'))
            ->assertForbidden();
    }
);

test(
    'status reflects the dates rather than the switch',
    function () {
        $scheduled = Advertisement::create([
            'title' => 'Next month',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/a.png',
            'position' => Advertisement::POSITION_ONE,
            'is_active' => true,
            'starts_at' => now()->addWeek(),
        ]);

        $ended = Advertisement::create([
            'title' => 'Last term',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/b.png',
            'position' => Advertisement::POSITION_TWO,
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $paused = Advertisement::create([
            'title' => 'Switched off',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/c.png',
            'position' => Advertisement::POSITION_ONE,
            'is_active' => false,
        ]);

        /*
         * Calling a future-dated advertisement "active" is how
         * an administrator concludes the system is broken.
         */
        expect($scheduled->status())
            ->toBe('scheduled')
            ->and($ended->status())
            ->toBe('ended')
            ->and($paused->status())
            ->toBe('paused');
    }
);

test(
    'the list can be filtered by status',
    function () {
        Advertisement::create([
            'title' => 'Running now',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/a.png',
            'position' => Advertisement::POSITION_ONE,
            'is_active' => true,
        ]);

        Advertisement::create([
            'title' => 'Next month',
            'type' => Advertisement::TYPE_IMAGE,
            'external_url' => 'https://example.com/b.png',
            'position' => Advertisement::POSITION_TWO,
            'is_active' => true,
            'starts_at' => now()->addWeek(),
        ]);

        $this->actingAs(adminUser())
            ->get(
                route(
                    'admin.business.advertisements.index',
                    ['status' => 'scheduled']
                )
            )
            ->assertOk()
            ->assertSee('Next month')
            ->assertDontSee('Running now');
    }
);

test(
    'an image cropped in the browser is stored',
    function () {
        Storage::fake('public');

        $pixel = 'data:image/png;base64,'
            .'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Cropped banner',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'cropped_asset' => $pixel,
                    'is_active' => '1',
                ]
            )
            ->assertRedirect();

        $advertisement = Advertisement::firstOrFail();

        expect($advertisement->asset_path)->not->toBeNull();

        Storage::disk('public')->assertExists(
            $advertisement->asset_path
        );
    }
);

test(
    'an upload has to match the advertisement type',
    function () {
        Storage::fake('public');

        /*
         * Nothing checked this before, so a video could be
         * stored as an image and the page would render a broken
         * picture.
         */
        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Wrong kind of file',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'asset' => UploadedFile::fake()->create(
                        'clip.mp4',
                        100,
                        'video/mp4'
                    ),
                    'is_active' => '1',
                ]
            )
            ->assertSessionHasErrors('asset');

        expect(Advertisement::count())->toBe(0);
    }
);

test(
    'an animated gif is accepted as an image',
    function () {
        Storage::fake('public');

        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Animated banner',
                    'type' => Advertisement::TYPE_IMAGE,
                    'position' => Advertisement::POSITION_ONE,
                    'asset' => UploadedFile::fake()->create(
                        'banner.gif',
                        200,
                        'image/gif'
                    ),
                    'is_active' => '1',
                ]
            )
            ->assertRedirect();

        expect(
            Advertisement::where('title', 'Animated banner')->exists()
        )->toBeTrue();
    }
);

test(
    'a link advertisement rejects an upload',
    function () {
        Storage::fake('public');

        $this->actingAs(adminUser())
            ->post(
                route('admin.business.advertisements.store'),
                [
                    'title' => 'Text only',
                    'type' => Advertisement::TYPE_LINK,
                    'position' => Advertisement::POSITION_ONE,
                    'click_url' => 'https://example.com',
                    'asset' => UploadedFile::fake()->image('banner.png'),
                    'is_active' => '1',
                ]
            )
            ->assertSessionHasErrors('asset');
    }
);
