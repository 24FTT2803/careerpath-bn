<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserPlanGrantController extends Controller
{
    public function index(Request $request): View
    {
        $activeGrants = UserPlanGrant::query()
            ->with(['user', 'plan'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        $revokedGrants = UserPlanGrant::query()
            ->with(['user', 'plan'])
            ->where('is_active', false)
            ->orderByDesc('id')
            ->get();

        /*
         * Only accounts without a live grant are offered, so an
         * administrator cannot stack two grants on one person
         * and then wonder which is in force.
         */
        $grantedUserIds = UserPlanGrant::query()
            ->currentlyActive()
            ->pluck('user_id');

        /*
         * Students only. Staff have no plan and no quota, so
         * granting one to a lecturer would mean nothing.
         */
        $users = User::query()
            ->where('role', 'student')
            ->whereNotIn('id', $grantedUserIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_id']);

        return view(
            'admin.business.grants.index',
            compact('activeGrants', 'revokedGrants', 'users')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'user_id' => [
                    'required',
                    Rule::exists('users', 'id')
                        ->where('role', 'student'),
                ],

                'source' => [
                    'required',
                    'in:admin,sponsorship,trial',
                ],

                'starts_at' => ['nullable', 'date'],

                'ends_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:starts_at',
                ],
            ],
            [
                'ends_at.after_or_equal' => 'The end date must not be before the start date.',
            ]
        );

        /*
         * A grant always confers Premium. There is nothing to
         * grant below it, so offering a choice only invited a
         * mistake.
         */
        UserPlanGrant::create(
            $this->withWindow($validated) + [
                'plan_id' => Plan::where('code', 'premium')
                    ->value('id'),
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('admin.business.grants.index')
            ->with(
                'success',
                'Access granted.'
            );
    }

    /**
     * Withdraw a grant without deleting it.
     *
     * The record is kept so there is a trace of who was given
     * what and when it was taken away.
     */
    public function revoke(UserPlanGrant $grant)
    {
        $grant->update([
            'is_active' => false,
            'ends_at' => $grant->ends_at ?? now(),
        ]);

        return redirect()
            ->route('admin.business.grants.index')
            ->with(
                'success',
                'Access revoked.'
            );
    }

    /**
     * Read the dates as local days.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withWindow(array $validated): array
    {
        $zone = config('app.business_timezone');
        $storage = config('app.timezone');

        if (! empty($validated['starts_at'])) {
            $validated['starts_at'] = Carbon::parse(
                $validated['starts_at'],
                $zone
            )
                ->startOfDay()
                ->setTimezone($storage);
        }

        if (! empty($validated['ends_at'])) {
            $validated['ends_at'] = Carbon::parse(
                $validated['ends_at'],
                $zone
            )
                ->endOfDay()
                ->setTimezone($storage);
        }

        return $validated;
    }
}
