<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureEmailIsVerifiedWithMessage
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'メール認証を完了してください');
        }

        return $next($request);
    }
}
