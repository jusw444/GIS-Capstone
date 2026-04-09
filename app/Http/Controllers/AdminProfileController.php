<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable',
            'password' => 'nullable|confirmed|min:6'
        ]);

        // Update name & email
        $user->name = $request->name;
        $user->email = $request->email;

        // If changing password
        if ($request->filled('password')) {

            if (!$request->filled('current_password')) {
                return back()->withErrors([
                    'current_password' => 'Current password is required'
                ]);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Incorrect current password'
                ]);
            }

            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('profile_success', 'Profile updated successfully!');
    }
}
