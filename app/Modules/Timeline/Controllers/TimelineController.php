<?php

namespace App\Modules\Timeline\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timeline\Repositories\TimelineRepository;
use App\Modules\Timeline\Services\TimelineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class TimelineController extends Controller
{
    public function __construct(
        protected TimelineService $timelineService,
        protected TimelineRepository $timelineRepo
    ) {}

    public function index(Request $request)
    {
        $familyId = Auth::user()->family_id;
        $pageNumber = (int) $request->input('page', 1);
        $moments = $this->timelineRepo->getPaginatedFamilyMoments($familyId, 4, 'page', $pageNumber);
        $mapMoments = $this->timelineRepo->getMomentsWithCoordinates($familyId);

        return view('modules.timeline.index', [
            'moments' => $moments,
            'mapMoments' => $mapMoments,
        ]);
    }

    public function mapPoints()
    {
        $familyId = Auth::user()->family_id;
        $moments = $this->timelineRepo->getMomentsWithCoordinates($familyId);

        $points = $moments->map(function ($m) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'story' => $m->story,
                'location_name' => $m->location_name,
                'latitude' => (float) $m->latitude,
                'longitude' => (float) $m->longitude,
                'formatted_date' => Carbon::parse($m->event_date)->locale('id')->isoFormat('D MMMM Y'),
                'user_name' => $m->user ? $m->user->name : 'Keluarga',
                'image_url' => $m->image_path ? (str_starts_with($m->image_path, 'http') ? $m->image_path : asset('storage/'.$m->image_path)) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $points,
        ]);
    }

    public function paginate(Request $request, ?int $page = null)
    {
        $familyId = Auth::user()->family_id;

        if ($request->has('draw') || $request->has('load_more')) {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 4);
            $searchParam = $request->input('search');
            $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');

            $dt = $this->timelineRepo->getDataTablesMoments($familyId, $start, $length, $search);

            $formatted = $dt['data']->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'story' => $m->story,
                    'location_name' => $m->location_name,
                    'latitude' => $m->latitude,
                    'longitude' => $m->longitude,
                    'event_date' => $m->event_date,
                    'formatted_date' => Carbon::parse($m->event_date)->locale('id')->isoFormat('D MMMM Y'),
                    'user_id' => $m->user_id,
                    'user_name' => $m->user ? $m->user->name : 'Keluarga',
                    'image_url' => $m->image_path ? (str_starts_with($m->image_path, 'http') ? $m->image_path : asset('storage/'.$m->image_path)) : null,
                    'update_url' => route('timeline.update', $m->id),
                    'destroy_url' => route('timeline.destroy', $m->id),
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $dt['recordsTotal'],
                'recordsFiltered' => $dt['recordsFiltered'],
                'data' => $formatted,
                'current_page' => (int) floor($start / max($length, 1)) + 1,
                'total' => $dt['recordsTotal'],
            ]);
        }

        $pageNumber = (int) ($page ?? $request->route('page') ?? $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 4);
        $moments = $this->timelineRepo->getPaginatedFamilyMoments($familyId, $perPage, 'page', $pageNumber);

        if ($request->expectsJson() || $request->ajax()) {
            $res = $moments->toArray();
            $res['success'] = true;

            return response()->json($res);
        }

        return redirect()->route('timeline.index', ['page' => $pageNumber]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'story' => 'nullable|string',
            'location_name' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'event_date' => 'required|date',
            'image' => 'nullable|image|max:5120',
        ]);

        $familyId = Auth::user()->family_id;
        $userId = Auth::id();

        try {
            $this->timelineService->createMoment($familyId, $userId, $validated, $request->file('image'));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Momen kenangan berhasil diabadikan!']);
            }

            return redirect()->route('timeline.index')->with('success', 'Momen kenangan berhasil diabadikan!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('timeline.index')->with('error', $e->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'story' => 'nullable|string',
            'location_name' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'event_date' => 'required|date',
            'image' => 'nullable|image|max:5120',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $this->timelineService->updateMoment($id, $familyId, $validated, $request->file('image'));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Momen kenangan berhasil diperbarui!']);
            }

            return redirect()->route('timeline.index')->with('success', 'Momen kenangan berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('timeline.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, Request $request)
    {
        $familyId = Auth::user()->family_id;

        try {
            $this->timelineService->deleteMoment($id, $familyId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Momen kenangan berhasil dihapus!']);
            }

            return redirect()->route('timeline.index')->with('success', 'Momen kenangan berhasil dihapus!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('timeline.index')->with('error', $e->getMessage());
        }
    }
}
