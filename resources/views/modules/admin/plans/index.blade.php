@extends('layouts.admin')

@section('title', 'Manajemen Paket Produk — FamSpace Super Admin')

@section('content')
<div class="space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b-3 border-ink">
    <div>
      <span class="text-xs font-mono font-black uppercase text-brand tracking-wider">Katalog Langganan</span>
      <h1 class="text-2xl sm:text-3xl font-black font-display text-ink">Manajemen Paket & Produk</h1>
    </div>
  </div>

  <!-- PLANS GRID -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
      <div class="bg-white brutal-card p-5 space-y-4 flex flex-col justify-between">
        
        <div class="space-y-3">
          <div class="flex items-center justify-between pb-2 border-b-2 border-ink">
            <span class="px-2 py-0.5 font-mono text-[10px] font-black uppercase brutal-border {{ $plan->code === 'free' ? 'bg-cream text-ink' : ($plan->code === 'lifetime' ? 'bg-coral text-white' : 'bg-brand text-white') }}">
              {{ strtoupper($plan->code) }}
            </span>
            <span class="px-2 py-0.5 text-[9px] font-mono font-bold brutal-border {{ $plan->is_active ? 'bg-accent text-ink' : 'bg-ink/20 text-ink/60' }}">
              {{ $plan->is_active ? 'AKTIF' : 'NON-AKTIF' }}
            </span>
          </div>

          <h3 class="font-display font-black text-xl text-ink">{{ $plan->name }}</h3>

          <div class="p-3 bg-cream brutal-border">
            <div class="text-[10px] font-mono uppercase text-ink/70">Harga Saat Ini:</div>
            <div class="text-2xl font-black font-mono text-ink">
              Rp {{ number_format($plan->price, 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-mono text-ink/60 mt-0.5">
              Durasi: {{ $plan->duration_days }} Hari &bull; Pelanggan: {{ $plan->subscriptions_count }} Keluarga
            </div>
          </div>

          @if(!empty($plan->features))
            <div class="space-y-1">
              <span class="text-[10px] font-mono font-black uppercase text-ink/70">Fitur:</span>
              <ul class="text-xs font-medium text-ink space-y-0.5">
                @foreach((array)$plan->features as $k => $f)
                  <li class="flex items-center gap-1.5">
                    <span class="text-brand">✔</span>
                    <span>{{ is_string($f) ? $f : "$k: $f" }}</span>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>

        <!-- EDIT FORM -->
        <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="pt-3 border-t-2 border-ink space-y-3">
          @csrf
          @method('PUT')
          
          <div>
            <label class="block text-[10px] font-black font-mono uppercase text-ink mb-1">Nama Paket:</label>
            <input type="text" name="name" value="{{ $plan->name }}" required class="w-full text-xs font-bold p-2 bg-cream brutal-border focus:bg-white outline-none">
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[10px] font-black font-mono uppercase text-ink mb-1">Harga (Rp):</label>
              <input type="number" name="price" value="{{ (int)$plan->price }}" required class="w-full text-xs font-mono font-bold p-2 bg-cream brutal-border focus:bg-white outline-none">
            </div>
            <div>
              <label class="block text-[10px] font-black font-mono uppercase text-ink mb-1">Durasi (Hari):</label>
              <input type="number" name="duration_days" value="{{ $plan->duration_days }}" required class="w-full text-xs font-mono font-bold p-2 bg-cream brutal-border focus:bg-white outline-none">
            </div>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input type="checkbox" name="is_active" id="active_{{ $plan->id }}" value="1" {{ $plan->is_active ? 'checked' : '' }} class="w-4 h-4 accent-brand brutal-border">
            <label for="active_{{ $plan->id }}" class="text-xs font-bold text-ink cursor-pointer">
              Paket Aktif & Ditampilkan
            </label>
          </div>

          <button type="submit" class="w-full py-2 bg-ink hover:bg-brand text-white font-mono font-black text-xs uppercase brutal-btn transition-colors">
            Simpan Perubahan
          </button>
        </form>

      </div>
    @endforeach
  </div>

</div>
@endsection
