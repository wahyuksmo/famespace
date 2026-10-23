<?php

namespace App\Modules\Family\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $family = $user->family;
        $members = $family ? $family->users()->orderBy('points', 'desc')->get() : collect();

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

        return response()->json([
            'success' => true,
            'can_view_all_finances' => $targetUser->can_view_all_finances,
            'message' => $targetUser->can_view_all_finances
                ? "Akses seluruh data keuangan diberikan kepada {$targetUser->name}."
                : "Akses data keuangan {$targetUser->name} dibatasi hanya untuk transaksi miliknya sendiri.",
        ]);
    }
}
