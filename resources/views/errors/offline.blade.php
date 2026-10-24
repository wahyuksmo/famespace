<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Koneksi Terputus — FamSpace</title>
  <meta name="theme-color" content="#2F5233">
  <link rel="icon" type="image/jpeg" href="{{ asset('icons/logo_famspace.jpeg') }}">
  <script src="{{ asset('vendor/tailwindcss/tailwind.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
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
      box-shadow: 3.5px 3.5px 0px 0px #121614;
      transition: all 0.15s ease;
    }
    .brutal-btn:active {
      transform: translate(2px, 2px);
      box-shadow: 1.5px 1.5px 0px 0px #121614;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 selection:bg-[#34D399] selection:text-[#121614]">
  <div class="max-w-md w-full bg-white brutal-border p-6 sm:p-8 text-center shadow-[6px_6px_0px_#121614] rounded-2xl">
    <div class="w-16 h-16 mx-auto brutal-border rounded-2xl overflow-hidden mb-4 shadow-[3px_3px_0px_#121614]">
      <img src="{{ asset('icons/logo_famspace.jpeg') }}" alt="FamSpace Logo" class="w-full h-full object-cover">
    </div>
    <span class="inline-block px-3 py-1 bg-amber-100 brutal-border text-[11px] font-black uppercase font-mono tracking-wider mb-2">
      Mode Offline
    </span>
    <h1 class="text-2xl font-black font-['Space_Grotesk'] text-[#121614] mb-2">
      Koneksi Terputus
    </h1>
    <p class="text-xs sm:text-sm font-semibold text-[#121614]/70 mb-6 leading-relaxed">
      Aplikasi FamSpace membutuhkan koneksi internet untuk sinkronisasi data keluarga. Periksa kembali Wi-Fi atau paket data seluler Anda.
    </p>
    <button onclick="window.location.reload()" class="w-full py-3 bg-[#2F5233] text-white brutal-btn text-xs font-black uppercase tracking-wider">
      🔄 Coba Muat Ulang
    </button>
  </div>
</body>
</html>
