<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\AuthenticateRegistrationNotification;
use App\Rules\NoProfanity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

/**
 * Signing up.
 *
 * Only Politeknik Brunei addresses are accepted. The account is
 * not created here: the details wait as a pending sign-up, and
 * an email to the address asks its owner to authenticate (which
 * creates the account) or say it was not them (which discards
 * it). See PendingRegistrationController.
 */
class RegisteredUserController extends Controller
{
    /**
     * Addresses a student may sign up with.
     */
    public const SIGN_UP_DOMAINS = [
        User::STUDENT_EMAIL_DOMAIN,
        'pb.edu.bn',
    ];

    public const PENDING_EMAIL_KEY = 'registration.pending_email';

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255', new NoProfanity],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[a-z0-9._%+-]+@(student\.pb\.edu\.bn|pb\.edu\.bn)$/',
            ],
            'programme' => [
                'required',
                'string',
                'exists:organisation_groups,name',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ], [
            'name.required' => 'Please enter your full name.',
            'email.unique' => 'This email is already registered. Please sign in instead.',
            'email.regex' => 'Please use your Politeknik Brunei email address, ending in @student.pb.edu.bn or @pb.edu.bn.',
            'email.required' => 'Email address is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'terms.required' => 'You must agree to the Terms of Service.',
            'programme.exists' => 'Please select a valid programme.',
        ]);

        $validator->after(function (Validator $validator) use ($request): void {
            if ($validator->errors()->has('email')) {
                return;
            }

            if (! self::isStudentAddress($request->input('email'))) {
                return;
            }

            $studentId = self::studentIdFromEmail($request->input('email'));

            if ($studentId === null) {
                $validator->errors()->add(
                    'email',
                    'A student.pb.edu.bn address must be your Student ID, like 24FTT2803@student.pb.edu.bn.'
                );

                return;
            }

            if (User::where('student_id', $studentId)->exists()) {
                $validator->errors()->add(
                    'email',
                    "An account with Student ID {$studentId} already exists. If it is yours, please contact an administrator."
                );
            }
        });

        $validated = $validator->validate();

        PendingRegistration::expired()->delete();

        $token = Str::random(64);

        /*
         * One sign-up per address. Signing up again replaces the
         * earlier one, and the new token leaves the earlier
         * email's links unusable.
         */
        $pending = PendingRegistration::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'programme' => $validated['programme'],
                'password' => Hash::make($validated['password']),
                'student_id' => self::studentIdFromEmail($validated['email']),
                'token_hash' => PendingRegistration::hashToken($token),
                'expires_at' => now()->addMinutes(self::expiryMinutes()),
            ]
        );

        self::sendAuthenticationEmail($pending, $token);

        $request->session()->put(self::PENDING_EMAIL_KEY, $pending->email);

        return redirect()->route('register.sent');
    }

    /**
     * "Check your email", after signing up.
     */
    public function sent(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get(self::PENDING_EMAIL_KEY);

        if (! $email) {
            return redirect()->route('register');
        }

        return view('auth.registration-status', [
            'state' => 'sent',
            'email' => $email,
        ]);
    }

    /**
     * Send the authentication email again, with fresh links.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get(self::PENDING_EMAIL_KEY);

        $pending = $email
            ? PendingRegistration::where('email', $email)->first()
            : null;

        if ($pending === null || $pending->hasExpired()) {
            $request->session()->forget(self::PENDING_EMAIL_KEY);

            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'That sign-up has expired. Please register again.',
                ]);
        }

        $token = Str::random(64);

        $pending->update([
            'token_hash' => PendingRegistration::hashToken($token),
            'expires_at' => now()->addMinutes(self::expiryMinutes()),
        ]);

        self::sendAuthenticationEmail($pending, $token);

        return redirect()
            ->route('register.sent')
            ->with('status', 'registration-link-sent');
    }

    /**
     * The Student ID a student.pb.edu.bn mailbox names, or null
     * when the address is not one or its mailbox is not an ID.
     */
    public static function studentIdFromEmail(?string $email): ?string
    {
        if (! self::isStudentAddress($email)) {
            return null;
        }

        $studentId = User::normaliseStudentId(Str::before((string) $email, '@'));

        return $studentId !== null && preg_match(User::STUDENT_ID_PATTERN, $studentId)
            ? $studentId
            : null;
    }

    private static function isStudentAddress(?string $email): bool
    {
        return Str::lower(Str::after((string) $email, '@')) === User::STUDENT_EMAIL_DOMAIN;
    }

    private static function expiryMinutes(): int
    {
        return (int) config('auth.verification.expire', 60);
    }

    private static function sendAuthenticationEmail(PendingRegistration $pending, string $token): void
    {
        Notification::route('mail', $pending->email)
            ->notify(new AuthenticateRegistrationNotification($pending, $token));
    }
}
