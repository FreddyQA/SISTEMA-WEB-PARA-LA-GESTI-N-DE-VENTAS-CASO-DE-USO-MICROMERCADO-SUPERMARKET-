<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class LoginController extends Controller
{
    use ThrottlesLogins;
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
            'g-recaptcha-response' => 'nullable',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingrese un correo válido.',
            'email.max'         => 'El correo no debe superar los 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max'      => 'La contraseña no debe superar los 255 caracteres.',
        ]);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $seconds = $this->limiter()->availableIn(
                $this->throttleKey($request)
            );
            return back()->withErrors([
                'email' => "Demasiados intentos de inicio de sesión. Por favor, inténtelo de nuevo en {$seconds} segundos.",
            ])->withInput($request->only('email'));
        }

        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            return redirect()->route('dashboard');
        }

        $this->incrementLoginAttempts($request);

        return back()->withErrors([
            'email' => 'Correo o contraseña incorrectos. Verifique sus credenciales.',
        ])->withInput($request->only('email'));
    }

    protected function throttleKey(Request $request)
    {
        return mb_strtolower($request->input('email')) . '|' . $request->ip();
    }

    protected function maxAttempts()
    {
        return 5;
    }

    protected function decayMinutes()
    {
        return 2;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}