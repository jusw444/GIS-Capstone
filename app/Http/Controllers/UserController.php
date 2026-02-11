<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $page = [
            'pageTitle' => 'User Dashboard',
            'pageName'  => 'User Dashboard',
        ];

        return view('user.dashboard', compact('page'));
    }
}
