<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gabung Ruang Keluarga — famespace</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
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
    .brutal-border { border: 2.5px solid #121614; }
    .brutal-btn {
      border: 2.5px solid #121614;
      box-shadow: 4px 4px 0px 0px #121614;
      transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .brutal-btn:hover {
      transform: translate(-1.5px, -1.5px);
      box-shadow: 6px 6px 0px 0px #121614;
    }
    .brutal-card {
      border: 2.5px solid #121614;
      box-shadow: 6px 6px 0px 0px #121614;
    }
    .brutal-input {
      border: 2.5px solid #121614;
      box-shadow: 2px 2px 0px 0px #121614;
      outline: none;
    }
    .brutal-input:focus {
      background-color: #FFFFFF;
      box-shadow: 4px 4px 0px 0px #2F5233;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 antialiased selection:bg-accent selection:text-ink">

  <header class="max-w-md mx-auto w-full flex items-center justify-between py-4">
    <a href="{{ route('landing') }}" class="flex items-center gap-2">
      <div class="w-9 h-9 bg-brand text-white brutal-border flex items-center justify-center font-black text-xl font-display">f</div>
      <span class="font-display font-black text-xl tracking-tight text-ink">famespace<span class="text-brand">.</span></span>
    </a>
    <a href="{{ route('login') }}" class="text-xs font-bold text-ink hover:text-brand">&larr; Masuk</a>
  </header>

  <main class="max-w-md mx-auto w-full my-auto py-6">
    <div class="bg-white brutal-card p-6 sm:p-8">
      <div class="text-center mb-6">
        <span class="inline-block px-3 py-1 bg-accent brutal-border text-[11px] font-black uppercase font-mono tracking-wider mb-2">
          Undangan Keluarga
        </span>
        <h1 class="text-2xl font-black font-display text-ink">Gabung ke Ruang</h1>
        <div class="mt-2 p-3 bg-brand-soft brutal-border inline-block">
          <span class="text-xs font-mono font-bold text-brand-dark">Kode: {{ $family->invite_code }}</span>
          <div class="text-sm font-black font-display text-ink mt-0.5">{{ $family->name }}</div>
        </div>
      </div>

      @if ($errors->any())
        <div class="mb-4 p-3 bg-coral/20 brutal-border text-xs font-bold text-coral space-y-1">
          @foreach ($errors->all() as $err)
            <div>• {{ $err }}</div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('join.process', $family->invite_code) }}" method="POST" class="space-y-4">
        @csrf

        <div>
          <label for="name" class="block text-xs font-black font-mono uppercase text-ink mb-1">Nama Lengkap</label>
          <input type="text" id="name" name="name" required placeholder="misal: Maya Indriani" class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink">
        </div>

        <div>
          <label for="role" class="block text-xs font-black font-mono uppercase text-ink mb-1">Peran di Keluarga Ini</label>
          <select id="role" name="role" class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink">
            <option value="Ibu">Ibu / Istri</option>
            <option value="Anak" selected>Anak</option>
            <option value="Kakek/Nenek">Kakek / Nenek</option>
            <option value="ART">Asisten Rumah Tangga</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div>
          <label for="email" class="block text-xs font-black font-mono uppercase text-ink mb-1">Email</label>
          <input type="email" id="email" name="email" required placeholder="nama@keluarga.id" class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink">
        </div>

        <div>
          <label for="password" class="block text-xs font-black font-mono uppercase text-ink mb-1">Kata Sandi</label>
          <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2.5 bg-cream brutal-input text-sm font-bold text-ink">
        </div>

        <button type="submit" class="w-full py-3.5 bg-brand text-white font-black text-sm uppercase tracking-wider brutal-btn mt-2">
          Gabung Sekarang &rarr;
        </button>
      </form>
    </div>
  </main>

  <footer class="max-w-md mx-auto w-full text-center py-4 text-xs font-mono font-bold text-ink/60">
    famespace • Warm Green Neobrutalism
  </footer>
</body>
</html>
