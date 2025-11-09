<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->status === 'ban') {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Ваш аккаунт заблокирован. Обратитесь в поддержку.',
                ]);
        }

        return $next($request);
    }
}
