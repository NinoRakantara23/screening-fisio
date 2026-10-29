<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $login = trim($request->input('login'));

        $credentials = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $request->input('password')]
            : ['phone' => User::normalizePhone($login), 'password' => $request->input('password')];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $redirectRoute = match ($user->role) {
                'admin'   => 'dashboard.index',
                'fisio'   => 'tests.index',
                'pelatih' => 'mental.index',
                'pemain'  => 'player.dashboard',
                default   => 'dashboard.index',
            };

            return redirect()->intended(route($redirectRoute));
        }

        return back()->withErrors(['login' => 'Email/No. HP atau password salah.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}