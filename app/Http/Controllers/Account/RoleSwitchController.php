<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Moving a special account, or an admin, to another of its roles.
 *
 * Only the roles the account is allowed; anything else is refused.
 */
class RoleSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $role = (string) $request->input('role');

        abort_unless($user->switchRole($role), 403);

        $label = ucfirst($role);

        return redirect()
            ->route('dashboard')
            ->with('success', $role === $user->homeRole()
                ? 'Back to your own role: '.$label.'.'
                : 'You are now using the site as a '.$label.'. Switch back from the bar at the top.');
    }
}
