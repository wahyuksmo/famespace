<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>@yield('title', 'Dashboard Keluarga — ' . ($family->name ?? 'famespace') . ' — famespace')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- PWA & Mobile Web App Meta -->
  <meta name="theme-color" content="#2F5233">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Famespace">

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
  <!-- JQUERY -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- DATATABLES JQUERY -->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
  <!-- FLATPICKR (CUSTOM BRUTALIST DATEPICKER) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
  <!-- CHART.JS FOR INTERACTIVE ANALYTICS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @include('dashboard.partials.styles')
  @stack('styles')
</head>
<body class="h-screen overflow-hidden flex antialiased selection:bg-accent selection:text-ink">

  <!-- MOBILE SIDEBAR BACKDROP -->
  <div id="sidebar-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-ink/60 backdrop-blur-[2px] z-40 hidden lg:hidden transition-opacity duration-300"></div>

  <!-- SIDEBAR KIRI TETAP (CHUNKY NEOBRUTALIST) -->
  @include('dashboard.partials.sidebar')

  <!-- MAIN CONTENT CANVAS -->
  <div id="main-content" class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">

    <!-- MOBILE TOP APP BAR (PWA / MOBILE HEADER) -->
    @include('dashboard.partials.mobile-header')

    <!-- TOP HEADER (HANYA DI LAYAR DESKTOP LG+) -->
    @include('dashboard.partials.header')

    <!-- NOTIFICATIONS FLASH -->
    @include('dashboard.partials.notifications')

    <!-- CONTENT WRAPPER (DENGAN SAFE AREA PWA & SPACE UNTUK BOTTOM NAV) -->
    <main class="p-4 sm:p-6 pt-safe lg:pt-6 pb-28 lg:pb-6 max-w-7xl w-full mx-auto space-y-6">
      @yield('content')
    </main>

  </div>

  <!-- FLOATING ACTION BUTTON (MOBILE QUICK ACTION) -->
  @include('dashboard.partials.fab')

  <!-- BOTTOM NAVIGATION BAR (MOBILE MENU) -->
  @include('dashboard.partials.bottom-nav')

  <!-- PWA INSTALL BANNER PROMPT -->
  @include('dashboard.partials.pwa-banner')

  <!-- GLOBAL PAYWALL & DELETE MODAL -->
  @include('modules.subscription.modal')
  @include('dashboard.partials.delete-modal')
  @include('dashboard.partials.image-modal')
  @stack('modals')

  <!-- JAVASCRIPT LOGIC -->
  @include('dashboard.partials.scripts')
  @stack('scripts')

</body>
</html>
