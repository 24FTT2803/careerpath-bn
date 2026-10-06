<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NoProfanity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

/**
 * A lecturer's own account: name, phone, profile picture,
 * password, and closing the account.
 */
class SettingsController extends Controller
{
    public function show(): View
    {
        $user = $this->lecturer();

        return view('lecturer.settings', [
            'user' => $user,
            'classes' => $user->assignedGroups()->orderBy('name')->get(),
        ]);
    }

    /**
     * Change or remove the profile picture on its own, from the
     * badge, without touching the rest of the profile.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = $this->lecturer();

        $request->validate([
            'avatar' => [
                'required_without:remove_avatar',
                'nullable',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb'),
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'avatar.required_without' => 'Choose a picture to upload.',
        ]);

        if ($request->hasFile('avatar')) {
            $this->deleteAvatarFile($user->avatar);
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);

            return redirect()
                ->route('lecturer.settings')
                ->with('success', 'Your profile picture has been updated.');
        }

        $this->deleteAvatarFile($user->avatar);
        $user->update(['avatar' => null]);

        return redirect()
            ->route('lecturer.settings')
            ->with('success', 'Your profile picture has been removed.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->lecturer();

        $request->validate([
            'name' => ['required', 'string', 'max:255', new NoProfanity],
            'phone' => ['nullable', 'string', 'max:30', (new Phone)->countryField('phone_country')],
            'phone_country' => ['nullable', 'required_with:phone', 'string', 'size:2'],
            'avatar' => [
                'nullable',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb'),
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Please enter your full name.',
            'phone.phone' => 'Enter a valid phone number for the selected country.',
            'phone.max' => 'The phone number is too long.',
            'phone_country.required_with' => 'Please select a country for the phone number.',
            'phone_country.size' => 'The selected phone country is invalid.',
        ]);

        /*
         * Same rule as students: store one standard format
         * (e.g. +6737123456) and never share a number between
         * two accounts.
         */
        $phone = null;

        if ($request->filled('phone')) {
            $phone = (new PhoneNumber(
                $request->input('phone'),
                strtoupper($request->input('phone_country'))
            ))->formatE164();

            $taken = User::query()
                ->where('phone', $phone)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'phone' => 'This phone number is already registered to another account.',
                ]);
            }
        }

        $avatar = $user->avatar;

        if ($request->hasFile('avatar')) {
            $this->deleteAvatarFile($avatar);
            $avatar = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatarFile($avatar);
            $avatar = null;
        }

        $user->update([
            'name' => $request->input('name'),
            'phone' => $phone,
            'avatar' => $avatar,
        ]);

        return redirect()
            ->route('lecturer.settings')
            ->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->lecturer();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()
            ->route('lecturer.settings')
            ->with('success', 'Your password has been changed.');
    }

    /**
     * Close the account. Class assignments are removed with it;
     * students and their records are not affected.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $this->lecturer();

        $request->validateWithBag('deleteAccount', [
            'delete_password' => ['required', 'current_password'],
        ], [
            'delete_password.required' => 'Enter your password to delete your account.',
            'delete_password.current_password' => 'That password is incorrect.',
        ]);

        $this->deleteAvatarFile($user->avatar);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deleted.');
    }

    /**
     * Settings are for a lecturer's own account only.
     */
    private function lecturer(): User
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user && $user->role === 'lecturer', 403);

        return $user;
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
