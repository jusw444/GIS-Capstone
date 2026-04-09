<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    /**
     * Show reset form
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->input('email') ?? '',
        ]);
    }

    /**
     * Handle reset WITHOUT auto login
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // ✅ Update password only
                $user->password = Hash::make($password);
                $user->save();
                // ❌ NO LOGIN HERE
            }
        );

        if ($status == Password::PASSWORD_RESET) {

            // 🔥 LOGOUT & CLEAR SESSION
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', 'Password reset successful! Please login.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}