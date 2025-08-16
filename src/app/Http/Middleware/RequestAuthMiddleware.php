<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // web または admin が認証されているか確認
        if (Auth::guard('web')->check() || Auth::guard('admin')->check()) {
            return $next($request);
        }

        // 認証されていなければログインページにリダイレクト
        return redirect()->route('login');
    }
}
