<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>@yield('title', 'Dashboard Keluarga — ' . ($family->name ?? 'famespace') . ' — famespace')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="user-id" content="{{ auth()->id() }}">
  <meta name="family-id" content="{{ auth()->user()?->family_id ?? '' }}">

  <!-- PWA & Mobile Web App Meta -->
  <meta name="theme-color" content="#2F5233">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Famespace">

  <script src="{{ asset('vendor/tailwindcss/tailwind.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
  <!-- JQUERY -->
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <!-- DATATABLES JQUERY -->
  <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.min.css') }}">
  <script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
  <!-- FLATPICKR (CUSTOM BRUTALIST DATEPICKER) -->
  <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
  <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
  <script src="{{ asset('vendor/flatpickr/id.js') }}"></script>
  <!-- CHART.JS FOR INTERACTIVE ANALYTICS -->
  <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
  @vite(['resources/js/app.js'])
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
  @include('dashboard.partials.profile-modal')
  @stack('modals')

  <!-- JAVASCRIPT LOGIC -->
  @include('dashboard.partials.scripts')
  @stack('scripts')

</body>
</html>
