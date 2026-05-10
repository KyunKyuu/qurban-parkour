<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|in:SUPERADMIN,PIC',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Verify that the selected role matches the user's actual role
            if ($user->role !== $request->role) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'role' => 'Role yang dipilih tidak sesuai dengan akun Anda.',
                ]);
            }

            $request->session()->regenerate();

            // Redirect based on role
            if ($user->role === 'SUPERADMIN') {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'PIC') {
                return redirect()->intended(route('pic.dashboard'));
            }

            // Default redirect
            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
