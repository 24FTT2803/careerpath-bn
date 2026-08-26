<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\StudentAspiration;
use App\Helpers\NotificationHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email', // This ensures email is unique
                'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|pb\.edu\.bn|student\.pb\.edu\.bn)$/',
            ],
            'programme' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'This email is already registered. Please use a different email address.',
            'email.regex' => 'Please use a valid email address from gmail.com, pb.edu.bn, or student.pb.edu.bn',
            'email.required' => 'Email address is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'terms.required' => 'You must agree to the Terms of Service.',
        ]);

        // Combine first and last name
        $fullName = $request->first_name . ' ' . $request->last_name;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $fullName,
            'email' => $request->email,
            'programme' => $request->programme,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        // Create default student profile
        StudentProfile::create([
            'user_id' => $user->id,
        ]);

        // Create default aspirations
        StudentAspiration::create([
            'user_id' => $user->id,
        ]);

        // Log activity - New student registration
        NotificationHelper::logStudentRegistration($user->id, $fullName);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('student.dashboard');
    }
}