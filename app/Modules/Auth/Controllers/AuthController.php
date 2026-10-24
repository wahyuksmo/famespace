<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('modules.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if ($this->authService->login($credentials['email'], $credentials['password'], $remember)) {
            $user = Auth::user();
            if (! $user->family_id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Akun Anda tidak lagi terdaftar dalam ruang keluarga manapun.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'errors' => ['email' => [$message]],
                    ], 403);
                }

                return back()->withErrors(['email' => $message]);
            }

            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password yang Anda masukkan salah.',
                'errors' => ['email' => ['Email atau password yang Anda masukkan salah.']],
            ], 422);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function demoLogin(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = $this->authService->demoLogin($request->email);
        if ($user) {
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }

            return redirect()->route('dashboard')->with('success', 'Masuk sebagai demo '.$user->name);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun demo tidak ditemukan. Silakan seeder ulang database.',
            ], 404);
        }

        return back()->withErrors(['email' => 'Akun demo tidak ditemukan. Silakan seeder ulang database.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'redirect' => route('login')]);
        }

        return redirect()->route('login');
    }
}
