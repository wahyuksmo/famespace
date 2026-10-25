@extends('layouts.admin')

@section('title', 'Direktori Anggota & Keluarga — FamSpace Super Admin')

@section('content')
<div class="space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b-3 border-ink">
    <div>
      <span class="text-xs font-mono font-black uppercase text-brand tracking-wider">Direktori Pengguna</span>
      <h1 class="text-2xl sm:text-3xl font-black font-display text-ink">Manajemen Anggota & Keluarga</h1>
    </div>

    <!-- SEARCH FORM -->
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-2">
      <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama keluarga / user / email..." class="p-2 text-xs font-mono font-bold bg-white brutal-border focus:bg-cream outline-none w-64">
      <button type="submit" class="px-3 py-2 bg-ink text-white brutal-border font-mono font-black text-xs uppercase hover:bg-accent hover:text-ink transition-colors">
        Cari
      </button>
      @if($search)
        <a href="{{ route('admin.users.index') }}" class="p-2 bg-white brutal-border font-mono font-black text-xs hover:bg-coral hover:text-white">✕</a>
      @endif
    </form>
  </div>

  <!-- FAMILIES & USERS TABLE -->
  <div class="bg-white brutal-card overflow-hidden">
    @if($families->isEmpty())
      <div class="py-12 text-center font-mono text-xs text-ink/60">
        Tidak ada keluarga atau pengguna yang cocok.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-cream border-b-2 border-ink text-ink font-black uppercase text-[11px]">
            <tr>
              <th class="p-3">Nama Keluarga & Kode</th>
              <th class="p-3">Status Langganan</th>
              <th class="p-3">Masa Berlaku</th>
              <th class="p-3">Daftar Anggota</th>
              <th class="p-3 text-right">Aksi Override Tier</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-ink/20">
            @foreach($families as $f)
              @php
                $sub = $f->subscription;
                $plan = $sub?->plan;
                $isPro = $plan && $plan->isPro() && $sub->expires_at->isFuture();
              @endphp
              <tr class="hover:bg-cream/40 transition-colors">
                
                <!-- KELUARGA & KODE -->
                <td class="p-3">
                  <div class="font-black text-ink text-sm">{{ $f->name }}</div>
                  <div class="text-[10px] text-ink/60 mt-0.5">
                    Kode Undangan: <span class="font-bold text-brand bg-cream px-1.5 py-0.5 brutal-border">{{ $f->invite_code }}</span>
                  </div>
                  <div class="text-[10px] text-ink/50 mt-0.5">
                    Dibuat: {{ $f->created_at->format('d M Y') }}
                  </div>
                </td>

                <!-- STATUS LANGGANAN -->
                <td class="p-3">
                  @if($isPro)
                    <span class="px-2 py-0.5 bg-brand text-white font-black text-[10px] brutal-border">
                      👑 {{ strtoupper($plan->name) }}
                    </span>
                  @else
                    <span class="px-2 py-0.5 bg-cream text-ink font-black text-[10px] brutal-border">
                      FREE TIER
                    </span>
                  @endif
                </td>

                <!-- MASA BERLAKU -->
                <td class="p-3 text-[11px]">
                  @if($isPro)
                    @if($plan->isLifetime())
                      <span class="font-bold text-coral">👑 Seumur Hidup</span>
                    @else
                      <span class="font-bold text-ink">Sampai {{ $sub->expires_at->format('d/m/Y') }}</span>
                      <span class="text-[9px] text-ink/60 block">({{ (int)now()->diffInDays($sub->expires_at) }} hari lagi)</span>
                    @endif
                  @else
                    <span class="text-ink/60">Tidak terbatas</span>
                  @endif
                </td>

                <!-- DAFTAR ANGGOTA -->
                <td class="p-3">
                  <div class="space-y-1">
                    <span class="text-[10px] font-bold text-ink/70">{{ $f->users_count }} Anggota:</span>
                    <div class="flex flex-wrap gap-1 max-w-xs">
                      @foreach($f->users as $u)
                        <span class="px-1.5 py-0.5 bg-white brutal-border text-[9px] font-bold text-ink" title="{{ $u->email }}">
                          {{ $u->name }} ({{ ucfirst($u->role) }})
                        </span>
                      @endforeach
                    </div>
                  </div>
                </td>

                <!-- AKSI OVERRIDE TIER -->
                <td class="p-3 text-right">
                  <form action="{{ route('admin.families.override_plan', $f->id) }}" method="POST" class="inline-flex items-center gap-1.5" onsubmit="return confirm('Yakin ingin mengubah status tier keluarga ini?')">
                    @csrf
                    <select name="plan_code" class="p-1 text-[10px] font-mono font-bold bg-cream brutal-border outline-none">
                      <option value="free" {{ (!$isPro) ? 'selected' : '' }}>Free Tier</option>
                      @foreach($plans as $p)
                        @if($p->code !== 'free')
                          <option value="{{ $p->code }}" {{ ($plan && $plan->code === $p->code) ? 'selected' : '' }}>
                            {{ $p->name }}
                          </option>
                        @endif
                      @endforeach
                    </select>
                    <button type="submit" class="px-2 py-1 bg-ink text-white font-mono font-black text-[10px] uppercase brutal-border hover:bg-accent hover:text-ink transition-colors">
                      Set
                    </button>
                  </form>
                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="p-4 border-t-2 border-ink bg-cream/30">
        {{ $families->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
