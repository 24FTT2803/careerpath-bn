<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AI\RecommendationStatusService;
use App\Services\Business\ProgrammeEnrolmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoProfanity;

class UserController extends Controller
{
    /**
     * Display list of all users (Admin only)
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->with('profile');

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('student_id', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Create a new user (Admin only)
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user (Admin only)
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', new NoProfanity],
            'role' => 'required|in:student,lecturer,admin',
            'password' => 'required|min:8|confirmed',
        ];

        // Email validation with role-based domains
        $rules['email'] = User::getEmailValidationRules($request->role);
        $rules['email'][] = 'unique:users';

        // Phone: valid for the chosen country, never shared between accounts
        $rules += User::phoneWithCountryRules();

            $request->merge([
            'student_id' => User::normaliseStudentId($request->input('student_id')),
        ]);

        // Only require student_id and programme if role is student
        if ($request->role === 'student') {
            $rules['student_id'] = User::studentIdRules();
            $rules['programme'] =
                'required|string|exists:organisation_groups,name';
        } else {
            $rules['student_id'] = ['exclude'];
            $rules['programme'] = 'nullable|string';
        }

        $request->validate($rules, User::studentIdMessages() + User::phoneWithCountryMessages());

        $phone = User::standardisePhone($request->phone, $request->phone_country);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
                        'student_id' => $request->role === 'student'
                ? $request->student_id
                : null,
            'programme' => $request->role === 'student'
                ? $request->programme
                : null,
        ]);

        /*
         * An ID entered by an admin counts as verified by them.
         */
        if ($user->student_id !== null) {
            $user->forceFill([
                'student_id_verified_at' => now(),
                'student_id_verified_by' => $request->user()->id,
            ])->save();
        }

        app(ProgrammeEnrolmentService::class)->syncFor(
            $user->fresh(),
            $user->programme
        );

        // Create student profile with phone number if provided
        StudentProfile::create([
            'user_id' => $user->id,
            'phone' => $phone,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Edit user (Admin only)
     */
    public function edit($id)
    {
        $user = User::with('profile')->findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user (Admin only)
     */
    public function update(
        Request $request,
        $id,
        RecommendationStatusService $recommendationStatus
    ) {
        $user = User::with('profile')->findOrFail($id);

        $rules = [
            'name' => ['required', 'string', 'max:255', new NoProfanity],
            'role' => 'required|in:student,lecturer,admin',
        ];

        // Email validation with role-based domains
        $rules['email'] = User::getEmailValidationRules($request->role);
        $rules['email'][] = 'unique:users,email,'.$id;

        // Phone: valid for the chosen country, never shared between accounts
        $rules += User::phoneWithCountryRules();

            $request->merge([
            'student_id' => User::normaliseStudentId($request->input('student_id')),
        ]);

        // Only require student_id and programme if role is student
        if ($request->role === 'student') {
            $rules['student_id'] = User::studentIdRules((int) $id);
            $rules['programme'] =
                'required|string|exists:organisation_groups,name';
        } else {
            $rules['student_id'] = ['exclude'];
            $rules['programme'] = 'nullable|string';
        }

        $request->validate($rules, User::studentIdMessages() + User::phoneWithCountryMessages());

        $phone = User::standardisePhone($request->phone, $request->phone_country, $user->id);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'role' => $request->role,
            'student_id' => $request->role === 'student' ? $request->student_id : null,
            'programme' => $request->role === 'student' ? $request->programme : null,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->fill($data);

        /*
         * An admin entering or changing a student's ID vouches
         * for it, so it is verified in the same save.
         */
        if ($user->isDirty('student_id') && $user->student_id !== null) {
            $user->student_id_verified_at = now();
            $user->student_id_verified_by = $request->user()->id;
        }

        $user->save();

        /* Keep the profile's copy of the phone in step, as the student's own form does. */
        $user->profile?->update(['phone' => $phone]);

        app(ProgrammeEnrolmentService::class)->syncFor(
            $user->fresh(),
            $user->programme
        );

        /*
         * An administrator can change a student's programme,
         * which is part of the AI-relevant profile.
         */
        $recommendationStatus->refreshFor($user->fresh());

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
