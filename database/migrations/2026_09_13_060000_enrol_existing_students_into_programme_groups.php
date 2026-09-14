<?php

use App\Models\User;
use App\Services\Business\ProgrammeEnrolmentService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Put existing students into their programme group.
     *
     * Until now a programme was a string that matched nothing,
     * so sponsoring a school or targeting a programme reached
     * nobody. This connects the students already on record.
     */
    public function up(): void
    {
        $service = app(ProgrammeEnrolmentService::class);

        User::query()
            ->where('role', 'student')
            ->whereNotNull('programme')
            ->cursor()
            ->each(function (User $student) use ($service) {
                $service->syncFor(
                    $student,
                    $student->programme
                );
            });
    }

    /**
     * Left in place deliberately.
     *
     * Removing the memberships would be guesswork: by the time
     * this is reversed some may have been assigned by hand, and
     * there is no way to tell those apart.
     */
    public function down(): void {}
};
