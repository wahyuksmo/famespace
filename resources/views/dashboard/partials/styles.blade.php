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
  }
  .brutal-border {
    border: 2.5px solid #121614;
  }
  .custom-scrollbar {
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
  }
  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #121614;
  }
  .brutal-btn {
    border: 2.5px solid #121614;
    box-shadow: 3px 3px 0px 0px #121614;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .brutal-btn:hover {
    transform: translate(-1.5px, -1.5px);
    box-shadow: 5px 5px 0px 0px #121614;
  }
  .brutal-btn:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px 0px #121614;
  }
  .brutal-card {
    border: 2.5px solid #121614;
    box-shadow: 4px 4px 0px 0px #121614;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .brutal-card:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px 0px #121614;
  }
  .nav-item-active {
    background-color: #34D399;
    color: #121614;
    border: 2px solid #121614;
    box-shadow: 3px 3px 0px #121614;
    font-weight: 800;
    transform: translate(-1px, -1px);
  }
  .nav-item {
    font-weight: 700;
    color: #121614;
    border: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .nav-item:hover {
    background-color: #FFFFFF;
    border-color: #121614;
    transform: translateX(2px);
  }

  /* Animation effects */
  @keyframes viewPopIn {
    0% {
      opacity: 0;
      transform: translateY(8px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .tab-view {
    animation: viewPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  }
  @keyframes modalBounce {
    0% {
      transform: scale(0.92) translateY(8px);
      opacity: 0;
    }
    100% {
      transform: scale(1) translateY(0);
      opacity: 1;
    }
  }
  .modal-box {
    animation: modalBounce 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  /* Neo-brutalist Flatpickr Overrides */
  .flatpickr-calendar {
    border: 2.5px solid #121614 !important;
    box-shadow: 5px 5px 0px 0px #121614 !important;
    background: #FBF8EF !important;
    border-radius: 0 !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    width: 300px !important;
    z-index: 99999 !important;
  }
  .flatpickr-calendar.arrowTop:before,
  .flatpickr-calendar.arrowTop:after,
  .flatpickr-calendar.arrowBottom:before,
  .flatpickr-calendar.arrowBottom:after {
    display: none !important;
  }
  .flatpickr-months {
    background: #2F5233 !important;
    color: #FFFFFF !important;
    padding: 6px 4px !important;
    border-bottom: 2.5px solid #121614 !important;
  }
  .flatpickr-month,
  .flatpickr-current-month,
  .flatpickr-current-month input.cur-year,
  .flatpickr-current-month .cur-month {
    color: #FFFFFF !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
  }
  .flatpickr-prev-month svg, .flatpickr-next-month svg {
    fill: #FFFFFF !important;
  }
  .flatpickr-weekdays {
    background: #34D399 !important;
    border-bottom: 2px solid #121614 !important;
  }
  span.flatpickr-weekday {
    color: #121614 !important;
    font-weight: 800 !important;
    font-size: 0.75rem !important;
  }
  .flatpickr-day {
    border-radius: 0 !important;
    font-weight: 700 !important;
    color: #121614 !important;
    height: 34px !important;
    line-height: 34px !important;
    margin: 1px 0 !important;
  }
  .flatpickr-day:hover {
    background: #34D399 !important;
    border-color: #121614 !important;
    color: #121614 !important;
  }
  .flatpickr-day.selected {
    background: #2F5233 !important;
    border-color: #121614 !important;
    color: #FFFFFF !important;
  }
  .flatpickr-day.today {
    border: 2px solid #E07A5F !important;
  }
  .flatpickr-time {
    border-top: 2.5px solid #121614 !important;
    background: #FBF8EF !important;
  }
  .flatpickr-time input {
    font-weight: 800 !important;
    font-family: 'JetBrains Mono', monospace !important;
    color: #121614 !important;
  }

  /* DATATABLES NEOBRUTALISM CUSTOM THEME */
  div.dt-container, div.dataTables_wrapper {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #121614;
    padding-top: 0.25rem;
  }
  div.dt-container .dt-length,
  div.dt-container .dt-search,
  div.dataTables_wrapper .dataTables_length,
  div.dataTables_wrapper .dataTables_filter {
    font-size: 0.75rem;
    font-weight: 800;
    font-family: 'JetBrains Mono', monospace;
    margin-bottom: 0.75rem;
  }
  div.dt-container .dt-search input,
  div.dt-container .dt-length select,
  div.dataTables_wrapper .dataTables_filter input,
  div.dataTables_wrapper .dataTables_length select {
    border: 2px solid #121614 !important;
    background-color: #FBF8EF !important;
    padding: 0.35rem 0.6rem !important;
    font-weight: 700 !important;
    outline: none !important;
    border-radius: 0 !important;
    box-shadow: 2px 2px 0px #121614 !important;
  }
  div.dt-container .dt-search input:focus,
  div.dt-container .dt-length select:focus,
  div.dataTables_wrapper .dataTables_filter input:focus,
  div.dataTables_wrapper .dataTables_length select:focus {
    background-color: #FFFFFF !important;
  }
  div.dt-container .dt-info,
  div.dataTables_wrapper .dataTables_info {
    font-size: 0.75rem;
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    color: rgba(18, 22, 20, 0.7);
    padding-top: 0.75rem;
  }
  div.dt-container .dt-paging,
  div.dataTables_wrapper .dataTables_paginate {
    padding-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-family: 'JetBrains Mono', monospace;
  }
  div.dt-container .dt-paging .dt-paging-button,
  div.dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 2px solid #121614 !important;
    background: #FFFFFF !important;
    color: #121614 !important;
    font-weight: 800 !important;
    font-size: 0.75rem !important;
    padding: 0.25rem 0.6rem !important;
    border-radius: 0 !important;
    box-shadow: 2px 2px 0px #121614 !important;
    cursor: pointer !important;
    margin: 0 !important;
    transition: all 0.1s ease !important;
  }
  div.dt-container .dt-paging .dt-paging-button:hover,
  div.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #34D399 !important;
    color: #121614 !important;
  }
  div.dt-container .dt-paging .dt-paging-button.current,
  div.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #34D399 !important;
    color: #121614 !important;
    font-weight: 900 !important;
  }
  div.dt-container .dt-paging .dt-paging-button.disabled,
  div.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    background: rgba(251, 248, 239, 0.5) !important;
    color: rgba(18, 22, 20, 0.4) !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
  }
  div.dt-container .dt-processing,
  div.dataTables_wrapper .dataTables_processing {
    background: #34D399 !important;
    border: 2px solid #121614 !important;
    box-shadow: 3px 3px 0px #121614 !important;
    color: #121614 !important;
    font-weight: 800 !important;
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 0.75rem !important;
    z-index: 20 !important;
  }
  /* Card datatables wrapper without table-like grid headers */
  .card-datatable {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
  }
  .card-datatable table.dataTable {
    border: none !important;
    background: transparent !important;
    table-layout: fixed !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  .card-datatable table.dataTable thead {
    display: none !important;
  }
  .card-datatable table.dataTable tbody,
  .card-datatable table.dataTable tr,
  .card-datatable table.dataTable td {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
    box-sizing: border-box !important;
  }
  .card-datatable table.dataTable tr {
    margin-bottom: 0.75rem !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  /* NEOBRUTALIST MOBILE CARD FEED & LOAD MORE */
  .mobile-card-item {
    border: 2px solid #121614;
    box-shadow: 3px 3px 0px #121614;
    transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.15s ease;
  }
  .mobile-card-item:active {
    transform: translate(1.5px, 1.5px);
    box-shadow: 1px 1px 0px #121614;
  }
  .btn-load-more {
    border: 2.5px solid #121614;
    box-shadow: 3px 3px 0px 0px #121614;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .btn-load-more:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px 0px #121614;
  }
  .btn-pill-filter {
    border: 2px solid #121614;
    box-shadow: 2px 2px 0px #121614;
    transition: all 0.12s ease;
  }
  .btn-pill-filter.active {
    background-color: #121614 !important;
    color: #FFFFFF !important;
    transform: translate(1px, 1px);
    box-shadow: 1px 1px 0px #121614;
  }
  .btn-pill-filter:active {
    transform: translate(1.5px, 1.5px);
    box-shadow: 0.5px 0.5px 0px #121614;
  }

  @media (max-width: 767px) {
    /* Responsive tweaks for mobile containers */
    .mobile-hide-table {
      display: none !important;
    }
  }

    /* POLISH DATATABLES MOBILE CONTROLS (SEARCH, LENGTH, PAGING) */
    div.dt-container, div.dataTables_wrapper {
      padding: 0 !important;
    }
    div.dt-container .dt-search,
    div.dataTables_wrapper .dataTables_filter {
      float: none !important;
      width: 100% !important;
      text-align: left !important;
      margin-bottom: 0.75rem !important;
    }
    div.dt-container .dt-search label,
    div.dataTables_wrapper .dataTables_filter label {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.35rem !important;
      width: 100% !important;
      font-size: 0.75rem !important;
    }
    div.dt-container .dt-search input,
    div.dataTables_wrapper .dataTables_filter input {
      width: 100% !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.85rem !important;
      border: 2px solid #121614 !important;
      background: #FFFFFF !important;
      box-shadow: 2px 2px 0px #121614 !important;
    }
    div.dt-container .dt-length,
    div.dataTables_wrapper .dataTables_length {
      float: none !important;
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      margin-bottom: 0.75rem !important;
    }
    div.dt-container .dt-length select,
    div.dataTables_wrapper .dataTables_length select {
      background: #FFFFFF !important;
      padding: 0.35rem 0.5rem !important;
    }
    div.dt-container .dt-info,
    div.dataTables_wrapper .dataTables_info {
      float: none !important;
      text-align: center !important;
      padding-top: 0.5rem !important;
      margin-bottom: 0.5rem !important;
      font-size: 0.7rem !important;
    }
    div.dt-container .dt-paging,
    div.dataTables_wrapper .dataTables_paginate {
      float: none !important;
      display: flex !important;
      justify-content: center !important;
      flex-wrap: wrap !important;
      gap: 0.35rem !important;
      padding-top: 0.25rem !important;
    }
    div.dt-container .dt-paging .dt-paging-button,
    div.dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0.4rem 0.75rem !important;
      font-size: 0.75rem !important;
      min-width: 34px !important;
      text-align: center !important;
    }
  }
  
  /* PWA & Mobile Native Polish */
  * {
    -webkit-tap-highlight-color: transparent;
  }
  nav, button, .select-none {
    -webkit-user-select: none;
    user-select: none;
  }
  .pt-safe {
    padding-top: max(env(safe-area-inset-top, 0px), 0.625rem);
  }
  .pb-safe {
    padding-bottom: max(env(safe-area-inset-bottom, 0px), 0.65rem);
  }
  @media all and (display-mode: standalone) {
    #pwa-install-prompt {
      display: none !important;
    }
  }

  /* Micro-animations & Interactive Mobile Feedback */
  @keyframes slideUpMobile {
    0% {
      transform: translateY(100%);
      opacity: 0.6;
    }
    100% {
      transform: translateY(0);
      opacity: 1;
    }
  }

  @keyframes springPop {
    0% { transform: scale(0.85); }
    60% { transform: scale(1.08); }
    100% { transform: scale(1); }
  }

  @keyframes iconWiggle {
    0%, 100% { transform: rotate(0deg) scale(1); }
    20% { transform: rotate(-12deg) scale(1.15); }
    40% { transform: rotate(12deg) scale(1.15); }
    60% { transform: rotate(-8deg) scale(1.08); }
    80% { transform: rotate(6deg) scale(1.04); }
  }

  @keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.18); box-shadow: 0 0 8px rgba(224, 122, 95, 0.6); }
  }

  @keyframes speedDialIn {
    0% {
      opacity: 0;
      transform: translateY(16px) scale(0.9);
    }
    100% {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .animate-slide-up-mobile {
    animation: slideUpMobile 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  .animate-spring-pop {
    animation: springPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }

  .animate-wiggle {
    animation: iconWiggle 0.5s ease-in-out;
  }

  .animate-badge-pulse {
    animation: badgePulse 2s infinite ease-in-out;
  }

  .speed-dial-item {
    animation: speedDialIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  /* Mobile Touch Tap Feedback */
  .touch-press {
    transition: transform 0.12s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.12s ease;
  }
  .touch-press:active {
    transform: scale(0.94);
  }

  /* Mobile Bottom Sheet Modal transformation */
  @media (max-width: 639px) {
    .fixed.inset-0.flex.items-center.justify-center:has(.modal-box) {
      align-items: flex-end !important;
      padding: 0 !important;
    }
    .modal-box {
      border-bottom: 0 !important;
      border-left: 2.5px solid #121614 !important;
      border-right: 2.5px solid #121614 !important;
      border-top: 2.5px solid #121614 !important;
      border-radius: 20px 20px 0 0 !important;
      max-height: 88vh !important;
      width: 100% !important;
      max-width: 100% !important;
      box-shadow: 0 -8px 28px rgba(18, 22, 20, 0.25) !important;
      animation: slideUpMobile 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
      padding-bottom: max(env(safe-area-inset-bottom, 0px), 1.25rem) !important;
    }
    .modal-box::before {
      content: '';
      display: block;
      width: 44px;
      height: 4.5px;
      background: #121614;
      opacity: 0.25;
      border-radius: 9999px;
      margin: -4px auto 14px auto;
    }
  }

  /* Safe area insets for Mobile PWA */
  .pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 0px);
  }
  .pt-safe {
    padding-top: env(safe-area-inset-top, 0px);
  }
</style>
