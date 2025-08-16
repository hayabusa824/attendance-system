<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)->get();

        return view('staff', compact('users'));
    }
}
