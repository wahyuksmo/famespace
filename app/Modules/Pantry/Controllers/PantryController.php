<?php

namespace App\Modules\Pantry\Controllers;

use App\Events\PantryEvent;
use App\Http\Controllers\Controller;
use App\Modules\Pantry\Services\PantryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PantryController extends Controller
{
    public function __construct(
        protected PantryService $pantryService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->has('draw')) {
            return $this->paginate($request);
        }

        $user = Auth::user();
        $family = $user->family;

        $filter = $request->query('filter');
        $category = $request->query('category');
        $search = $request->query('q');
        $sort = $request->query('sort');

        $items = $this->pantryService->getItems(
            familyId: $family->id,
            filter: $filter,
            category: $category,
            search: $search,
            sort: $sort,
            perPage: 12
        );

        $summary = $this->pantryService->getSummary($family->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'items' => $items,
                'summary' => $summary,
            ]);
        }

        return view('modules.pantry.index', [
            'family' => $family,
            'user' => $user,
            'items' => $items,
            'summary' => $summary,
            'members' => $family->users,
            'isPro' => $family->isPro(),
        ]);
    }

    public function paginate(Request $request): JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchParam = $request->input('search');
        $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
        $filter = (string) $request->input('filter', '');
        $category = (string) $request->input('category', '');

        $orderColIdx = $request->input('order.0.column');
        $orderDir = (string) $request->input('order.0.dir', 'asc');
        $orderColumnName = $orderColIdx !== null ? (string) $request->input("columns.{$orderColIdx}.name") : null;

        $dt = $this->pantryService->getDataTablesItems(
            familyId: $family->id,
            start: $start,
            length: $length,
            search: $search,
            filter: $filter,
            category: $category,
            orderColumn: $orderColumnName,
            orderDir: $orderDir
        );

        $summary = $this->pantryService->getSummary($family->id);

        $formatted = $dt['data']->map(function ($item) use ($user) {
            $isLow = $item->isLowStock();
            $isExp = $item->isExpired();
            $isSoon = $item->isExpiringSoon();

            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'quantity' => (float) $item->quantity,
                'formatted_quantity' => rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.'),
                'unit' => $item->unit,
                'min_quantity' => (float) $item->min_quantity,
                'formatted_min_quantity' => rtrim(rtrim(number_format((float) $item->min_quantity, 2, '.', ''), '0'), '.'),
                'expired_at' => $item->expired_at ? $item->expired_at->format('Y-m-d') : null,
                'formatted_expired_at' => $item->expired_at ? $item->expired_at->locale('id')->isoFormat('D MMM Y') : null,
                'days_until_expired' => $item->expired_at ? (int) now()->diffInDays($item->expired_at, false) + 1 : null,
                'is_low_stock' => $isLow,
                'is_expired' => $isExp,
                'is_expiring_soon' => $isSoon,
                'notes' => $item->notes,
                'creator_name' => $item->creator?->name ?? 'Keluarga',
                'can_delete' => $item->canDelete($user),
                'adjust_url' => route('pantry.adjust', $item->id),
                'update_url' => route('pantry.update', $item->id),
                'destroy_url' => route('pantry.destroy', $item->id),
                'raw_item' => $item,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $dt['recordsTotal'],
            'recordsFiltered' => $dt['recordsFiltered'],
            'data' => $formatted,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:dapur,kulkas,obat,kebersihan,lainnya',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'min_quantity' => 'required|numeric|min:0',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item = $this->pantryService->createItem($validated, $user);

        broadcast(new PantryEvent(
            familyId: (string) $user->family_id,
            action: 'item_created',
            userId: (int) $user->id,
            userName: (string) $user->name,
            message: "{$user->name} menambahkan barang baru: {$item->name} ke pantry.",
            data: ['item_id' => $item->id, 'item_name' => $item->name]
        ));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dicatat ke inventaris pantry!',
                'item' => $item,
            ]);
        }

        return redirect()->route('pantry.index')->with('success', 'Barang berhasil dicatat ke inventaris pantry!');
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:dapur,kulkas,obat,kebersihan,lainnya',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'min_quantity' => 'required|numeric|min:0',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item = $this->pantryService->updateItem($id, $validated, $user);

        broadcast(new PantryEvent(
            familyId: (string) $user->family_id,
            action: 'item_updated',
            userId: (int) $user->id,
            userName: (string) $user->name,
            message: "{$user->name} memperbarui data barang: {$item->name}.",
            data: ['item_id' => $item->id, 'item_name' => $item->name]
        ));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data barang pantry berhasil diperbarui!',
                'item' => $item,
            ]);
        }

        return redirect()->route('pantry.index')->with('success', 'Data barang pantry berhasil diperbarui!');
    }

    public function adjustStock(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'delta' => 'required|numeric',
        ]);

        $item = $this->pantryService->adjustStock($id, (float) $validated['delta'], $user);

        broadcast(new PantryEvent(
            familyId: (string) $user->family_id,
            action: 'stock_adjusted',
            userId: (int) $user->id,
            userName: (string) $user->name,
            message: "{$user->name} mengubah stok {$item->name} menjadi {$item->quantity} {$item->unit}.",
            data: ['item_id' => $item->id, 'item_name' => $item->name, 'new_quantity' => $item->quantity]
        ));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stok barang berhasil disesuaikan!',
                'item' => $item,
            ]);
        }

        return redirect()->route('pantry.index')->with('success', 'Stok barang berhasil disesuaikan!');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $this->pantryService->deleteItem($id, $user);

        broadcast(new PantryEvent(
            familyId: (string) $user->family_id,
            action: 'item_deleted',
            userId: (int) $user->id,
            userName: (string) $user->name,
            message: "{$user->name} menghapus barang dari inventaris pantry.",
            data: ['item_id' => $id]
        ));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Barang pantry berhasil dihapus dari inventaris!',
            ]);
        }

        return redirect()->route('pantry.index')->with('success', 'Barang pantry berhasil dihapus dari inventaris!');
    }
}
