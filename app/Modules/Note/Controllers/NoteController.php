<?php

namespace App\Modules\Note\Controllers;

use App\Events\NoteEvent;
use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $query = Note::where('family_id', $family->id)
            ->with('user');

        if ($request->filled('q')) {
            $search = '%'.$request->query('q').'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        if ($request->filled('color')) {
            $query->where('color', $request->query('color'));
        }

        if ($request->query('filter') === 'pinned') {
            $query->where('is_pinned', true);
        }

        // Pinned notes stay at the top, followed by newest notes
        $notes = $query->orderByDesc('is_pinned')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'notes' => $notes,
            ]);
        }

        return view('modules.note.index', [
            'family' => $family,
            'user' => $user,
            'notes' => $notes,
            'members' => $family->users,
            'isPro' => $family->isPro(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string|max:5000',
            'color' => 'nullable|string|in:yellow,mint,blue,pink,orange,lavender',
            'is_pinned' => 'nullable|boolean',
        ]);

        $note = Note::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'color' => $validated['color'] ?? 'yellow',
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        NoteEvent::dispatch(
            $family->id,
            'created',
            $user->id,
            $user->name,
            "{$user->name} menambahkan catatan kulkas: \"{$note->title}\"",
            ['note_id' => $note->id]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil ditempel ke papan keluarga!',
                'note' => $note->load('user'),
            ]);
        }

        return redirect()->route('notes.index')->with('success', 'Catatan berhasil ditempel ke papan keluarga!');
    }

    public function update(Request $request, Note $note): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($note->family_id !== $family->id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! $note->canManage($user)) {
            abort(403, 'Hanya pembuat catatan atau Orang Tua (Parent) yang dapat mengubah catatan ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string|max:5000',
            'color' => 'nullable|string|in:yellow,mint,blue,pink,orange,lavender',
            'is_pinned' => 'nullable|boolean',
        ]);

        $note->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'color' => $validated['color'] ?? $note->color,
            'is_pinned' => $request->has('is_pinned') ? $request->boolean('is_pinned') : $note->is_pinned,
        ]);

        NoteEvent::dispatch(
            $family->id,
            'updated',
            $user->id,
            $user->name,
            "{$user->name} memperbarui catatan: \"{$note->title}\"",
            ['note_id' => $note->id]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui!',
                'note' => $note->load('user'),
            ]);
        }

        return redirect()->route('notes.index')->with('success', 'Catatan berhasil diperbarui!');
    }

    public function togglePin(Request $request, Note $note): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($note->family_id !== $family->id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! $note->canManage($user)) {
            abort(403, 'Hanya pembuat catatan atau Orang Tua (Parent) yang dapat mengatur pin.');
        }

        $note->is_pinned = ! $note->is_pinned;
        $note->save();

        $actionText = $note->is_pinned ? 'disematkan tetap di atas' : 'dilepas dari pin atas';

        NoteEvent::dispatch(
            $family->id,
            'pinned_toggled',
            $user->id,
            $user->name,
            "{$user->name} menyematkan catatan: \"{$note->title}\"",
            ['note_id' => $note->id, 'is_pinned' => $note->is_pinned]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Catatan berhasil {$actionText}!",
                'is_pinned' => $note->is_pinned,
            ]);
        }

        return back()->with('success', "Catatan berhasil {$actionText}!");
    }

    public function destroy(Request $request, Note $note): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($note->family_id !== $family->id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! $note->canManage($user)) {
            abort(403, 'Hanya pembuat catatan atau Orang Tua (Parent) yang dapat menghapus catatan ini.');
        }

        $title = $note->title;
        $note->delete();

        NoteEvent::dispatch(
            $family->id,
            'deleted',
            $user->id,
            $user->name,
            "{$user->name} menghapus catatan: \"{$title}\"",
            ['note_id' => $note->id]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil dicopot dari papan kulkas!',
            ]);
        }

        return redirect()->route('notes.index')->with('success', 'Catatan berhasil dicopot dari papan kulkas!');
    }
}
