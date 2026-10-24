@extends('layouts.main')

@section('title', 'Kalender & Agenda — famespace')

@section('content')
<!-- TAB 5: KALENDER & AGENDA REMINDER -->
<div id="view-calendar" class="space-y-6">
  
  <!-- SUB-NAVIGATION: KALENDER & LINIMASA -->
  <div class="flex items-center gap-1.5 p-1 bg-white brutal-border w-full sm:w-fit rounded-lg shadow-[2px_2px_0px_#121614] select-none">
    <a href="{{ route('events.index') }}" 
       class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 px-3 py-1.5 rounded brutal-border font-mono text-xs font-black bg-accent text-ink shadow-[1.5px_1.5px_0px_#121614]">
      <span>📅</span>
      <span>Agenda Kalender</span>
    </a>
    <a href="{{ route('timeline.index') }}" 
       class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 px-3 py-1.5 rounded border border-transparent font-mono text-xs font-bold text-ink/70 hover:text-ink hover:bg-cream transition-colors">
      <span>🗺️</span>
      <span>Linimasa & Kenangan</span>
    </a>
  </div>
  
  <!-- HEADER KALENDER (SATU BARIS BERSIH) -->
  <div class="bg-white brutal-card p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 flex-wrap mb-1">
        <span class="px-2.5 py-0.5 bg-sky text-ink brutal-border font-mono text-[10px] sm:text-xs font-black uppercase">
          PENGINGAT TERPUSAT KELUARGA
        </span>
        <span class="px-2 py-0.5 bg-accent text-ink brutal-border font-mono text-[10px] sm:text-[11px] font-black uppercase">
          ⚡ Master Kalender 2026 Aktif
        </span>
      </div>
      <h2 class="text-xl sm:text-2xl font-black font-display text-ink mt-1">Kalender Bersama & Agenda</h2>
      <p class="text-xs font-medium text-ink/70">Jadwal kegiatan keluarga terintegrasi dengan kalender kerja (m_wkcal) dan hari libur nasional 2026.</p>
    </div>
    <div class="flex items-center gap-2 w-full md:w-auto">
      <button type="button" onclick="openCalendarModal()" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider text-center">
        + Tambah Agenda Baru
      </button>
    </div>
  </div>

  @php
    $viewYear = $year ?? (int) now()->year;
    $viewMonth = $month ?? (int) now()->month;
    $activeFilter = $filter ?? 'all';

    $viewDate = \Carbon\Carbon::createFromDate($viewYear, $viewMonth, 1);
    $startOfMonth = $viewDate->copy()->startOfMonth();
    $endOfMonth = $viewDate->copy()->endOfMonth();
    $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday
    $daysInMonth = $viewDate->daysInMonth;
    $prevMonthDays = $startOfMonth->copy()->subMonth()->daysInMonth;

    $prevDate = $viewDate->copy()->subMonth();
    $nextDate = $viewDate->copy()->addMonth();

    $eventsByDay = ($events ?? collect())->groupBy(function ($ev) {
        return \Carbon\Carbon::parse($ev->start_time)->format('Y-m-d');
    });
    $todayString = now()->format('Y-m-d');

    $monthEventsCount = ($events ?? collect())->filter(function ($ev) use ($viewYear, $viewMonth) {
        $dt = \Carbon\Carbon::parse($ev->start_time);
        return $dt->year === $viewYear && $dt->month === $viewMonth;
    })->count();

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
  @endphp

  <!-- LAYOUT UTAMA: KALENDER (8 COLS) & AGENDA SIDEBAR (4 COLS) TANPA CARD BERLEBIH -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- KALENDER UTAMA (TERPADU: NAVIGASI, FILTER & GRID DALAM SATU CARD) -->
    <div id="calendar-grid-section" class="lg:col-span-8 bg-white brutal-card p-3 sm:p-5 md:p-6 space-y-3 sm:space-y-4">
      
      <!-- CARD HEADER: NAVIGASI BULAN/TAHUN & FILTER -->
      <div class="space-y-2.5 sm:space-y-3 pb-3 sm:pb-4 border-b-2 border-ink">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
          <!-- NAVIGASI BULAN & TAHUN -->
          <form id="calendar-filter-form" method="GET" action="{{ route('events.index') }}" class="flex items-center gap-1 sm:gap-1.5 flex-wrap w-full sm:w-auto">
            <input type="hidden" name="filter" id="filter-input-val" value="{{ $activeFilter }}">

            <div class="flex items-center gap-1">
              <a href="{{ route('events.index', ['year' => $prevDate->year, 'month' => $prevDate->month, 'filter' => $activeFilter]) }}" 
                 class="px-2 py-1.5 sm:px-2.5 bg-cream hover:bg-accent brutal-btn font-mono font-black text-xs" 
                 title="Bulan Sebelumnya">
                &larr;
              </a>

              <!-- SELECT BULAN -->
              <select name="month" id="calendar-select-month" onchange="this.form.submit()" 
                      class="px-2 py-1.5 sm:px-2.5 bg-white brutal-border font-display font-black text-xs focus:outline-none focus:bg-accent/20 cursor-pointer">
                @foreach($monthNames as $mNum => $mLabel)
                  <option value="{{ $mNum }}" {{ $viewMonth === $mNum ? 'selected' : '' }}>
                    {{ $mLabel }}
                  </option>
                @endforeach
              </select>

              <!-- SELECT TAHUN -->
              <select name="year" id="calendar-select-year" onchange="this.form.submit()" 
                      class="px-2 py-1.5 sm:px-2.5 bg-white brutal-border font-mono font-black text-xs focus:outline-none focus:bg-accent/20 cursor-pointer">
                @foreach(($distinctYears ?? [2025, 2026, 2027]) as $y)
                  <option value="{{ $y }}" {{ $viewYear === (int)$y ? 'selected' : '' }}>
                    {{ $y }}
                  </option>
                @endforeach
              </select>

              <a href="{{ route('events.index', ['year' => $nextDate->year, 'month' => $nextDate->month, 'filter' => $activeFilter]) }}" 
                 class="px-2 py-1.5 sm:px-2.5 bg-cream hover:bg-accent brutal-btn font-mono font-black text-xs" 
                 title="Bulan Berikutnya">
                &rarr;
              </a>
            </div>

            <!-- QUICK SHORTCUTS -->
            <div class="flex items-center gap-1">
              <a href="{{ route('events.index', ['year' => now()->year, 'month' => now()->month, 'filter' => $activeFilter]) }}" 
                 class="px-2 py-1.5 bg-white hover:bg-cream brutal-border text-[10px] sm:text-[11px] font-mono font-bold whitespace-nowrap">
                Bulan Ini
              </a>
              <a href="{{ route('events.index', ['year' => 2026, 'month' => 10, 'filter' => $activeFilter]) }}" 
                 class="px-2 py-1.5 {{ $viewYear === 2026 ? 'bg-accent text-ink' : 'bg-white hover:bg-cream text-ink' }} brutal-border text-[10px] sm:text-[11px] font-mono font-black whitespace-nowrap">
                2026 🎯
              </a>
            </div>
          </form>

          <!-- FILTER PILLS (HORIZONTAL SCROLL ON MOBILE, CLEAN & WRAP-FREE) -->
          <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none sm:flex-wrap w-full sm:w-auto -mx-1 px-1">
            <span class="text-[10px] sm:text-[11px] font-mono font-bold text-ink/60 uppercase mr-0.5 flex-shrink-0">Filter:</span>
            <a href="{{ route('events.index', ['year' => $viewYear, 'month' => $viewMonth, 'filter' => 'all']) }}" 
               class="flex-shrink-0 px-2 py-1 text-[10px] sm:text-[11px] font-mono font-bold brutal-border {{ $activeFilter === 'all' ? 'bg-ink text-white' : 'bg-white hover:bg-cream text-ink' }}">
              Semua
            </a>
            <a href="{{ route('events.index', ['year' => $viewYear, 'month' => $viewMonth, 'filter' => 'events']) }}" 
               class="flex-shrink-0 px-2 py-1 text-[10px] sm:text-[11px] font-mono font-bold brutal-border {{ $activeFilter === 'events' ? 'bg-brand text-white' : 'bg-white hover:bg-cream text-ink' }}">
              Agenda Keluarga
            </a>
            <a href="{{ route('events.index', ['year' => $viewYear, 'month' => $viewMonth, 'filter' => 'holidays']) }}" 
               class="flex-shrink-0 px-2 py-1 text-[10px] sm:text-[11px] font-mono font-bold brutal-border {{ $activeFilter === 'holidays' ? 'bg-coral text-white' : 'bg-white hover:bg-cream text-ink' }}">
              Libur Nasional
            </a>
            <a href="{{ route('events.index', ['year' => $viewYear, 'month' => $viewMonth, 'filter' => 'workdays']) }}" 
               class="flex-shrink-0 px-2 py-1 text-[10px] sm:text-[11px] font-mono font-bold brutal-border {{ $activeFilter === 'workdays' ? 'bg-forest text-white' : 'bg-white hover:bg-cream text-ink' }}">
              Hari Kerja
            </a>
          </div>
        </div>

        <!-- STATS & INFO BAR (TERINTEGRASI DI ATAS KALENDER) -->
        <div class="flex flex-wrap items-center justify-between gap-1.5 sm:gap-2 text-xs font-mono pt-1">
          <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
            <span class="font-display font-black text-lg sm:text-xl text-ink">
              {{ $viewDate->locale('id')->isoFormat('MMMM Y') }}
            </span>
            <span class="px-1.5 sm:px-2 py-0.5 bg-forest/20 text-forest brutal-border font-bold text-[9px] sm:text-[10px]">
              💼 {{ $workingDaysCount ?? 0 }} Hari Kerja
            </span>
            <span class="px-1.5 sm:px-2 py-0.5 bg-coral/20 text-coral brutal-border font-bold text-[9px] sm:text-[10px]">
              🏖️ {{ $holidaysCount ?? 0 }} Libur Nasional
            </span>
          </div>
          <div class="text-[10px] sm:text-[11px] text-ink/70">
            <span class="px-2 py-0.5 bg-brand text-white brutal-border font-bold">
              {{ $monthEventsCount }} Agenda
            </span>
          </div>
        </div>
      </div>

      <!-- DAYS HEADER -->
      <div class="grid grid-cols-7 gap-1 text-center font-mono text-[10px] sm:text-xs font-black uppercase py-1.5 sm:py-2 bg-cream brutal-border">
        <span class="text-coral">Min</span>
        <span>Sen</span>
        <span>Sel</span>
        <span>Rab</span>
        <span>Kam</span>
        <span>Jum</span>
        <span class="text-coral">Sab</span>
      </div>

      <!-- CALENDAR DAYS TILES -->
      <div class="grid grid-cols-7 gap-1 text-xs font-bold">
        <!-- Leading days from previous month -->
        @for($i = $startDayOfWeek - 1; $i >= 0; $i--)
          <div class="min-h-[46px] sm:min-h-[5.5rem] bg-cream/20 p-1 sm:p-1.5 brutal-border text-ink/25 flex flex-col justify-start select-none">
            <span class="text-[10px] sm:text-[11px] font-mono font-bold">{{ $prevMonthDays - $i }}</span>
          </div>
        @endfor

        <!-- Current month days -->
        @for($day = 1; $day <= $daysInMonth; $day++)
          @php
            $dateKey = $viewDate->copy()->day($day)->format('Y-m-d');
            $isToday = ($dateKey === $todayString);
            $dayEvents = $eventsByDay->get($dateKey, collect());
            $dayOfWeek = $viewDate->copy()->day($day)->dayOfWeek;
            $isSunday = ($dayOfWeek === 0);
            $isSaturday = ($dayOfWeek === 6);
            $isWeekend = ($isSunday || $isSaturday);

            $wkcal = ($wkcalDays ?? collect())->get($dateKey);
            $isHoliday = $wkcal && $wkcal->is_working_day === 'N' && !$isWeekend;
            $isWorkday = $wkcal && $wkcal->is_working_day === 'Y';
            $holidayName = $isHoliday ? ($wkcal->event_name ?? 'Hari Libur Nasional') : ($isWeekend ? 'Akhir Pekan' : '');

            // Filter logic
            $dimClass = '';
            if ($activeFilter === 'events' && $dayEvents->isEmpty()) {
                $dimClass = 'opacity-30 bg-cream/10';
            } elseif ($activeFilter === 'holidays' && !($isHoliday || $isWeekend)) {
                $dimClass = 'opacity-30 bg-cream/10';
            } elseif ($activeFilter === 'workdays' && !$isWorkday) {
                $dimClass = 'opacity-30 bg-cream/10';
            }
          @endphp
          <div 
            onclick="openDayDetail('{{ $dateKey }}', '{{ addslashes($holidayName) }}', '{{ $isWorkday ? 'Y' : 'N' }}', {{ json_encode($dayEvents->values()) }})" 
            class="min-h-[46px] sm:min-h-[5.5rem] {{ $isToday ? 'bg-accent/30 ring-2 ring-ink shadow-[2px_2px_0px_#121614]' : ($isHoliday ? 'bg-coral/10' : 'bg-white') }} {{ $dimClass }} p-1 sm:p-1.5 brutal-border hover:bg-cream/60 active:scale-95 sm:active:scale-100 cursor-pointer flex flex-col justify-between overflow-hidden group transition-all relative"
            title="Klik untuk detail & agenda pada {{ $dateKey }}"
          >
            <!-- TOP ROW: TANGGAL & STATUS -->
            <div class="flex justify-between items-start gap-0.5 w-full">
              <span class="{{ $isToday ? 'px-1 bg-accent brutal-border text-[9px] sm:text-[10px] font-black' : ($isSunday || $isHoliday ? 'text-coral font-black' : 'text-ink font-bold') }} text-[10px] sm:text-[11px] font-mono leading-none">
                {{ $day }}
              </span>
              
              <!-- DESKTOP STATUS (hidden di mobile) -->
              <div class="hidden sm:flex items-center gap-1">
                @if($isToday)
                  <span class="px-1 bg-coral text-white brutal-border text-[8px] font-mono font-black uppercase">
                    HARI INI
                  </span>
                @elseif($isHoliday)
                  <span class="px-1 bg-coral text-white brutal-border text-[8px] font-mono font-black uppercase truncate max-w-[55px]" title="{{ $wkcal->event_name }}">
                    LIBUR
                  </span>
                @elseif($isWeekend)
                  <span class="text-[8px] font-mono font-bold text-coral/80">
                    LIBUR
                  </span>
                @elseif($isWorkday)
                  <span class="text-[8px] font-mono font-bold text-forest/70">
                    KERJA
                  </span>
                @endif
              </div>

              <!-- MOBILE DOT STATUS (rapi, tidak overflow di mobile) -->
              <div class="flex sm:hidden items-center gap-0.5">
                @if($isToday)
                  <span class="w-1.5 h-1.5 rounded-full bg-coral ring-1 ring-ink" title="Hari Ini"></span>
                @elseif($isHoliday)
                  <span class="w-1.5 h-1.5 rounded-full bg-coral" title="{{ $wkcal->event_name ?? 'Libur' }}"></span>
                @endif
              </div>
            </div>

            <!-- DESKTOP MIDDLE: EVENTS & HOLIDAY INFO (hidden di mobile) -->
            <div class="hidden sm:block space-y-1 my-1 overflow-hidden">
              @if($isHoliday)
                <div class="p-0.5 px-1 bg-coral/20 text-coral brutal-border text-[8px] font-mono font-black truncate" title="{{ $wkcal->event_name }}">
                  🇮🇩 {{ $wkcal->event_name }}
                </div>
              @endif

              @foreach($dayEvents->take(2) as $ev)
                <div 
                  onclick="event.stopPropagation(); openEditEventModal({{ json_encode($ev) }})" 
                  class="p-0.5 px-1 bg-brand text-white brutal-border text-[9px] font-mono font-bold truncate hover:bg-ink transition-colors cursor-pointer" 
                  title="{{ $ev->title }} ({{ \Carbon\Carbon::parse($ev->start_time)->format('H:i') }})"
                >
                  🕒 {{ \Carbon\Carbon::parse($ev->start_time)->format('H:i') }} {{ $ev->title }}
                </div>
              @endforeach

              @if($dayEvents->count() > 2)
                <div class="text-[8px] font-mono text-ink/70 font-black">
                  +{{ $dayEvents->count() - 2 }} agenda
                </div>
              @endif
            </div>

            <!-- MOBILE MIDDLE/BOTTOM: INDIKATOR AGENDA RAPI (TIDAK SEMPIT & TIDAK TEKS BERANTAKAN) -->
            <div class="flex sm:hidden items-center justify-between w-full mt-auto pt-0.5">
              @if($dayEvents->count() > 0)
                <div class="flex items-center gap-0.5">
                  @foreach($dayEvents->take(2) as $ev)
                    <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                  @endforeach
                  @if($dayEvents->count() > 2)
                    <span class="w-1 h-1 rounded-full bg-brand"></span>
                  @endif
                </div>
                <span class="px-1 py-0.2 bg-brand text-white brutal-border font-mono text-[8px] font-black leading-none ml-auto">
                  {{ $dayEvents->count() }}
                </span>
              @else
                <span></span>
              @endif
            </div>

            <!-- BOTTOM: HOVER ACTION (DESKTOP) -->
            <div class="hidden sm:flex pt-0.5 border-t border-ink/10 items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
              <span class="text-[8px] font-mono font-bold text-ink/60">Detail</span>
              <span class="text-[9px]">🔍</span>
            </div>
          </div>
        @endfor

        <!-- Trailing days from next month -->
        @php
          $totalCells = $startDayOfWeek + $daysInMonth;
          $remainingCells = (7 - ($totalCells % 7)) % 7;
        @endphp
        @for($nextDay = 1; $nextDay <= $remainingCells; $nextDay++)
          <div class="min-h-[46px] sm:min-h-[5.5rem] bg-cream/20 p-1 sm:p-1.5 brutal-border text-ink/25 flex flex-col justify-start select-none">
            <span class="text-[10px] sm:text-[11px] font-mono font-bold">{{ $nextDay }}</span>
          </div>
        @endfor
      </div>

    </div>

    <!-- AGENDA LIST SIDEBAR (4 COLS) WITH DATATABLES -->
    <div class="lg:col-span-4 space-y-4">
      <div class="bg-white brutal-card p-4 sm:p-5">
        <div class="flex items-center justify-between pb-3 border-b-2 border-ink mb-4">
          <div>
            <h3 class="font-black font-display text-base text-ink">Pengingat Terjadwal</h3>
            <p class="text-[11px] font-medium text-ink/70">Daftar agenda terorganisir keluarga</p>
          </div>
          <button type="button" onclick="openCalendarModal()" class="px-2 py-1 bg-accent hover:bg-brand hover:text-white brutal-btn text-[10px] font-mono font-black uppercase">
            + Tambah
          </button>
        </div>
        
        @php
          $eventCount = isset($paginatedEvents) ? count($paginatedEvents) : 0;
          $totalEvents = isset($paginatedEvents) && method_exists($paginatedEvents, 'total') ? $paginatedEvents->total() : $eventCount;
        @endphp

        <!-- 1. DESKTOP VIEW (MD+): DATATABLES CARD WRAPPER -->
        <div class="hidden md:block card-datatable">
          <table id="events-datatable" class="w-full">
            <thead>
              <tr>
                <th>Agenda</th>
              </tr>
            </thead>
            <tbody class="space-y-3">
              <!-- Populated by DataTables Server-Side -->
            </tbody>
          </table>
        </div>

        <!-- 2. MOBILE VIEW (< MD): NEOBRUTALIST CARD STACK & LOAD MORE -->
        <div class="block md:hidden space-y-3" id="events-mobile-container">
          <div class="relative">
            <input 
              type="text" 
              id="events-mobile-search" 
              placeholder="Cari agenda kegiatan..." 
              class="w-full pl-9 pr-3 py-2 bg-white brutal-border font-sans text-xs font-bold text-ink placeholder:text-ink/40 focus:outline-none focus:bg-brand-soft shadow-brutal-sm transition-colors"
            >
            <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
          </div>

          <div id="events-mobile-card-list" class="space-y-2.5">
            @if(isset($paginatedEvents) && count($paginatedEvents) > 0)
              @foreach($paginatedEvents as $ev)
                @php
                  $evPayload = json_encode([
                    'id' => $ev->id,
                    'title' => $ev->title,
                    'description' => $ev->description,
                    'location' => $ev->location ?? 'Agenda',
                    'start_time' => $ev->start_time,
                    'formatted_time' => \Carbon\Carbon::parse($ev->start_time)->locale('id')->isoFormat('D MMM &bull; HH:mm'),
                    'update_url' => route('events.update', $ev->id),
                    'destroy_url' => route('events.destroy', $ev->id),
                  ]);
                @endphp
                <div class="p-3 bg-accent/25 brutal-border space-y-2 mobile-card-item transition-all" data-id="{{ $ev->id }}">
                  <div class="flex justify-between items-center text-[11px] font-mono">
                    <span class="text-brand font-black">
                      {{ \Carbon\Carbon::parse($ev->start_time)->locale('id')->isoFormat('D MMM • HH:mm') }}
                    </span>
                    <span class="px-1.5 bg-white brutal-border text-[10px]">{{ $ev->location ?? 'Agenda' }}</span>
                  </div>
                  <div class="text-sm font-black text-ink">{{ $ev->title }}</div>
                  @if($ev->description)
                    <div class="text-[11px] font-normal text-ink/70">{{ $ev->description }}</div>
                  @endif
                  <div class="pt-1.5 border-t border-ink/20 flex items-center justify-end gap-1.5">
                    <button 
                      type="button" 
                      onclick='openEditEventModal({!! $evPayload !!})' 
                      class="px-2 py-0.5 bg-white hover:bg-accent brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm"
                      title="Edit Agenda"
                    >
                      ✏️ Edit
                    </button>
                    <button 
                      type="button" 
                      onclick="confirmDelete('{{ route('events.destroy', $ev->id) }}', 'agenda {{ addslashes($ev->title) }}')" 
                      class="px-2 py-0.5 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm"
                      title="Hapus Agenda"
                    >
                      🗑️
                    </button>
                  </div>
                </div>
              @endforeach
            @else
              <div class="p-4 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
                Belum ada agenda keluarga terjadwal.
              </div>
            @endif
          </div>

          <!-- LOAD MORE CONTROLS -->
          <div id="events-mobile-pagination-box" class="pt-1 text-center space-y-2">
            <div id="events-mobile-counter" class="text-[10px] font-mono font-bold text-ink/70">
              Menampilkan <span id="events-mobile-count">{{ $eventCount }}</span> dari <span id="events-mobile-total">{{ $totalEvents }}</span> agenda
            </div>
            <button 
              type="button" 
              id="events-mobile-load-more" 
              onclick="loadMoreEventsMobile()" 
              class="{{ $eventCount >= $totalEvents ? 'hidden' : '' }} w-full py-2.5 bg-brand text-white hover:bg-brand-dark brutal-btn text-xs font-mono font-black uppercase tracking-wider flex items-center justify-center gap-2 active:scale-[0.99] transition-all"
            >
              <span>⬇ Muat Lebih Banyak</span>
            </button>
            <div id="events-mobile-all-loaded" class="{{ $eventCount < $totalEvents ? 'hidden' : '' }} py-2 bg-cream brutal-border font-mono text-[10px] font-black text-ink/60">
              ✓ Semua agenda telah ditampilkan
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection

@push('modals')
<!-- MODAL DETAIL HARI (POPUP RINCIAN TANGGAL & AGENDA) -->
<div id="day-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-ink/60 hidden">
  <div class="bg-white brutal-card max-w-lg w-full p-4 sm:p-6 space-y-3.5 sm:space-y-4 max-h-[90vh] overflow-y-auto">
    
    <!-- HEADER DETAIL MODAL -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <div>
        <span class="px-2 py-0.5 bg-accent brutal-border font-mono text-[10px] font-black uppercase">
          RINCIAN TANGGAL
        </span>
        <h3 id="day-detail-date-title" class="text-lg sm:text-xl font-black font-display text-ink mt-1"></h3>
      </div>
      <button type="button" onclick="closeDayDetailModal()" class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-mono font-black flex items-center justify-center">
        &times;
      </button>
    </div>

    <!-- STATUS HARI (KERJA / LIBUR) -->
    <div id="day-detail-status-badge" class="p-3 brutal-border font-mono text-xs font-bold space-y-1">
      <!-- Injected via JavaScript -->
    </div>

    <!-- DAFTAR AGENDA PADA HARI INI -->
    <div>
      <div class="flex items-center justify-between mb-2">
        <h4 class="font-black font-display text-sm text-ink uppercase">Agenda Keluarga:</h4>
        <span id="day-detail-events-count" class="text-[11px] font-mono font-bold text-ink/60"></span>
      </div>
      
      <div id="day-detail-events-list" class="space-y-2">
        <!-- Injected via JavaScript -->
      </div>
    </div>

    <!-- FOOTER ACTIONS -->
    <div class="pt-4 border-t-2 border-ink flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2">
      <button type="button" onclick="closeDayDetailModal()" class="w-full sm:w-auto px-4 py-2 bg-cream hover:bg-ink hover:text-white brutal-btn text-xs font-bold font-mono">
        Tutup
      </button>
      <button type="button" id="day-detail-btn-add" class="w-full sm:w-auto px-4 py-2 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider">
        + Tambah Agenda di Tanggal Ini
      </button>
    </div>

  </div>
</div>

<!-- PER-MODULE MODAL TAMBAH/EDIT AGENDA -->
@include('modules.calendar.modal')
@endpush

@push('scripts')
<script>
  let eventsDataTable = null;
  let calendarDatePicker = null;
  let selectedDateForAdd = null;

  $(document).ready(function() {
    // Inisialisasi Flatpickr dengan Jam & Menit
    if (typeof flatpickr !== 'undefined') {
      calendarDatePicker = flatpickr("#calendar-input-time", {
        locale: "id",
        enableTime: true,
        time_24hr: true,
        dateFormat: "Y-m-d H:i:00",
        altInput: true,
        altFormat: "j F Y - H:i"
      });
    }

    // Inisialisasi Server-Side jQuery DataTables untuk Agenda (Card View)
    window.eventsDataTable = eventsDataTable = $('#events-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('events.paginate') }}",
        type: 'GET'
      },
      pageLength: 4,
      lengthMenu: [4, 8, 16],
      language: {
        search: "Cari Agenda:",
        lengthMenu: "Tampil _MENU_",
        info: "_START_-_END_ dari _TOTAL_ agenda",
        infoEmpty: "0 agenda",
        infoFiltered: "(dari _MAX_)",
        emptyTable: "Belum ada agenda keluarga terjadwal.",
        processing: "Memuat...",
        paginate: {
          first: "&laquo;",
          previous: "&larr;",
          next: "&rarr;",
          last: "&raquo;"
        }
      },
      columns: [
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            const payload = JSON.stringify(row).replace(/"/g, '&quot;');
            const descHtml = row.description ? `<div class="text-[11px] font-normal text-ink/70">${escapeHtml(row.description)}</div>` : '';

            return `
              <div class="p-3 bg-accent/25 brutal-border space-y-2">
                <div class="flex justify-between items-center text-[11px] font-mono">
                  <span class="text-brand font-black">
                    ${row.formatted_time}
                  </span>
                  <span class="px-1.5 bg-white brutal-border text-[10px]">${escapeHtml(row.location)}</span>
                </div>
                <div class="text-sm font-black text-ink">${escapeHtml(row.title)}</div>
                ${descHtml}
                <div class="pt-1.5 border-t border-ink/20 flex items-center justify-end gap-1.5">
                  <button 
                    type="button" 
                    onclick='openEditEventModal(${payload})' 
                    class="px-2 py-0.5 bg-white hover:bg-accent brutal-border text-[10px] font-bold transition-colors"
                    title="Edit Agenda"
                  >
                    ✏️ Edit
                  </button>
                  <button 
                    type="button" 
                    onclick="confirmDelete('${row.destroy_url}', 'agenda ${escapeHtml(row.title)}')" 
                    class="px-2 py-0.5 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors"
                    title="Hapus Agenda"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            `;
          }
        }
      ]
    });

    $('#day-detail-btn-add').on('click', function() {
      const targetDate = selectedDateForAdd;
      closeDayDetailModal();
      if (targetDate) { 
        openCalendarModalWithDate(targetDate);
      } else {
        openCalendarModal();
      }
    });
  });

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // MOBILE EVENTS STATE & LOAD MORE
  let eventsMobileStart = {{ $eventCount }};
  const eventsMobileLength = 4;
  let eventsMobileTotal = {{ $totalEvents }};
  let eventsMobileSearch = '';
  let eventsMobileLoading = false;

  function renderMobileEventCard(row) {
    const payload = JSON.stringify(row).replace(/"/g, '&quot;');
    const descHtml = row.description ? `<div class="text-[11px] font-normal text-ink/70">${escapeHtml(row.description)}</div>` : '';

    return `
      <div class="p-3 bg-accent/25 brutal-border space-y-2 mobile-card-item transition-all" data-id="${row.id}">
        <div class="flex justify-between items-center text-[11px] font-mono">
          <span class="text-brand font-black">
            ${row.formatted_time}
          </span>
          <span class="px-1.5 bg-white brutal-border text-[10px]">${escapeHtml(row.location)}</span>
        </div>
        <div class="text-sm font-black text-ink">${escapeHtml(row.title)}</div>
        ${descHtml}
        <div class="pt-1.5 border-t border-ink/20 flex items-center justify-end gap-1.5">
          <button 
            type="button" 
            onclick='openEditEventModal(${payload})' 
            class="px-2 py-0.5 bg-white hover:bg-accent brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm"
            title="Edit Agenda"
          >
            ✏️ Edit
          </button>
          <button 
            type="button" 
            onclick="confirmDelete('${row.destroy_url}', 'agenda ${escapeHtml(row.title)}')" 
            class="px-2 py-0.5 bg-coral text-white hover:bg-ink brutal-border text-[10px] font-bold transition-colors shadow-brutal-sm"
            title="Hapus Agenda"
          >
            🗑️
          </button>
        </div>
      </div>
    `;
  }

  function fetchEventsMobileData(append = false) {
    if (eventsMobileLoading) return;
    eventsMobileLoading = true;

    const $btn = $('#events-mobile-load-more');
    const origBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span>⏳ Memuat...</span>');

    const fetchStart = append ? eventsMobileStart : 0;

    $.ajax({
      url: "{{ route('events.paginate') }}",
      type: 'GET',
      data: {
        draw: 1,
        start: fetchStart,
        length: eventsMobileLength,
        search: eventsMobileSearch
      },
      dataType: 'json',
      success: function(res) {
        const rows = res.data || [];
        eventsMobileTotal = res.recordsFiltered !== undefined ? res.recordsFiltered : (res.total || 0);

        if (!append) {
          $('#events-mobile-card-list').empty();
          eventsMobileStart = 0;
        }

        if (rows.length === 0 && !append) {
          $('#events-mobile-card-list').html(`
            <div class="p-4 text-center bg-cream brutal-border font-mono text-xs font-bold text-ink/60">
              Tidak ada agenda yang sesuai filter.
            </div>
          `);
        } else {
          rows.forEach(function(row) {
            $('#events-mobile-card-list').append(renderMobileEventCard(row));
          });
          eventsMobileStart += rows.length;
        }

        // Update progress counter
        $('#events-mobile-count').text(eventsMobileStart);
        $('#events-mobile-total').text(eventsMobileTotal);

        if (eventsMobileStart >= eventsMobileTotal || rows.length === 0) {
          $('#events-mobile-load-more').addClass('hidden');
          $('#events-mobile-all-loaded').removeClass('hidden');
        } else {
          $('#events-mobile-load-more').removeClass('hidden');
          $('#events-mobile-all-loaded').addClass('hidden');
        }
      },
      error: function(err) {
        console.error('Gagal mengambil data agenda mobile:', err);
      },
      complete: function() {
        eventsMobileLoading = false;
        $btn.prop('disabled', false).html(origBtnHtml);
      }
    });
  }

  function loadMoreEventsMobile() {
    fetchEventsMobileData(true);
  }

  function refreshEventsMobile() {
    eventsMobileStart = 0;
    fetchEventsMobileData(false);
  }

  $(document).ready(function() {
    let eventSearchTimer = null;
    $(document).on('input', '#events-mobile-search', function() {
      clearTimeout(eventSearchTimer);
      const query = $(this).val();
      eventSearchTimer = setTimeout(function() {
        eventsMobileSearch = query;
        refreshEventsMobile();
      }, 350);
    });
  });

  // Buka Popup Rincian Tanggal & Agenda
  function openDayDetail(dateStr, holidayName, isWorkday, dayEvents) {
    selectedDateForAdd = dateStr;
    const dateObj = new Date(dateStr + 'T00:00:00');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateFormatted = dateObj.toLocaleDateString('id-ID', options);

    $('#day-detail-date-title').text(dateFormatted);

    // Status Badge
    let statusHtml = '';
    if (holidayName && holidayName !== 'Akhir Pekan') {
      statusHtml = `
        <div class="text-coral font-black">🏖️ HARI LIBUR NASIONAL</div>
        <div class="text-sm text-ink">${escapeHtml(holidayName)}</div>
      `;
      $('#day-detail-status-badge').removeClass('bg-forest/15 bg-cream/40').addClass('bg-coral/15');
    } else if (holidayName === 'Akhir Pekan') {
      statusHtml = `
        <div class="text-coral font-black">🏖️ LIBUR AKHIR PEKAN</div>
        <div class="text-xs text-ink/80">Waktu santai bersama seluruh keluarga.</div>
      `;
      $('#day-detail-status-badge').removeClass('bg-forest/15 bg-coral/15').addClass('bg-cream/40');
    } else {
      statusHtml = `
        <div class="text-forest font-black">💼 HARI KERJA OPERASIONAL (m_wkcal)</div>
        <div class="text-xs text-ink/80">Jam kerja normal: 08:00 - 17:00 WIB.</div>
      `;
      $('#day-detail-status-badge').removeClass('bg-coral/15 bg-cream/40').addClass('bg-forest/15');
    }
    $('#day-detail-status-badge').html(statusHtml);

    // Daftar Agenda
    const events = Array.isArray(dayEvents) ? dayEvents : [];
    $('#day-detail-events-count').text(events.length + ' agenda');

    if (events.length === 0) {
      $('#day-detail-events-list').html(`
        <div class="p-3 bg-cream/30 brutal-border text-center text-xs text-ink/60 font-mono">
          Belum ada agenda keluarga pada tanggal ini.
        </div>
      `);
    } else {
      let eventsHtml = '';
      events.forEach(function(ev) {
        const timeStr = ev.start_time ? ev.start_time.substring(11, 16) : '-';
        const payload = JSON.stringify(ev).replace(/"/g, '&quot;');
        eventsHtml += `
          <div class="p-3 bg-accent/20 brutal-border flex items-start justify-between gap-3">
            <div class="space-y-1 min-w-0">
              <div class="flex items-center gap-2 text-[11px] font-mono">
                <span class="px-1.5 py-0.5 bg-brand text-white brutal-border font-bold">🕒 ${timeStr}</span>
                <span class="px-1.5 py-0.5 bg-white brutal-border font-bold">${escapeHtml(ev.location || 'Agenda')}</span>
              </div>
              <div class="font-black text-sm text-ink truncate">${escapeHtml(ev.title)}</div>
              ${ev.description ? `<p class="text-xs text-ink/70">${escapeHtml(ev.description)}</p>` : ''}
            </div>
            <button 
              type="button" 
              onclick='closeDayDetailModal(); openEditEventModal(${payload})' 
              class="px-2 py-1 bg-white hover:bg-accent brutal-border text-[10px] font-mono font-bold flex-shrink-0"
            >
              ✏️ Edit
            </button>
          </div>
        `;
      });
      $('#day-detail-events-list').html(eventsHtml);
    }

    $('#day-detail-modal').removeClass('hidden');
  }

  function closeDayDetailModal() {
    $('#day-detail-modal').addClass('hidden');
  }

  function openCalendarModal() {
    currentModalType = 'event';
    $('#calendar-modal-form')[0].reset();
    $('#calendar-modal-method').val('POST');
    $('#calendar-modal-form').attr('action', "{{ route('events.store') }}");
    $('#calendar-modal-title').text('Tambah Agenda Baru');
    $('#calendar-modal-icon').text('📅');
    $('#calendar-btn-submit').html('Simpan Agenda &rarr;');
    if (calendarDatePicker) calendarDatePicker.clear();

    selectComboboxOption('assignee', "{{ Auth::id() }}", "👨 {{ Auth::user()->name }}");
    selectComboboxOption('category', 'Keluarga Inti', '🏠 Keluarga Inti');

    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#calendar-modal').removeClass('hidden');
  }

  function openCalendarModalWithDate(dateStr) {
    openCalendarModal();
    if (calendarDatePicker) {
      calendarDatePicker.setDate(dateStr + " 09:00:00");
    }
    $('#calendar-modal-title').text('Tambah Agenda: ' + dateStr);
  }

  function closeCalendarModal() {
    $('#calendar-modal').addClass('hidden');
    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
  }

  function openEditEventModal(ev) {
    currentModalType = 'event';
    $('#calendar-modal-form')[0].reset();
    $('#calendar-modal-method').val('PUT');
    $('#calendar-modal-form').attr('action', ev.update_url || ('/events/' + ev.id));
    $('#calendar-modal-title').text('Edit Agenda: ' + ev.title);
    $('#calendar-modal-icon').text('✏️');
    $('#calendar-btn-submit').html('Perbarui Agenda &rarr;');

    $('#calendar-input-title').val(ev.title);
    $('#calendar-input-location').val(ev.location || '');
    $('#calendar-input-desc').val(ev.description || '');
    if (ev.start_time && calendarDatePicker) {
      calendarDatePicker.setDate(ev.start_time);
    }

    selectComboboxOption('assignee', "{{ Auth::id() }}", "👨 {{ Auth::user()->name }}");
    selectComboboxOption('category', 'Keluarga Inti', '🏠 Keluarga Inti');

    if (typeof removeUploadedFile === 'function') removeUploadedFile();
    $('#popover-assignee').addClass('hidden');
    $('#popover-category').addClass('hidden');
    $('#calendar-modal').removeClass('hidden');
  }

  // Handle Form Submit via AJAX
  $('#calendar-modal-form').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#calendar-btn-submit');
    const origText = $btn.html();
    const actionUrl = $form.attr('action');

    const formData = new FormData($form[0]);

    $btn.prop('disabled', true).html('Menyimpan...');
    showLoading('Menyimpan agenda...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        closeCalendarModal();
        if (typeof fireConfetti === 'function') fireConfetti();
        if (typeof showToast === 'function') showToast(res.message || 'Agenda berhasil disimpan!');
        if (typeof window.reloadCurrentModuleData === 'function') {
          window.reloadCurrentModuleData();
        } else {
          if (eventsDataTable) {
            eventsDataTable.ajax.reload(null, false);
          }
          if (typeof refreshEventsMobile === 'function') {
            refreshEventsMobile();
          }
          const $calGrid = $('#calendar-grid-section');
          if ($calGrid.length) {
            $.get(window.location.href, function(html) {
              const newGrid = $(html).find('#calendar-grid-section').html();
              if (newGrid) $calGrid.html(newGrid);
            });
          }
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menyimpan agenda.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
          if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
        }
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else {
          alert(msg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(origText);
        hideLoading();
      }
    });
  });

  // Global helper fallback
  window.openModal = function(type) {
    if (type === 'event') openCalendarModal();
    else if (window.openFinanceModal) window.openFinanceModal();
    else if (window.openChoreModal) window.openChoreModal();
    else if (window.openTimelineModal) window.openTimelineModal();
  };

  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_create') === '1') {
      setTimeout(openCalendarModal, 200);
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  });
</script>
@endpush
