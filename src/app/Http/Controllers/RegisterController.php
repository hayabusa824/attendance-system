<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function index() {
        return view('auth.register');
    }

    public function store(RegisterRequest $request) {

    $users = $request->only(['name', 'email', 'password']);
    $users['password'] = Hash::make($users['password']);

    $user = User::create($users);
    $user->sendEmailVerificationNotification(); // 認証メールを送信
    Auth::login($user); // 自動ログイン

    return redirect()->route('verification.notice');
    }

}
