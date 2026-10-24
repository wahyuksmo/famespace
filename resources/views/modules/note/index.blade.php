@extends('layouts.main')

@section('title', 'Catatan & Kulkas — ' . ($family->name ?? 'FamSpace'))

@section('content')
<div class="space-y-6">

  <!-- TOP HEADER / BANNER -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-6 bg-white brutal-card">
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="text-2xl sm:text-3xl">📌</span>
        <h1 class="text-xl sm:text-2xl font-black font-display tracking-tight text-ink">Papan Catatan & Kulkas</h1>
      </div>
      <p class="text-xs sm:text-sm font-mono text-ink/70">
        Papan pesan & catatan penting keluarga: info WiFi, aturan rumah, kontak darurat, atau titipan belanja.
      </p>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto">
      <button type="button" 
              onclick="openNoteModal()" 
              class="px-4 py-2 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-[2px_2px_0px_#121614] active:scale-95 transition-all">
        <span>+</span>
        <span>Tempel Catatan</span>
      </button>
    </div>
  </div>

  <!-- SEARCH & FILTER BAR -->
  <div class="p-3 sm:p-4 bg-white brutal-card space-y-3">
    <form action="{{ route('notes.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
      <!-- SEARCH INPUT -->
      <div class="relative flex-1">
        <input type="text" 
               name="q" 
               value="{{ request('q') }}" 
               placeholder="Cari isi catatan atau judul..." 
               class="w-full px-3.5 py-2 pl-9 bg-cream brutal-border font-mono text-xs focus:bg-white focus:outline-none transition-colors">
        <span class="absolute left-3 top-2.5 text-xs text-ink/50">🔍</span>
      </div>

      <!-- COLOR & PIN FILTER PILLS -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Filter Pinned -->
        <a href="{{ route('notes.index', array_merge(request()->query(), ['filter' => request('filter') === 'pinned' ? null : 'pinned'])) }}" 
           class="px-3 py-1.5 brutal-border text-xs font-mono font-bold flex items-center gap-1.5 transition-all {{ request('filter') === 'pinned' ? 'bg-ink text-white shadow-[2px_2px_0px_#FFE500]' : 'bg-cream hover:bg-accent text-ink' }}">
          <span>📌</span>
          <span>Hanya Disematkan</span>
        </a>

        <!-- Filter Color Dropdown / Reset -->
        @if(request('color') || request('q') || request('filter'))
          <a href="{{ route('notes.index') }}" class="px-2.5 py-1.5 bg-coral text-white brutal-border text-xs font-mono font-black hover:bg-coral/80 transition-colors">
            Reset ✕
          </a>
        @endif
      </div>
    </form>

    <!-- PALET FILTER WARNA CEPAT -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-mono">
      <span class="text-[10px] uppercase font-black text-ink/60 mr-1">Warna:</span>
      @php
        $palette = [
          'yellow' => ['bg' => 'bg-[#FFF9A6]', 'label' => 'Kuning'],
          'mint' => ['bg' => 'bg-[#D1FAE5]', 'label' => 'Hijau Mint'],
          'blue' => ['bg' => 'bg-[#BAE6FD]', 'label' => 'Biru Langit'],
          'pink' => ['bg' => 'bg-[#FCE7F3]', 'label' => 'Pink'],
          'orange' => ['bg' => 'bg-[#FED7AA]', 'label' => 'Oranye'],
          'lavender' => ['bg' => 'bg-[#E9D5FF]', 'label' => 'Lavender'],
        ];
      @endphp
      @foreach($palette as $colKey => $colData)
        <a href="{{ route('notes.index', array_merge(request()->query(), ['color' => request('color') === $colKey ? null : $colKey])) }}"
           class="px-2 py-0.5 brutal-border text-[10px] font-bold flex items-center gap-1 transition-transform {{ request('color') === $colKey ? 'scale-105 ring-2 ring-ink shadow-[2px_2px_0px_#121614]' : 'opacity-85 hover:opacity-100' }} {{ $colData['bg'] }} text-ink">
          <span>●</span>
          <span>{{ $colData['label'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

  <!-- STICKY NOTES BOARD GRID -->
  @if($notes->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      @foreach($notes as $note)
        @php
          $bgClass = match($note->color) {
            'mint' => 'bg-[#D1FAE5]',
            'blue' => 'bg-[#BAE6FD]',
            'pink' => 'bg-[#FCE7F3]',
            'orange' => 'bg-[#FED7AA]',
            'lavender' => 'bg-[#E9D5FF]',
            default => 'bg-[#FFF9A6]',
          };
          $canManage = $note->canManage($user);
        @endphp

        <div class="relative p-5 brutal-border {{ $bgClass }} flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-[6px_6px_0px_#121614] shadow-[4px_4px_0px_#121614] group">
          
          <!-- PIN / TAPE BADGE DI ATAS -->
          <div class="flex items-start justify-between gap-2 mb-3">
            <div class="flex items-center gap-1.5">
              @if($note->is_pinned)
                <span class="px-2 py-0.5 bg-ink text-accent brutal-border text-[10px] font-mono font-black flex items-center gap-1 shadow-[1.5px_1.5px_0px_#FFE500]">
                  <span>📌</span>
                  <span>TETAP DI ATAS</span>
                </span>
              @else
                <span class="text-sm select-none opacity-40 group-hover:opacity-100 transition-opacity">📝</span>
              @endif
            </div>

            <!-- PIN TOGGLE BUTTON -->
            @if($canManage)
              <form action="{{ route('notes.toggle_pin', $note->id) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        title="{{ $note->is_pinned ? 'Lepas pin tetap' : 'Sematkan tetap di atas' }}" 
                        class="p-1 rounded-none hover:bg-black/10 active:scale-90 transition-transform text-sm"
                        onclick="if(window.vibrateDevice) window.vibrateDevice(10);">
                  {{ $note->is_pinned ? '📍' : '📌' }}
                </button>
              </form>
            @endif
          </div>

          <!-- NOTE TITLE & CONTENT -->
          <div class="space-y-2 flex-1">
            <h3 class="font-display font-black text-base text-ink tracking-tight break-words leading-snug">
              {{ $note->title }}
            </h3>
            <div class="font-mono text-xs text-ink/90 whitespace-pre-wrap break-words leading-relaxed">
              {{ $note->content }}
            </div>
          </div>

          <!-- FOOTER: AUTHOR & ACTIONS -->
          <div class="pt-4 mt-4 border-t-2 border-ink/20 flex items-center justify-between gap-2 text-[10px] font-mono">
            <div class="flex items-center gap-1.5 truncate">
              <span class="w-4 h-4 rounded-full bg-ink text-white flex items-center justify-center font-bold text-[8px] shrink-0">
                {{ substr($note->user->name ?? 'F', 0, 1) }}
              </span>
              <span class="font-bold text-ink truncate">{{ $note->user->name ?? 'Anggota' }}</span>
              <span class="text-ink/40">&bull;</span>
              <span class="text-ink/60">{{ $note->created_at->diffForHumans() }}</span>
            </div>

            <!-- ACTIONS (EDIT & DELETE) -->
            @if($canManage)
              <div class="flex items-center gap-1 shrink-0">
                <button type="button" 
                        onclick='openEditNoteModal(@json($note))' 
                        class="p-1 hover:bg-black/10 brutal-border border-transparent hover:border-ink transition-colors"
                        title="Edit Catatan">
                  ✏️
                </button>
                <form action="{{ route('notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Hapus catatan ini dari papan kulkas?');" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" 
                          class="p-1 hover:bg-coral/20 brutal-border border-transparent hover:border-coral transition-colors"
                          title="Hapus Catatan">
                    🗑️
                  </button>
                </form>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div class="pt-4">
      {{ $notes->links() }}
    </div>

  @else
    <!-- EMPTY STATE -->
    <div class="p-8 sm:p-12 bg-white brutal-card text-center space-y-4">
      <div class="w-16 h-16 mx-auto bg-brand-soft brutal-border flex items-center justify-center text-3xl shadow-[3px_3px_0px_#121614]">
        📌
      </div>
      <div class="space-y-1 max-w-md mx-auto">
        <h3 class="font-display font-black text-lg text-ink">Papan Catatan Masih Kosong</h3>
        <p class="font-mono text-xs text-ink/70">
          Belum ada catatan atau memo di papan kulkas keluarga. Tempel info WiFi, catatan belanja, atau pesan manis untuk keluarga!
        </p>
      </div>
      <button type="button" 
              onclick="openNoteModal()" 
              class="px-5 py-2.5 bg-brand text-white hover:bg-accent hover:text-ink brutal-btn font-mono font-black text-xs uppercase tracking-wider inline-flex items-center gap-2 shadow-[3px_3px_0px_#121614]">
        <span>+</span>
        <span>Tempel Catatan Pertama</span>
      </button>
    </div>
  @endif

</div>
@endsection

@push('modals')
<!-- MODAL: NOTE (TAMBAH / EDIT CATATAN KULKAS) -->
<div id="note-modal" class="fixed inset-0 bg-ink/80 backdrop-blur-sm z-[70] flex items-center justify-center p-3 sm:p-4 hidden" onclick="if(event.target === this) closeNoteModal()">
  <div class="bg-white brutal-card max-w-lg w-full max-h-[92vh] overflow-y-auto p-5 sm:p-6 relative modal-box space-y-4 shadow-[6px_6px_0px_#121614]">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
      <div class="flex items-center gap-2">
        <div id="note-modal-icon" class="w-7 h-7 bg-brand text-white brutal-border flex items-center justify-center font-black text-xs">
          📌
        </div>
        <h3 id="note-modal-title" class="font-black font-display text-lg sm:text-xl text-ink">
          Tempel Catatan Kulkas
        </h3>
      </div>
      <button type="button" 
              onclick="closeNoteModal()" 
              class="w-8 h-8 bg-cream hover:bg-coral hover:text-white brutal-border font-black flex items-center justify-center transition-colors text-sm"
              aria-label="Tutup">
        ✕
      </button>
    </div>

    <!-- FORM -->
    <form id="note-form" method="POST" action="{{ route('notes.store') }}" class="space-y-3.5">
      @csrf
      <input type="hidden" name="_method" id="note-method-input" value="POST">

      <!-- JUDUL CATATAN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Judul Catatan: <span class="text-coral">*</span>
        </label>
        <input type="text" 
               id="note-title-input" 
               name="title" 
               required 
               maxlength="150" 
               placeholder="misal: Password WiFi Rumah / Aturan Jam Tidur" 
               class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white">
      </div>

      <!-- ISI CATATAN -->
      <div>
        <label class="block text-xs font-black font-mono uppercase text-ink mb-1">
          Isi Catatan: <span class="text-coral">*</span>
        </label>
        <textarea id="note-content-input" 
                  name="content" 
                  required 
                  rows="4" 
                  maxlength="5000" 
                  placeholder="Tulis pesan lengkap di sini..." 
                  class="w-full px-3 py-2 bg-cream brutal-border text-xs sm:text-sm font-bold text-ink outline-none focus:bg-white resize-y"></textarea>
      </div>

      <!-- WARNA KERTAS STICKY NOTE -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-black font-mono uppercase text-ink">
            Warna Kertas:
          </label>
          <span id="note-color-badge" class="px-2 py-0.5 bg-[#FFF9A6] brutal-border text-[10px] font-mono font-black text-ink shadow-[1px_1px_0px_#121614]">
            Kuning
          </span>
        </div>
        <div class="grid grid-cols-6 gap-2">
          @php
            $colors = [
              ['id' => 'yellow', 'label' => 'Kuning', 'hex' => 'bg-[#FFF9A6]'],
              ['id' => 'mint', 'label' => 'Mint', 'hex' => 'bg-[#D1FAE5]'],
              ['id' => 'blue', 'label' => 'Biru', 'hex' => 'bg-[#BAE6FD]'],
              ['id' => 'pink', 'label' => 'Pink', 'hex' => 'bg-[#FCE7F3]'],
              ['id' => 'orange', 'label' => 'Oranye', 'hex' => 'bg-[#FED7AA]'],
              ['id' => 'lavender', 'label' => 'Lavender', 'hex' => 'bg-[#E9D5FF]'],
            ];
          @endphp
          @foreach($colors as $c)
            <label class="cursor-pointer select-none">
              <input type="radio" 
                     name="color" 
                     value="{{ $c['id'] }}" 
                     onchange="selectNoteColor('{{ $c['id'] }}', '{{ $c['label'] }}', '{{ $c['hex'] }}')"
                     class="peer sr-only" 
                     {{ $loop->first ? 'checked' : '' }}>
              <div class="h-10 sm:h-11 brutal-border flex items-center justify-center text-xs font-black {{ $c['hex'] }} text-ink transition-all peer-checked:ring-2 peer-checked:ring-ink peer-checked:scale-105 peer-checked:shadow-[2px_2px_0px_#121614] active:scale-95">
                <span class="opacity-0 peer-checked:opacity-100 transition-opacity">✓</span>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      <!-- SEMATKAN TETAP DI ATAS (PIN) -->
      <label class="p-3 bg-cream brutal-border flex items-center justify-between cursor-pointer hover:bg-white transition-colors">
        <div class="flex items-center gap-2.5">
          <span class="text-base">📌</span>
          <div>
            <div class="text-xs font-black text-ink">Sematkan Tetap di Atas (Pin)</div>
            <div class="text-[10px] font-mono text-ink/60">Selalu tampil di baris teratas papan & beranda</div>
          </div>
        </div>
        <input type="checkbox" 
               id="note-pinned-input" 
               name="is_pinned" 
               value="1" 
               class="w-5 h-5 accent-brand brutal-border cursor-pointer">
      </label>

      <!-- ACTION BUTTONS -->
      <div class="pt-2 flex items-center gap-3">
        <button type="button" 
                onclick="closeNoteModal()" 
                class="w-1/3 py-2.5 bg-white brutal-btn text-xs font-bold uppercase">
          Batal
        </button>
        <button type="submit" 
                id="note-btn-submit" 
                onclick="if(window.vibrateDevice) window.vibrateDevice(12);"
                class="w-2/3 py-2.5 bg-brand text-white brutal-btn text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_#121614]">
          <span id="note-submit-icon">💾</span>
          <span id="note-submit-text">Simpan Catatan &rarr;</span>
        </button>
      </div>

    </form>

  </div>
</div>
@endpush

@push('scripts')
<script>
  const noteColorsMap = {
    yellow: { label: 'Kuning', hex: 'bg-[#FFF9A6]' },
    mint: { label: 'Mint', hex: 'bg-[#D1FAE5]' },
    blue: { label: 'Biru', hex: 'bg-[#BAE6FD]' },
    pink: { label: 'Pink', hex: 'bg-[#FCE7F3]' },
    orange: { label: 'Oranye', hex: 'bg-[#FED7AA]' },
    lavender: { label: 'Lavender', hex: 'bg-[#E9D5FF]' }
  };

  function selectNoteColor(colorKey, label, hexClass) {
    const badge = document.getElementById('note-color-badge');
    if (badge) {
      badge.className = `px-2 py-0.5 ${hexClass} brutal-border text-[10px] font-mono font-black text-ink shadow-[1px_1px_0px_#121614]`;
      badge.textContent = label;
    }
    if (window.vibrateDevice) window.vibrateDevice(8);
  }

  function openNoteModal() {
    if (window.vibrateDevice) window.vibrateDevice(10);
    const form = document.getElementById('note-form');
    form.action = "{{ route('notes.store') }}";
    document.getElementById('note-method-input').value = 'POST';
    document.getElementById('note-modal-title').textContent = 'Tempel Catatan Kulkas';
    document.getElementById('note-submit-text').textContent = 'Simpan Catatan →';
    
    document.getElementById('note-title-input').value = '';
    document.getElementById('note-content-input').value = '';
    document.getElementById('note-pinned-input').checked = false;
    
    const firstRadio = form.querySelector('input[name="color"][value="yellow"]');
    if (firstRadio) {
      firstRadio.checked = true;
      selectNoteColor('yellow', 'Kuning', 'bg-[#FFF9A6]');
    }

    document.getElementById('note-modal').classList.remove('hidden');
    setTimeout(() => {
      document.getElementById('note-title-input').focus();
    }, 100);
  }

  function openEditNoteModal(note) {
    if (window.vibrateDevice) window.vibrateDevice(10);
    const form = document.getElementById('note-form');
    form.action = `/notes/${note.id}`;
    document.getElementById('note-method-input').value = 'PUT';
    document.getElementById('note-modal-title').textContent = 'Edit Catatan Kulkas';
    document.getElementById('note-submit-text').textContent = 'Perbarui Catatan →';

    document.getElementById('note-title-input').value = note.title;
    document.getElementById('note-content-input').value = note.content;
    document.getElementById('note-pinned-input').checked = !!note.is_pinned;

    const colorKey = note.color || 'yellow';
    const colorRadio = form.querySelector(`input[name="color"][value="${colorKey}"]`);
    if (colorRadio) {
      colorRadio.checked = true;
      const c = noteColorsMap[colorKey] || noteColorsMap.yellow;
      selectNoteColor(colorKey, c.label, c.hex);
    }

    document.getElementById('note-modal').classList.remove('hidden');
    setTimeout(() => {
      document.getElementById('note-title-input').focus();
    }, 100);
  }

  function closeNoteModal() {
    if (window.vibrateDevice) window.vibrateDevice(8);
    document.getElementById('note-modal').classList.add('hidden');
  }

  // Reload handler when NoteEvent is received via Reverb
  window.reloadCurrentModuleData = function(event) {
    if (event && event.action) {
      window.location.reload();
    }
  };

  // Close modal on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeNoteModal();
    }
  });

  // Auto-open modal if query parameter ?action=create is present
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
      openNoteModal();
    }
  });
</script>
@endpush
