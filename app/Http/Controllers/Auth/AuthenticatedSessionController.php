<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{

     public function store(Request $request)
    {
          $credentials = $request->only('email', 'password');
  $user = \App\Models\User::where('email', $request->email)->first();
    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if ($user->status === 'ban') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Ваш аккаунт заблокирован. Обратитесь в поддержку.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Неверные данные для входа.',
    ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        return back()->withErrors([
            'email' => 'Неверные учетные данные.',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    protected function authenticated($request, $user)
{
    $redirect = $request->get('redirect', '/');

    if (! str_starts_with($redirect, config('app.url'))) {
        $redirect = '/';
    }

    return redirect()->intended($redirect);
}


}
