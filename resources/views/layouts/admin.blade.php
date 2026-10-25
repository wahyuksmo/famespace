<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Super Administrator Portal — FamSpace')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/png" href="{{ asset('icons/favicon-32x32.png') }}">
  <script src="{{ asset('vendor/tailwindcss/tailwind.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  @include('dashboard.partials.styles')
  @stack('styles')
</head>
<body class="min-h-screen bg-cream text-ink antialiased selection:bg-accent selection:text-ink flex flex-col font-sans">

  <!-- TOP ADMIN NAVBAR -->
  <header class="bg-ink text-cream border-b-4 border-ink sticky top-0 z-50 px-4 sm:px-6 py-3 shadow-[0_4px_0_#111]">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      
      <!-- BRAND & ROLE BADGE -->
      <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white hover:text-accent transition-colors">
          <div class="w-8 h-8 bg-accent text-ink brutal-border flex items-center justify-center font-black text-sm">
            ⚡
          </div>
          <span class="font-display font-black text-xl tracking-tight">FamSpace</span>
        </a>
        <span class="px-2 py-0.5 bg-coral text-white text-[10px] font-mono font-black uppercase brutal-border">
          SUPER ADMIN
        </span>
      </div>

      <!-- DESKTOP NAV LINKS -->
      <nav class="hidden md:flex items-center gap-1">
        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 text-xs font-mono font-black uppercase transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-ink brutal-border' : 'text-cream/80 hover:text-white hover:bg-white/10' }}">
          📊 Dashboard
        </a>
        <a href="{{ route('admin.payments.index') }}" class="px-3 py-1.5 text-xs font-mono font-black uppercase transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-accent text-ink brutal-border' : 'text-cream/80 hover:text-white hover:bg-white/10' }}">
          💳 Transaksi & QRIS
        </a>
        <a href="{{ route('admin.plans.index') }}" class="px-3 py-1.5 text-xs font-mono font-black uppercase transition-all {{ request()->routeIs('admin.plans.*') ? 'bg-accent text-ink brutal-border' : 'text-cream/80 hover:text-white hover:bg-white/10' }}">
          🏷️ Paket Produk
        </a>
        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 text-xs font-mono font-black uppercase transition-all {{ request()->routeIs('admin.users.*') ? 'bg-accent text-ink brutal-border' : 'text-cream/80 hover:text-white hover:bg-white/10' }}">
          👨‍👩‍👧‍👦 Anggota & Keluarga
        </a>
      </nav>

      <!-- USER PROFILE & LOGOUT -->
      <div class="flex items-center gap-3">
        <span class="text-xs font-mono font-bold text-cream/70 hidden sm:inline">
          wahyuganteng@admin.com
        </span>
        <form action="{{ route('logout') }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="px-3 py-1 bg-coral hover:bg-white hover:text-coral text-white font-mono font-black text-xs uppercase brutal-border transition-colors">
            Logout
          </button>
        </form>
      </div>

    </div>

    <!-- MOBILE NAV BAR -->
    <div class="md:hidden flex items-center justify-around gap-1 pt-2.5 mt-2.5 border-t border-white/20 text-[11px] font-mono font-black">
      <a href="{{ route('admin.dashboard') }}" class="p-1 {{ request()->routeIs('admin.dashboard') ? 'text-accent underline' : 'text-cream/80' }}">Dashboard</a>
      <a href="{{ route('admin.payments.index') }}" class="p-1 {{ request()->routeIs('admin.payments.*') ? 'text-accent underline' : 'text-cream/80' }}">Transaksi</a>
      <a href="{{ route('admin.plans.index') }}" class="p-1 {{ request()->routeIs('admin.plans.*') ? 'text-accent underline' : 'text-cream/80' }}">Paket</a>
      <a href="{{ route('admin.users.index') }}" class="p-1 {{ request()->routeIs('admin.users.*') ? 'text-accent underline' : 'text-cream/80' }}">Anggota</a>
    </div>
  </header>

  <!-- FLASH MESSAGES -->
  <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 pt-4">
    @if(session('success'))
      <div class="p-3 bg-brand text-white brutal-border mb-4 flex items-center justify-between font-bold text-xs">
        <div class="flex items-center gap-2">
          <span>✅</span>
          <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="font-mono text-xs">✕</button>
      </div>
    @endif

    @if(session('error'))
      <div class="p-3 bg-coral text-white brutal-border mb-4 flex items-center justify-between font-bold text-xs">
        <div class="flex items-center gap-2">
          <span>⚠️</span>
          <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="font-mono text-xs">✕</button>
      </div>
    @endif
  </div>

  <!-- MAIN CANVAS -->
  <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
    @yield('content')
  </main>

  <!-- FOOTER -->
  <footer class="border-t-2 border-ink py-4 text-center font-mono text-xs text-ink/60 bg-cream">
    FamSpace Super Administrator Portal &bull; Single-Tenant Ops Control
  </footer>

  @stack('scripts')
</body>
</html>
