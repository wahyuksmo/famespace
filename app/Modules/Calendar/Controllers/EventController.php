<?php

namespace App\Modules\Calendar\Controllers;

use App\Events\CalendarEvent;
use App\Http\Controllers\Controller;
use App\Models\Wkcal;
use App\Modules\Calendar\Repositories\EventRepository;
use App\Modules\Calendar\Services\EventService;
use App\Modules\Calendar\Services\WkcalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected EventRepository $eventRepo,
        protected WkcalService $wkcalService
    ) {}

    public function index(Request $request)
    {
        $familyId = Auth::user()->family_id;
        $pageNumber = (int) $request->input('page', 1);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $filter = (string) $request->input('filter', 'all');

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        // Auto-generate m_wkcal for year if empty
        $yearExists = Wkcal::where('calendar_year', $year)->exists();
        if (! $yearExists) {
            $this->wkcalService->generateYear($year, 'Auto Generator');
        }

        $wkcalDays = Wkcal::where('calendar_year', $year)
            ->where('calendar_month', $month)
            ->orderBy('calendar_date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->calendar_date)->format('Y-m-d');
            });

        $paginatedEvents = $this->eventRepo->getPaginatedFamilyEvents($familyId, 5, 'page', $pageNumber);
        $events = $this->eventRepo->getFamilyEvents($familyId);

        $distinctYears = Wkcal::select('calendar_year')
            ->distinct()
            ->orderBy('calendar_year', 'asc')
            ->pluck('calendar_year')
            ->toArray();

        if (! in_array(2026, $distinctYears)) {
            $distinctYears[] = 2026;
            sort($distinctYears);
        }

        $workingDaysCount = $wkcalDays->where('is_working_day', 'Y')->count();
        $holidaysCount = $wkcalDays->where('is_working_day', 'N')->filter(function ($item) {
            return ! in_array($item->day_of_week, [0, 6]);
        })->count();

        return view('modules.calendar.index', [
            'paginatedEvents' => $paginatedEvents,
            'events' => $events,
            'wkcalDays' => $wkcalDays,
            'year' => $year,
            'month' => $month,
            'filter' => $filter,
            'distinctYears' => $distinctYears,
            'workingDaysCount' => $workingDaysCount,
            'holidaysCount' => $holidaysCount,
        ]);
    }

    public function paginate(Request $request, ?int $page = null)
    {
        $familyId = Auth::user()->family_id;

        if ($request->has('draw') || $request->has('load_more')) {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 5);
            $searchParam = $request->input('search');
            $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');

            $dt = $this->eventRepo->getDataTablesEvents($familyId, $start, $length, $search);

            $formatted = $dt['data']->map(function ($ev) {
                return [
                    'id' => $ev->id,
                    'title' => $ev->title,
                    'description' => $ev->description,
                    'location' => $ev->location ?? 'Agenda',
                    'start_time' => $ev->start_time,
                    'formatted_time' => Carbon::parse($ev->start_time)->locale('id')->isoFormat('D MMM &bull; HH:mm'),
                    'update_url' => route('events.update', $ev->id),
                    'destroy_url' => route('events.destroy', $ev->id),
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
        $perPage = (int) $request->input('per_page', 5);
        $events = $this->eventRepo->getPaginatedFamilyEvents($familyId, $perPage, 'page', $pageNumber);

        if ($request->expectsJson() || $request->ajax()) {
            $res = $events->toArray();
            $res['success'] = true;

            return response()->json($res);
        }

        return redirect()->route('events.index', ['page' => $pageNumber]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:150',
        ]);

        $familyId = Auth::user()->family_id;
        $userId = Auth::id();

        try {
            $event = $this->eventService->createEvent($familyId, $userId, $validated);

            broadcast(new CalendarEvent(
                familyId: (string) $familyId,
                action: 'created',
                userId: (int) $userId,
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menambahkan agenda: '.$validated['title'],
                data: ['id' => $event->id]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Agenda bersama berhasil ditambahkan!']);
            }

            return redirect()->route('events.index')->with('success', 'Agenda bersama berhasil ditambahkan!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('events.index')->with('error', $e->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:150',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $event = $this->eventService->updateEvent($id, $familyId, $validated);

            broadcast(new CalendarEvent(
                familyId: (string) $familyId,
                action: 'updated',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' memperbarui agenda: '.$validated['title'],
                data: ['id' => $id]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Agenda berhasil diperbarui!']);
            }

            return redirect()->route('events.index')->with('success', 'Agenda berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('events.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, Request $request)
    {
        $familyId = Auth::user()->family_id;

        try {
            $this->eventService->deleteEvent($id, $familyId);

            broadcast(new CalendarEvent(
                familyId: (string) $familyId,
                action: 'deleted',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menghapus agenda.',
                data: ['id' => $id]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Agenda berhasil dihapus!']);
            }

            return redirect()->route('events.index')->with('success', 'Agenda berhasil dihapus!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('events.index')->with('error', $e->getMessage());
        }
    }
}
