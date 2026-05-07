<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember-me');

        if (!Auth::attempt($request->only('email', 'password'), $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Credenciales incorrectas.',
                ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // redirección según rol
        $redirect = $this->redirectByRole($user);

        return redirect()->intended($redirect);
    }


    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    private function redirectByRole(User $user): string
    {
        if ($user->hasRole('Administrador')) {
            return route('dashboard');
        }

        if ($user->hasRole('Secretario')) {
            return route('dashboard');
        }

        if ($user->hasRole('Gerente')) {
            return route('dashboard');
        }

        if ($user->hasRole('Asesor')) {
            return route('dashboard');
        }

        return route('dashboard');
    }
}
