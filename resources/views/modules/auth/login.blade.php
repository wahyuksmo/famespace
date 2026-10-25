<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Masuk — FamKoji</title>

  <!-- PWA & Mobile Web App Meta -->
  <meta name="theme-color" content="#2F5233">
  <link rel="icon" type="image/png" href="{{ asset('icons/favicon-32x32.png') }}">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="FamKoji">

  <script src="{{ asset('vendor/tailwindcss/tailwind.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              DEFAULT: '#2F5233',
              dark: '#1E3821',
              light: '#3F6B44',
              soft: '#EAF0EB',
              accent: '#34D399'
            },
            cream: '#FBF8EF',
            ink: '#121614',
            accent: '#34D399',
            mustard: '#34D399',
            coral: '#E07A5F',
            sage: '#87A987',
            sky: '#81B0C4'
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            display: ['"Space Grotesk"', 'sans-serif'],
            mono: ['"JetBrains Mono"', 'monospace']
          },
          boxShadow: {
            brutal: '4px 4px 0px 0px #121614',
            'brutal-lg': '6px 6px 0px 0px #121614',
            'brutal-sm': '2px 2px 0px 0px #121614'
          }
        }
      }
    }
  </script>
  <style>
    body {
      background-color: #FBF8EF;
      color: #121614;
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-image: radial-gradient(#121614 0.75px, transparent 0.75px);
      background-size: 20px 20px;
    }
    .brutal-border {
      border: 2.5px solid #121614;
    }
    .brutal-btn {
      border: 2.5px solid #121614;
      box-shadow: 4px 4px 0px 0px #121614;
      transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .brutal-btn:hover {
      transform: translate(-1.5px, -1.5px);
      box-shadow: 6px 6px 0px 0px #121614;
    }
    .brutal-btn:active {
      transform: translate(2px, 2px);
      box-shadow: 2px 2px 0px 0px #121614;
    }
    .brutal-card {
      border: 2.5px solid #121614;
      box-shadow: 6px 6px 0px 0px #121614;
      animation: popIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .brutal-input {
      border: 2.5px solid #121614;
      box-shadow: 2px 2px 0px 0px #121614;
      outline: none;
      transition: all 0.15s ease;
    }
    .brutal-input:focus {
      background-color: #FFFFFF;
      box-shadow: 4px 4px 0px 0px #2F5233;
      transform: translate(-1px, -1px);
    }
    @keyframes popIn {
      0% { opacity: 0; transform: scale(0.96) translateY(12px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    @keyframes float-badge {
      0%, 100% { transform: translateY(0) rotate(6deg); }
      50% { transform: translateY(-4px) rotate(4deg); }
    }
    .animate-float-badge {
      animation: float-badge 3s ease-in-out infinite;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 antialiased selection:bg-accent selection:text-ink">

  <!-- TOP HEADER -->
  <header class="max-w-xl mx-auto w-full flex items-center justify-between py-4">
    <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
      <div class="w-10 h-10 bg-brand-soft brutal-border rounded-xl p-1 flex items-center justify-center shadow-[2px_2px_0px_#121614] group-hover:rotate-6 transition-transform shrink-0">
        <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji Logo" class="w-full h-full object-contain">
      </div>
      <span class="font-display font-black text-xl tracking-tight text-ink">FamKoji<span class="text-brand">.</span></span>
    </a>
    <a href="{{ route('landing') }}" class="text-xs font-bold text-ink/80 hover:text-brand flex items-center gap-1">
      &larr; Kembali ke Beranda
    </a>
  </header>

  <!-- LOGIN CARD -->
  <main class="max-w-md mx-auto w-full my-auto py-6">
    <div class="bg-white brutal-card p-6 sm:p-8 relative">
      
      <!-- CARD HEADER -->
      <div class="text-center mb-6">
        <span class="inline-block px-3 py-1 bg-accent brutal-border text-[11px] font-black uppercase font-mono tracking-wider mb-2">
          Keluarga Masuk Sini
        </span>
        <h1 class="text-2xl sm:text-3xl font-black font-display text-ink">Selamat Datang Kembali!</h1>
        <p class="text-xs sm:text-sm font-semibold text-ink/70 mt-1">
          Buka catatan kas, agenda, dan tugas rumah tangga Anda.
        </p>
      </div>

      <!-- ERROR ALERTS -->
      @if ($errors->any())
        <div class="mb-4 p-3 bg-coral/20 brutal-border text-xs font-bold text-coral space-y-1">
          @foreach ($errors->all() as $err)
            <div>• {!! $err !!}</div>
          @endforeach
        </div>
      @endif

      <!-- FORM -->
      <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
          <label for="email" class="block text-xs font-black uppercase font-mono text-ink mb-1">
            Email Terdaftar
          </label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email') }}" 
            required 
            placeholder="nama@keluarga.id"
            class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
          >
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label for="password" class="block text-xs font-black uppercase font-mono text-ink">
              Kata Sandi
            </label>
            <a href="#" class="text-[11px] font-bold text-brand hover:underline">Lupa Sandi?</a>
          </div>
          <input 
            type="password" 
            id="password" 
            name="password" 
            required 
            placeholder="•••••••••"
            class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
          >
        </div>

        <div class="flex items-center gap-2 pt-1">
          <input type="checkbox" id="remember" name="remember" class="w-4 h-4 brutal-border accent-brand cursor-pointer" checked>
          <label for="remember" class="text-xs font-bold text-ink cursor-pointer select-none">
            Ingat saya di perangkat keluarga ini
          </label>
        </div>

        <button type="submit" class="w-full py-3.5 bg-brand text-white font-black text-sm uppercase tracking-wider brutal-btn mt-2">
          Masuk ke Dashboard &rarr;
        </button>
      </form>

      <!-- SWITCH TO REGISTER & JOIN -->
      <div class="mt-6 pt-5 border-t-2 border-ink text-center space-y-3">
        <div>
          <p class="text-xs font-bold text-ink/80">
            Sudah punya akun & mau masuk keluarga baru?
          </p>
          <a href="{{ route('join.family') }}" class="inline-block mt-1.5 px-4 py-2 bg-brand-soft hover:bg-accent brutal-btn text-xs font-black uppercase tracking-wide">
            🔑 Masuk Keluarga dengan Kode
          </a>
        </div>
        <div class="pt-2 border-t border-ink/20">
          <p class="text-xs font-bold text-ink/80">
            Belum punya ruang keluarga?
          </p>
          <a href="{{ route('register') }}" class="inline-block mt-1.5 px-4 py-2 bg-cream hover:bg-accent brutal-btn text-xs font-black uppercase tracking-wide">
            + Buat Ruang Keluarga Baru
          </a>
        </div>
      </div>

    </div>
  </main>

  <!-- FOOTER NOTICE -->
  <footer class="max-w-xl mx-auto w-full text-center py-4 text-xs font-mono font-bold text-ink/60">
    &copy; {{ date('Y') }} FamKoji. Seluruh hak cipta dilindungi.
  </footer>
</body>
</html>
