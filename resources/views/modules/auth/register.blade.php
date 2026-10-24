<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Daftar & Buat Ruang Keluarga — FamKoji</title>

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
    .step-active {
      background-color: #34D399;
      color: #121614;
      box-shadow: 3px 3px 0px #121614;
      transform: scale(1.05);
      transition: all 0.2s ease;
    }
    .step-completed {
      background-color: #2F5233;
      color: #FFFFFF;
      transition: all 0.2s ease;
    }
    @keyframes popIn {
      0% { opacity: 0; transform: scale(0.96) translateY(10px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .step-fade-in {
      animation: popIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 antialiased selection:bg-accent selection:text-ink">

  <!-- TOP HEADER -->
  <header class="max-w-2xl mx-auto w-full flex items-center justify-between py-4">
    <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
      <div class="w-10 h-10 bg-brand-soft brutal-border rounded-xl p-1 flex items-center justify-center shadow-[2px_2px_0px_#121614] group-hover:rotate-6 transition-transform shrink-0">
        <img src="{{ asset('icons/new_logo.png') }}" alt="FamKoji Logo" class="w-full h-full object-contain">
      </div>
      <span class="font-display font-black text-xl tracking-tight text-ink">FamKoji<span class="text-brand">.</span></span>
    </a>
    <div class="text-xs font-bold text-ink/80 flex items-center gap-1">
      Sudah punya akun? <a href="{{ route('login') }}" class="text-brand font-black underline hover:text-ink ml-1">Masuk Sini &rarr;</a>
    </div>
  </header>

  <!-- MAIN WIZARD CONTAINER -->
  <main class="max-w-2xl mx-auto w-full my-auto py-6">
    <div class="bg-white brutal-card p-6 sm:p-8">

      <!-- TOP TOGGLE: BUAT KELUARGA BARU vs GABUNG DENGAN KODE -->
      <div class="grid grid-cols-2 gap-2 p-1.5 bg-cream brutal-border mb-8">
        <button 
          type="button"
          id="tab-btn-create" 
          onclick="switchFlow('create')" 
          class="py-2.5 text-xs sm:text-sm font-black font-display text-center transition-all bg-brand text-white brutal-border"
        >
          🏡 Buat Ruang Keluarga Baru
        </button>
        <button 
          type="button"
          id="tab-btn-join" 
          onclick="switchFlow('join')" 
          class="py-2.5 text-xs sm:text-sm font-bold font-display text-center transition-all bg-white text-ink hover:bg-accent"
        >
          🤝 Punya Kode Undangan
        </button>
      </div>

      <!-- ERROR ALERTS -->
      @if ($errors->any())
        <div class="mb-6 p-3 bg-coral/20 brutal-border text-xs font-bold text-coral space-y-1">
          @foreach ($errors->all() as $err)
            <div>• {{ $err }}</div>
          @endforeach
        </div>
      @endif

      <!-- ============================================== -->
      <!-- FLOW A: BUAT RUANG KELUARGA BARU (3 STEPS) -->
      <!-- ============================================== -->
      <form id="flow-create" action="{{ route('register') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- STEP INDICATOR -->
        <div class="flex items-center justify-between pb-4 border-b-2 border-ink">
          <div class="flex items-center gap-2">
            <div id="badge-step-1" class="w-7 h-7 brutal-border step-active flex items-center justify-center font-mono font-black text-xs">
              1
            </div>
            <span class="text-xs font-bold font-mono uppercase hidden sm:inline">Ruang Keluarga</span>
          </div>

          <div class="h-0.5 flex-1 mx-3 bg-ink/20"></div>

          <div class="flex items-center gap-2">
            <div id="badge-step-2" class="w-7 h-7 brutal-border bg-white text-ink flex items-center justify-center font-mono font-bold text-xs">
              2
            </div>
            <span class="text-xs font-bold font-mono uppercase text-ink/60 hidden sm:inline">Profil Admin</span>
          </div>

          <div class="h-0.5 flex-1 mx-3 bg-ink/20"></div>

          <div class="flex items-center gap-2">
            <div id="badge-step-3" class="w-7 h-7 brutal-border bg-white text-ink flex items-center justify-center font-mono font-bold text-xs">
              3
            </div>
            <span class="text-xs font-bold font-mono uppercase text-ink/60 hidden sm:inline">Siap Meluncur</span>
          </div>
        </div>

        <!-- ================= STEP 1: NAMA KELUARGA & TARGET ================= -->
        <div id="step-content-1" class="space-y-5">
          <div>
            <span class="text-[11px] font-mono font-bold text-brand uppercase tracking-wider">Langkah 1 dari 3</span>
            <h2 class="text-2xl font-black font-display text-ink mt-0.5">Beri Nama Ruang Keluarga Anda</h2>
            <p class="text-xs font-medium text-ink/70">Ruang bersama ini akan dikelola dan dihuni oleh seluruh anggota keluarga.</p>
          </div>

          <div class="space-y-4">
            <div>
              <label for="family_name" class="block text-xs font-black font-mono uppercase text-ink mb-1">Nama Ruang Keluarga</label>
              <input 
                type="text" 
                id="family_name" 
                name="family_name"
                value="{{ old('family_name') }}" 
                required
                placeholder="misal: Keluarga Santoso / The Smiths"
                class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
              >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="budget_monthly" class="block text-xs font-black font-mono uppercase text-ink mb-1">Target Kas Bulanan (Rp)</label>
                <input 
                  type="text" 
                  id="budget_monthly" 
                  name="budget_monthly"
                  value="{{ old('budget_monthly') }}" 
                  placeholder="Rp 10.000.000"
                  class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-mono font-bold text-ink"
                >
                <input type="hidden" name="currency" value="IDR">
              </div>
              
            </div>

            <!-- FAMILY RULE BADGE -->
            <div class="p-3 bg-brand-soft brutal-border space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-sm">🛡️</span>
                <span class="text-xs font-black text-brand-dark font-display uppercase tracking-wider">Prinsip Ruang Bersama</span>
              </div>
              <p class="text-[11px] text-ink/80 leading-relaxed">
                Setiap anggota keluarga dapat melihat catatan kas bersama, tugas harian, dan berkontribusi mengumpulkan poin tanpa rasa terbebani.
              </p>
            </div>
          </div>

          <div class="pt-4 flex justify-end">
            <button 
              type="button" 
              onclick="goToStep(2)" 
              class="px-6 py-3 bg-brand text-white font-black text-xs uppercase tracking-wider brutal-btn flex items-center gap-2"
            >
              Lanjut ke Profil Admin &rarr;
            </button>
          </div>
        </div>

        <!-- ================= STEP 2: PROFIL & PERAN ADMIN ================= -->
        <div id="step-content-2" class="space-y-5 hidden">
          <div>
            <span class="text-[11px] font-mono font-bold text-brand uppercase tracking-wider">Langkah 2 dari 3</span>
            <h2 class="text-2xl font-black font-display text-ink mt-0.5">Siapa nama Anda dan peran di rumah?</h2>
            <p class="text-xs font-medium text-ink/70">Akun ini akan menjadi Admin Ruang Keluarga pertama.</p>
          </div>

          <!-- ROLE SELECTOR -->
          <div>
            <label class="block text-xs font-black font-mono uppercase text-ink mb-2">
              Pilih Peran Anda di Rumah:
            </label>
            <div class="grid grid-cols-3 gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="role" value="Ayah" class="peer hidden" checked>
                <div class="p-3 text-center bg-white brutal-border peer-checked:bg-accent peer-checked:shadow-brutal-sm font-bold text-xs">
                  <div class="text-2xl mb-1">👨</div>
                  <span>Ayah / Kepala</span>
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="role" value="Ibu" class="peer hidden">
                <div class="p-3 text-center bg-white brutal-border peer-checked:bg-accent peer-checked:shadow-brutal-sm font-bold text-xs">
                  <div class="text-2xl mb-1">👩</div>
                  <span>Ibu / Manajer</span>
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="role" value="Anak" class="peer hidden">
                <div class="p-3 text-center bg-white brutal-border peer-checked:bg-accent peer-checked:shadow-brutal-sm font-bold text-xs">
                  <div class="text-2xl mb-1">👦</div>
                  <span>Anak / Lainnya</span>
                </div>
              </label>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label for="name" class="block text-xs font-black font-mono uppercase text-ink mb-1">Nama Lengkap</label>
              <input 
                type="text" 
                id="name" 
                name="name"
                value="{{ old('name') }}" 
                required
                placeholder="misal: Dimas Pratama"
                class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
              >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="reg_email" class="block text-xs font-black font-mono uppercase text-ink mb-1">Email Aktif</label>
                <input 
                  type="email" 
                  id="reg_email" 
                  name="email"
                  value="{{ old('email') }}" 
                  required
                  placeholder="nama@keluarga.id"
                  class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
                >
              </div>
              <div>
                <label for="reg_password" class="block text-xs font-black font-mono uppercase text-ink mb-1">Kata Sandi</label>
                <input 
                  type="password" 
                  id="reg_password" 
                  name="password"
                  value="" 
                  required
                  placeholder="Minimal 6 karakter"
                  class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink"
                >
              </div>
            </div>
          </div>

          <div class="pt-4 flex items-center justify-between">
            <button 
              type="button" 
              onclick="goToStep(1)" 
              class="px-4 py-2.5 bg-cream hover:bg-accent font-bold text-xs uppercase brutal-btn"
            >
              &larr; Kembali
            </button>
            <button 
              type="button" 
              onclick="goToStep(3)" 
              class="px-6 py-3 bg-brand text-white font-black text-xs uppercase tracking-wider brutal-btn flex items-center gap-2"
            >
              Langkah Terakhir &rarr;
            </button>
          </div>
        </div>

        <!-- ================= STEP 3: UNDANG & FINALISASI ================= -->
        <div id="step-content-3" class="space-y-5 hidden">
          <div>
            <span class="text-[11px] font-mono font-bold text-brand uppercase tracking-wider">Langkah 3 dari 3 — Terakhir!</span>
            <h2 class="text-2xl font-black font-display text-ink mt-0.5">Ruang Keluarga Anda Siap Terbit</h2>
            <p class="text-xs font-medium text-ink/70">Setelah klik tombol di bawah, kode 6-digit unik keluarga Anda akan otomatis dibuat.</p>
          </div>

          <!-- SUMMARY CARD -->
          <div class="p-4 bg-accent/25 brutal-border space-y-3">
            <div class="text-xs font-mono font-black uppercase text-ink">Ringkasan Pendaftaran:</div>
            <div class="grid grid-cols-2 gap-2 text-xs font-bold text-ink">
              <div>Nama Ruang: <span id="summary-family-name" class="font-mono text-brand block mt-0.5 font-black">-</span></div>
              <div>Admin Pertama: <span id="summary-user-name" class="font-mono text-brand block mt-0.5 font-black">-</span></div>
            </div>
            <div class="text-[11px] text-ink/80 pt-2 border-t border-ink/20">
              💡 Kode undangan dan link ajak anggota keluarga langsung tersedia di dashboard setelah akun terbuat.
            </div>
          </div>

          <div class="pt-4 flex items-center justify-between">
            <button 
              type="button" 
              onclick="goToStep(2)" 
              class="px-4 py-2.5 bg-cream hover:bg-accent font-bold text-xs uppercase brutal-btn"
            >
              &larr; Kembali
            </button>
            <button 
              type="submit" 
              class="px-6 py-3.5 bg-brand hover:bg-accent hover:text-ink text-white font-black text-xs sm:text-sm uppercase tracking-wider brutal-btn flex items-center gap-2 transition-all"
            >
              🚀 Selesaikan & Buka Ruang Keluarga &rarr;
            </button>
          </div>
        </div>

      </form>

      <!-- ============================================== -->
      <!-- FLOW B: GABUNG DENGAN KODE UNDANGAN -->
      <!-- ============================================== -->
      <div id="flow-join" class="space-y-6 hidden">
        <div>
          <span class="text-[11px] font-mono font-bold text-brand uppercase tracking-wider">Gabung Anggota</span>
          <h2 class="text-2xl font-black font-display text-ink mt-0.5">Masukkan Kode Ruang Keluarga</h2>
          <p class="text-xs font-medium text-ink/70">Minta 6 karakter kode undangan dari Ayah atau Ibu pengelola ruang.</p>
        </div>

        <div class="space-y-4">
          <div>
            <label for="invite_code_input" class="block text-xs font-black font-mono uppercase text-ink mb-1">Kode Undangan (FAM-XXXX)</label>
            <input 
              type="text" 
              id="invite_code_input" 
              placeholder="FAM-XXXX"
              value=""
              class="w-full px-3 py-3 bg-cream brutal-input text-base font-mono font-black uppercase text-brand tracking-wider"
            >
          </div>

          <button 
            type="button" 
            onclick="redirectToJoin()" 
            class="w-full py-3.5 bg-brand text-white font-black text-sm uppercase tracking-wider brutal-btn text-center block"
          >
            Lanjut Pendaftaran Anggota &rarr;
          </button>
        </div>
      </div>

    </div>
  </main>

  <!-- FOOTER NOTICE -->
  <footer class="max-w-2xl mx-auto w-full text-center py-4 text-xs font-mono font-bold text-ink/60">
    &copy; {{ date('Y') }} FamKoji. Seluruh hak cipta dilindungi.
  </footer>

  <script>
    function switchFlow(flow) {
      const btnCreate = document.getElementById('tab-btn-create');
      const btnJoin = document.getElementById('tab-btn-join');
      const flowCreate = document.getElementById('flow-create');
      const flowJoin = document.getElementById('flow-join');

      if (flow === 'create') {
        btnCreate.className = 'py-2.5 text-xs sm:text-sm font-black font-display text-center transition-all bg-brand text-white brutal-border';
        btnJoin.className = 'py-2.5 text-xs sm:text-sm font-bold font-display text-center transition-all bg-white text-ink hover:bg-accent';
        flowCreate.classList.remove('hidden');
        flowJoin.classList.add('hidden');
      } else {
        btnJoin.className = 'py-2.5 text-xs sm:text-sm font-black font-display text-center transition-all bg-brand text-white brutal-border';
        btnCreate.className = 'py-2.5 text-xs sm:text-sm font-bold font-display text-center transition-all bg-white text-ink hover:bg-accent';
        flowJoin.classList.remove('hidden');
        flowCreate.classList.add('hidden');
      }
    }

    function goToStep(step) {
      const s1 = document.getElementById('step-content-1');
      const s2 = document.getElementById('step-content-2');
      const s3 = document.getElementById('step-content-3');

      [s1, s2, s3].forEach(s => s.classList.add('hidden'));

      const b1 = document.getElementById('badge-step-1');
      const b2 = document.getElementById('badge-step-2');
      const b3 = document.getElementById('badge-step-3');

      [b1, b2, b3].forEach(b => {
        b.className = 'w-7 h-7 brutal-border bg-white text-ink flex items-center justify-center font-mono font-bold text-xs';
      });

      if (step === 1) {
        s1.classList.remove('hidden');
        s1.classList.add('step-fade-in');
        b1.className = 'w-7 h-7 brutal-border step-active flex items-center justify-center font-mono font-black text-xs';
      } else if (step === 2) {
        s2.classList.remove('hidden');
        s2.classList.add('step-fade-in');
        b1.className = 'w-7 h-7 brutal-border step-completed flex items-center justify-center font-mono font-black text-xs';
        b2.className = 'w-7 h-7 brutal-border step-active flex items-center justify-center font-mono font-black text-xs';
      } else if (step === 3) {
        s3.classList.remove('hidden');
        s3.classList.add('step-fade-in');
        b1.className = 'w-7 h-7 brutal-border step-completed flex items-center justify-center font-mono font-black text-xs';
        b2.className = 'w-7 h-7 brutal-border step-completed flex items-center justify-center font-mono font-black text-xs';
        b3.className = 'w-7 h-7 brutal-border step-active flex items-center justify-center font-mono font-black text-xs';

        // Update summary
        document.getElementById('summary-family-name').innerText = document.getElementById('family_name').value;
        document.getElementById('summary-user-name').innerText = document.getElementById('name').value;
      }
    }

    function redirectToJoin() {
      const code = document.getElementById('invite_code_input').value.trim();
      if (code) {
        window.location.href = '/join/' + encodeURIComponent(code);
      }
    }
  </script>
</body>
</html>
