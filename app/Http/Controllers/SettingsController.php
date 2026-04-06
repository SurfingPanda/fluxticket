<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'username'          => ['nullable', 'string', 'max:100'],
            'employee_id'       => ['nullable', 'string', 'max:100'],
            'job_title'         => ['nullable', 'string', 'max:150'],
            'primary_contact'   => ['nullable', 'string', 'max:30'],
            'secondary_contact' => ['nullable', 'string', 'max:30'],
            'email'             => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'department'        => ['nullable', 'string', 'max:150'],
            'profile_photo'     => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        } else {
            unset($data['profile_photo']);
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
