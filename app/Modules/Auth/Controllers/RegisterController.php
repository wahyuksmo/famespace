<?php

namespace App\Modules\Auth\Controllers;

use App\Events\FamilyMemberEvent;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Family\Repositories\FamilyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected FamilyRepository $familyRepo,
        protected UserRepository $userRepo
    ) {}

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('modules.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:100',
            'budget_monthly' => 'nullable|string',
            'currency' => 'nullable|string|max:5',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:m_users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:30',
        ]);

        try {
            $user = $this->authService->registerFamilyAndAdmin($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('dashboard'),
                    'message' => 'Selamat datang di Ruang Keluarga baru Anda!',
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Selamat datang di Ruang Keluarga baru Anda!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }

    public function showJoin(string $code)
    {
        $family = $this->familyRepo->findByInviteCode($code);
        if (! $family) {
            return redirect()->route('login')->withErrors(['email' => 'Kode undangan keluarga tidak valid atau sudah kedaluwarsa.']);
        }

        return view('modules.auth.join', compact('family'));
    }

    public function processJoin(Request $request, string $code)
    {
        $family = $this->familyRepo->findByInviteCode($code);
        if (! $family) {
            abort(404, 'Keluarga tidak ditemukan');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:m_users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:30',
        ]);

        try {
            $user = $this->authService->joinFamily($family->id, $validated);

            broadcast(new FamilyMemberEvent(
                familyId: (string) $family->id,
                action: 'member_joined',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} baru saja bergabung ke ruang keluarga!",
                data: [
                    'member_id' => $user->id,
                    'member_name' => $user->name,
                    'role' => $user->role,
                ]
            ));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('dashboard'),
                    'message' => 'Berhasil bergabung dengan '.$family->name.'!',
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Berhasil bergabung dengan '.$family->name.'!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }

    public function showJoinFamily()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('modules.auth.join_family');
    }

    public function processJoinFamily(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string|max:20',
            'email' => 'required|email|exists:m_users,email',
            'password' => 'required|string',
            'role' => 'required|string|max:30',
        ], [
            'invite_code.required' => 'Kode keluarga wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.exists' => 'Akun dengan email ini tidak ditemukan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'role.required' => 'Peran di keluarga wajib dipilih.',
        ]);

        $family = $this->familyRepo->findByInviteCode(trim($validated['invite_code']));
        if (! $family) {
            return back()->withErrors(['invite_code' => 'Kode keluarga tidak valid atau tidak ditemukan.'])->withInput();
        }

        $user = $this->userRepo->findByEmail($validated['email']);
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Kata sandi salah.'])->withInput();
        }

        if ($user->family_id !== null) {
            return back()->withErrors(['email' => 'Akun Anda saat ini masih terdaftar aktif di keluarga lain. Silakan keluar dari keluarga sebelumnya terlebih dahulu.'])->withInput();
        }

        try {
            $user->update([
                'family_id' => $family->id,
                'role' => $validated['role'],
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            broadcast(new FamilyMemberEvent(
                familyId: (string) $family->id,
                action: 'member_joined',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} baru saja bergabung ke ruang keluarga!",
                data: [
                    'member_id' => $user->id,
                    'member_name' => $user->name,
                    'role' => $user->role,
                ]
            ));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('dashboard'),
                    'message' => 'Berhasil bergabung dengan '.$family->name.'!',
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Berhasil bergabung dengan '.$family->name.'!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }
}
