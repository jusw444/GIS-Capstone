<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalAdmins = User::where('role', 'admin')->count();
        $totalUsers = User::where('role', 'user')->count();

        return view('superadmin.dashboard', compact('totalAdmins', 'totalUsers'));
    }

    // Show create admin form
    public function createAdmin()
    {
        return view('superadmin.create');
    }

    // Handle storing new admin
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('superadmin.admins.create')->with('success', 'Admin created successfully!');
    }

    // Show all users/admins
    public function allUsers()
    {
        $users = User::all();
        return view('superadmin.users', compact('users'));
    }
}
