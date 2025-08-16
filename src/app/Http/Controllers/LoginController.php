<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    public function index() {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            // 管理者ならログアウトさせる
            if ($user->is_admin) {
                Auth::guard('web')->logout();
                return redirect()->back()->withErrors([
                    'email' => '一般ユーザーとしてログインできません。',
                ])->withInput();
            }

            // 一般ユーザーとしてログイン成功
            return redirect('/attendance');
        }

        // ログイン失敗
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->withInput();
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate(); // セッションを無効化
        $request->session()->regenerateToken(); // CSRF トークンを再生成

        return redirect('/login');
    }

}
