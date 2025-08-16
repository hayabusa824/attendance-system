<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
        // 管理者の場合のリダイレクト先
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        // 一般ユーザーの場合のリダイレクト先
        return route('login');
    }
    }
}
