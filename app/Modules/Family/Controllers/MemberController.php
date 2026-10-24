<?php

namespace App\Modules\Family\Controllers;

use App\Events\FamilyMemberEvent;
use App\Http\Controllers\Controller;
use App\Models\Chore;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $family = $user->family;

        if (! $family) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda tidak lagi terdaftar dalam ruang keluarga ini.']);
        }

        $members = $family->users()->orderBy('points', 'desc')->get();

        return view('modules.family.index', compact('user', 'family', 'members'));
    }

    public function toggleFinanceAccess(Request $request, int $id): JsonResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->isAyah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Ayah yang berhak mengatur izin akses keuangan keluarga.',
            ], 403);
        }

        $targetUser = User::where('id', $id)
            ->where('family_id', $currentUser->family_id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota keluarga tidak ditemukan.',
            ], 404);
        }

        if ($targetUser->isAyah()) {
            return response()->json([
                'success' => false,
                'message' => 'Izin akun Ayah selalu aktif penuh dan tidak dapat diubah.',
            ], 422);
        }

        $newValue = $request->has('can_view_all_finances')
            ? $request->boolean('can_view_all_finances')
            : ! $targetUser->can_view_all_finances;

        $targetUser->update([
            'can_view_all_finances' => $newValue,
        ]);

        broadcast(new FamilyMemberEvent(
            familyId: (string) $currentUser->family_id,
            action: 'permission_changed',
            userId: (int) $currentUser->id,
            userName: (string) $currentUser->name,
            message: "Izin akses keuangan {$targetUser->name} telah diperbarui oleh {$currentUser->name}.",
            data: [
                'target_user_id' => $targetUser->id,
                'can_view_all_finances' => $targetUser->can_view_all_finances,
            ]
        ))->toOthers();

        return response()->json([
            'success' => true,
            'can_view_all_finances' => $targetUser->can_view_all_finances,
            'message' => $targetUser->can_view_all_finances
                ? "Akses seluruh data keuangan diberikan kepada {$targetUser->name}."
                : "Akses data keuangan {$targetUser->name} dibatasi hanya untuk transaksi miliknya sendiri.",
        ]);
    }

    public function kickMember(Request $request, int $id): JsonResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->isAyah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Ayah (Admin Keluarga) yang berhak mengeluarkan anggota keluarga.',
            ], 403);
        }

        if ($currentUser->id === $id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengeluarkan akun Anda sendiri dari keluarga.',
            ], 422);
        }

        $targetUser = User::where('id', $id)
            ->where('family_id', $currentUser->family_id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota keluarga tidak ditemukan.',
            ], 404);
        }

        if ($targetUser->isAyah()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengeluarkan akun Ayah (Admin Utama).',
            ], 422);
        }

        $targetName = $targetUser->name;
        $familyId = $currentUser->family_id;

        // Lepas penugasan tugas yang sedang aktif/tertunda untuk anggota ini
        Chore::where('assigned_to_user_id', $targetUser->id)
            ->where('family_id', $familyId)
            ->update(['assigned_to_user_id' => null]);

        // Putuskan hubungan user dengan keluarga
        $targetUser->update([
            'family_id' => null,
            'can_view_all_finances' => false,
        ]);

        // Invalidate active sessions user yang dikeluarkan agar langsung logout
        DB::table('sessions')->where('user_id', $targetUser->id)->delete();

        // Broadcast event ke semua anggota keluarga
        broadcast(new FamilyMemberEvent(
            familyId: (string) $familyId,
            action: 'member_kicked',
            userId: (int) $currentUser->id,
            userName: (string) $currentUser->name,
            message: "{$targetName} telah dikeluarkan dari ruang keluarga oleh {$currentUser->name}.",
            data: [
                'kicked_user_id' => $targetUser->id,
                'kicked_user_name' => $targetName,
            ]
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "{$targetName} berhasil dikeluarkan dari ruang keluarga.",
            'kicked_user_id' => $targetUser->id,
        ]);
    }

    public function regenerateInviteCode(Request $request): JsonResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->isAyah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Ayah yang berhak memperbarui kode undangan keluarga.',
            ], 403);
        }

        $family = $currentUser->family;
        if (! $family) {
            return response()->json([
                'success' => false,
                'message' => 'Data keluarga tidak ditemukan.',
            ], 404);
        }

        do {
            $newCode = 'FAM-'.strtoupper(Str::random(4));
        } while (Family::where('invite_code', $newCode)->exists());

        $family->update([
            'invite_code' => $newCode,
        ]);

        return response()->json([
            'success' => true,
            'invite_code' => $newCode,
            'invite_url' => url('/join/'.$newCode),
            'message' => 'Kode undangan baru berhasil dibuat. Kode lama sudah tidak berlaku.',
        ]);
    }
}
