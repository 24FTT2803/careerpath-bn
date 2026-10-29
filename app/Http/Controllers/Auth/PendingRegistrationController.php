<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\StudentAspiration;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\Business\ProgrammeEnrolmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The two links in the sign-up email.
 *
 * Opening a link only shows a page with a button; the button
 * does the work. Mail scanners, Microsoft's included, open every
 * link in a message to check it, and if opening were enough they
 * would create the account and discard it in the same second.
 */
class PendingRegistrationController extends Controller
{
    public function showAuthenticate(Request $request, int $pending, string $token): View
    {
        $registration = $this->usable($request, $pending, $token);

        if ($registration === null) {
            return $this->invalid();
        }

        return view('auth.registration-status', [
            'state' => 'authenticate',
            'email' => $registration->email,
            'action' => $request->fullUrl(),
        ]);
    }

    public function authenticate(
        Request $request,
        int $pending,
        string $token,
        ProgrammeEnrolmentService $enrolment
    ): View|RedirectResponse {
        $registration = $this->usable($request, $pending, $token);

        if ($registration === null) {
            return $this->invalid();
        }

        if (User::where('email', $registration->email)->exists()) {
            $registration->delete();

            return $this->invalid(
                'This email address already has an account. Please sign in.'
            );
        }

        $user = DB::transaction(function () use ($registration): User {
            /*
             * The address has just been proved, so the account
             * starts confirmed, and a Student ID taken from a
             * student.pb.edu.bn mailbox is approved with it.
             */
            $studentId = $registration->student_id
                && ! User::where('student_id', $registration->student_id)->exists()
                    ? $registration->student_id
                    : null;

            $user = new User;

            $user->forceFill([
                'name' => $registration->name,
                'email' => $registration->email,
                'programme' => $registration->programme,
                'password' => $registration->password,
                'student_id' => $studentId,
                'role' => 'student',
                'email_verified_at' => now(),
            ])->save();

            StudentProfile::create(['user_id' => $user->id]);
            StudentAspiration::create(['user_id' => $user->id]);

            $registration->delete();

            return $user;
        });

        $enrolment->syncFor($user->fresh(), $user->programme);

        NotificationHelper::logStudentRegistration($user->id, $user->name);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        $request->session()->forget(RegisteredUserController::PENDING_EMAIL_KEY);

        /*
         * Marks the redirect that follows as the student's
         * first view of the dashboard, so the header can
         * greet them without saying "Welcome back" to an
         * account they created seconds ago.
         */
        $request->session()->flash('first_visit', true);

        return redirect()->route('student.dashboard');
    }

    public function showDecline(Request $request, int $pending, string $token): View
    {
        $registration = $this->usable($request, $pending, $token);

        if ($registration === null) {
            return $this->invalid();
        }

        return view('auth.registration-status', [
            'state' => 'decline',
            'email' => $registration->email,
            'action' => $request->fullUrl(),
        ]);
    }

    public function decline(Request $request, int $pending, string $token): View
    {
        $registration = $this->usable($request, $pending, $token);

        if ($registration === null) {
            return $this->invalid();
        }

        $email = $registration->email;

        $registration->delete();

        return view('auth.registration-status', [
            'state' => 'declined',
            'email' => $email,
        ]);
    }

    /**
     * The sign-up a link points at, if the link is still good:
     * signed by us, not expired, and from the latest email sent.
     */
    private function usable(Request $request, int $pending, string $token): ?PendingRegistration
    {
        if (! $request->hasValidSignature()) {
            return null;
        }

        $registration = PendingRegistration::find($pending);

        if ($registration === null
            || $registration->hasExpired()
            || ! $registration->tokenMatches($token)) {
            return null;
        }

        return $registration;
    }

    private function invalid(?string $message = null): View
    {
        return view('auth.registration-status', [
            'state' => 'invalid',
            'message' => $message,
        ]);
    }
}
