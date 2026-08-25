<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display list of all users (Admin only)
     */
    public function index(Request $request)
    {
        $query = User::query();

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
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,lecturer,admin',
            'password' => 'required|min:8|confirmed',
        ];

        // Email validation with role-based domains
        $rules['email'] = User::getEmailValidationRules($request->role);
        $rules['email'][] = 'unique:users';

        // Phone validation
        $rules['phone'] = User::getPhoneValidationRules();

        // Only require student_id and programme if role is student
        if ($request->role === 'student') {
            $rules['student_id'] = 'required|unique:users';
            $rules['programme'] = 'required|string';
        } else {
            $rules['student_id'] = 'nullable|unique:users';
            $rules['programme'] = 'nullable|string';
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'student_id' => $request->student_id,
            'programme' => $request->programme,
        ]);

        // Create student profile with phone number if provided
        StudentProfile::create([
            'user_id' => $user->id,
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
    public function update(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,lecturer,admin',
        ];

        // Email validation with role-based domains
        $rules['email'] = User::getEmailValidationRules($request->role);
        $rules['email'][] = 'unique:users,email,' . $id;

        // Phone validation
        $rules['phone'] = User::getPhoneValidationRules();

        // Only require student_id and programme if role is student
        if ($request->role === 'student') {
            $rules['student_id'] = 'required|unique:users,student_id,' . $id;
            $rules['programme'] = 'required|string';
        } else {
            $rules['student_id'] = 'nullable|unique:users,student_id,' . $id;
            $rules['programme'] = 'nullable|string';
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'student_id' => $request->role === 'student' ? $request->student_id : null,
            'programme' => $request->role === 'student' ? $request->programme : null,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

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