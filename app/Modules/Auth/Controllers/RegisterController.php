<?php

namespace App\Modules\Auth\Controllers;

use App\Events\FamilyMemberEvent;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Family\Repositories\FamilyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected FamilyRepository $familyRepo
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
            ))->toOthers();

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
