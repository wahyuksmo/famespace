<!-- JAVASCRIPT LOGIC -->
<script>
  let pendingUnlockTab = null;
  let selectedPayMethod = 'qris';
  const serverIsPro = {{ $isPro ? 'true' : 'false' }};
  let activeAjaxCount = 0;

  // JQUERY AJAX GLOBAL SETUP & LOADING STATE
  function showLoading(text = 'Memproses data...') {
    $('#global-ajax-loader-text').text(text);
    $('#global-ajax-progress').removeClass('hidden');
    $('#global-ajax-loader').removeClass('hidden');
  }

  function hideLoading() {
    if (activeAjaxCount <= 0) {
      $('#global-ajax-progress').addClass('hidden');
      $('#global-ajax-loader').addClass('hidden');
    }
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    beforeSend: function(xhr) {
      if (window.Echo && typeof window.Echo.socketId === 'function' && window.Echo.socketId()) {
        xhr.setRequestHeader('X-Socket-ID', window.Echo.socketId());
      }
    }
  });

  $(document).ajaxSend(function() {
    activeAjaxCount++;
    showLoading();
  });

  $(document).ajaxComplete(function() {
    activeAjaxCount = Math.max(0, activeAjaxCount - 1);
    if (activeAjaxCount === 0) {
      hideLoading();
    }
  });

  function isProUser() {
    return serverIsPro;
  }

  function toggleMobileSidebar() {
    toggleMobileProfileDropdown(false);
    const aside = document.getElementById('sidebar-aside');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (aside) {
      aside.classList.toggle('-translate-x-full');
    }
    if (backdrop) {
      backdrop.classList.toggle('hidden');
    }
  }

  function toggleMobileProfileDropdown(forceState) {
    const dd = document.getElementById('mobile-profile-dropdown');
    const btn = document.getElementById('mobile-profile-btn');
    if (!dd) return;
    const shouldOpen = (typeof forceState === 'boolean') ? forceState : dd.classList.contains('hidden');
    if (shouldOpen) {
      dd.classList.remove('hidden');
      dd.classList.add('flex');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    } else {
      dd.classList.add('hidden');
      dd.classList.remove('flex');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }
  }

  document.addEventListener('click', function(e) {
    const container = document.getElementById('mobile-profile-container');
    if (container && !container.contains(e.target)) {
      toggleMobileProfileDropdown(false);
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      toggleMobileProfileDropdown(false);
    }
  });

  function updateSidebarChoreCount(count) {
    if (count !== undefined && count !== null) {
      const $badge = $('#sidebar-chore-count');
      if ($badge.length) {
        $badge.text(count);
      }
      const $mobileBadge = $('#mobile-bottom-nav-chore-count');
      if ($mobileBadge.length) {
        $mobileBadge.text(count);
        if (parseInt(count, 10) > 0) {
          $mobileBadge.removeClass('hidden');
        } else {
          $mobileBadge.addClass('hidden');
        }
      }
    }
  }

  if (typeof window.toggleTask !== 'function') {
    window.toggleTask = function(checkbox, choreId) {
      const $checkbox = $(checkbox);
      const $parent = $checkbox.closest('div');
      let $text = $parent.find('label > div > div').first();
      if (!$text.length) {
        $text = $parent.find('label > span').first();
      }

      const $choreCount = $('#sidebar-chore-count');
      const prevCount = parseInt($choreCount.text(), 10) || 0;

      if (checkbox.checked) {
        $text.addClass('line-through text-ink/50');
        $choreCount.text(Math.max(0, prevCount - 1));
        if (typeof fireConfetti === 'function') {
          const rect = checkbox.getBoundingClientRect();
          fireConfetti(rect.left + 8, rect.top + 8);
        }
      } else {
        $text.removeClass('line-through text-ink/50');
        $choreCount.text(prevCount + 1);
      }

      if (choreId) {
        $checkbox.prop('disabled', true);
        showLoading('Menyinkronkan status tugas...');

        $.ajax({
          url: '/chores/' + choreId + '/toggle',
          type: 'PATCH',
          dataType: 'json',
          success: function(data) {
            if (data.success) {
              showToast(`Status tugas diperbarui (${data.points_awarded > 0 ? '+' : ''}${data.points_awarded} Poin)`);
              if (data.user_points !== undefined && typeof window.updateUserPoints === 'function') {
                window.updateUserPoints(data.user_points);
              }
              if (typeof window.reloadCurrentModuleData === 'function') {
                window.reloadCurrentModuleData({ data: data });
              } else if (data.pending_count !== undefined) {
                updateSidebarChoreCount(data.pending_count);
              }
            }
          },
          error: function() {
            showToast('Gagal memperbarui status tugas.', 'error');
            checkbox.checked = !checkbox.checked;
            $choreCount.text(prevCount);
          },
          complete: function() {
            $checkbox.prop('disabled', false);
            hideLoading();
          }
        });
      }
    };
  }

  function updateSubscriptionUI() {
    const pro = isProUser();
    const planBadge = document.getElementById('plan-badge-sidebar');
    const planDesc = document.getElementById('plan-desc-sidebar');
    const badgeTimeline = document.getElementById('badge-nav-timeline');
    const badgeCalendar = document.getElementById('badge-nav-calendar');
    const overlayTimeline = document.getElementById('overlay-timeline');
    const overlayCalendar = document.getElementById('overlay-calendar');

    if (pro) {
      if (planBadge) {
        planBadge.innerText = 'PRO 👑';
        planBadge.className = 'px-1.5 py-0.5 text-[9px] font-black font-mono brutal-border bg-accent text-ink';
      }
      if (planDesc) planDesc.innerText = 'Aktif (Rp 20rb)';
      if (badgeTimeline) {
        badgeTimeline.innerText = 'PRO ✓';
        badgeTimeline.className = 'px-1.5 py-0.5 bg-brand text-white font-mono text-[10px] brutal-border font-bold';
      }
      if (badgeCalendar) {
        badgeCalendar.innerText = 'PRO ✓';
        badgeCalendar.className = 'px-1.5 py-0.5 bg-brand text-white font-mono text-[10px] brutal-border font-bold';
      }
      if (overlayTimeline) overlayTimeline.classList.add('hidden');
      if (overlayCalendar) overlayCalendar.classList.add('hidden');
    } else {
      if (planBadge) {
        planBadge.innerText = 'FREE';
        planBadge.className = 'px-1.5 py-0.5 text-[9px] font-black font-mono brutal-border bg-coral text-white';
      }
      if (planDesc) planDesc.innerText = 'Rp 20rb/bln';
      if (badgeTimeline) {
        badgeTimeline.innerText = '🔒 PRO';
        badgeTimeline.className = 'px-1.5 py-0.5 bg-coral text-white font-mono text-[10px] brutal-border font-bold';
      }
      if (badgeCalendar) {
        badgeCalendar.innerText = '🔒 PRO';
        badgeCalendar.className = 'px-1.5 py-0.5 bg-coral text-white font-mono text-[10px] brutal-border font-bold';
      }
      if (overlayTimeline) overlayTimeline.classList.remove('hidden');
      if (overlayCalendar) overlayCalendar.classList.remove('hidden');
    }
  }

  function openPaywallModal(targetTab) {
    pendingUnlockTab = targetTab || null;
    const modal = document.getElementById('paywall-modal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  }

  function closePaywallModal() {
    const modal = document.getElementById('paywall-modal');
    if (modal) {
      modal.classList.add('hidden');
    }
    pendingUnlockTab = null;
  }

  let selectedPlanCode = 'pro_monthly';

  function selectPlan(planCode) {
    selectedPlanCode = planCode;
    const btnMonthly = document.getElementById('plan-card-pro_monthly');
    const btnLifetime = document.getElementById('plan-card-lifetime');
    const devToggleInput = document.getElementById('dev-toggle-plan-code');
    const btnSubmitText = document.getElementById('btn-pay-text');

    if (devToggleInput) devToggleInput.value = planCode;

    if (planCode === 'lifetime') {
      if (btnLifetime) {
        btnLifetime.className = 'p-3 bg-brand text-white brutal-border text-left relative transition-all cursor-pointer';
      }
      if (btnMonthly) {
        btnMonthly.className = 'p-3 bg-white text-ink brutal-border text-left relative transition-all cursor-pointer';
      }
      if (btnSubmitText) btnSubmitText.innerText = 'Bayar Rp 150.000 Sekarang';
    } else {
      if (btnMonthly) {
        btnMonthly.className = 'p-3 bg-brand text-white brutal-border text-left relative transition-all cursor-pointer';
      }
      if (btnLifetime) {
        btnLifetime.className = 'p-3 bg-white text-ink brutal-border text-left relative transition-all cursor-pointer';
      }
      if (btnSubmitText) btnSubmitText.innerText = 'Bayar Rp 20.000 Sekarang';
    }
  }

  function selectPayMethod(method) {
    selectedPayMethod = method;
    ['qris', 'gopay', 'bca', 'mandiri'].forEach(m => {
      const btn = document.getElementById('pay-' + m);
      if (btn) {
        if (m === method) {
          btn.classList.add('bg-accent');
          btn.classList.remove('bg-white');
        } else {
          btn.classList.remove('bg-accent');
          btn.classList.add('bg-white');
        }
      }
    });
  }

  function simulatePayment() {
    const $btn = $('#btn-pay-submit');
    const originalText = $btn.html();
    const amountText = (selectedPlanCode === 'lifetime') ? 'Rp 150.000' : 'Rp 20.000';
    $btn.prop('disabled', true).html('<span class="inline-block animate-spin mr-1.5">⚡</span> Memproses Pembayaran ' + amountText + '...');
    showLoading('Memproses pembayaran...');
    fireConfetti();

    $.ajax({
      url: "{{ route('subscription.dev_toggle') }}",
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        plan_code: selectedPlanCode
      },
      dataType: 'json',
      success: function(data) {
        closePaywallModal();
        showToast('Selamat! Seluruh fitur Famespace Pro aktif.');
        setTimeout(function() {
          window.location.reload();
        }, 400);
      },
      error: function() {
        $('#form-dev-toggle').submit();
      },
      complete: function() {
        $btn.prop('disabled', false).html(originalText);
      }
    });
  }

  // Canvas confetti celebration
  function fireConfetti(x, y) {
    const canvas = document.createElement('canvas');
    canvas.style.position = 'fixed';
    canvas.style.inset = '0';
    canvas.style.width = '100vw';
    canvas.style.height = '100vh';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = '9999';
    document.body.appendChild(canvas);
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ['#34D399', '#2F5233', '#E07A5F', '#81B0C4', '#121614', '#87A987'];
    const cx = x || window.innerWidth / 2;
    const cy = y || window.innerHeight / 2;

    const pieces = Array.from({ length: 35 }, () => ({
      x: cx,
      y: cy,
      vx: (Math.random() - 0.5) * 12,
      vy: (Math.random() - 0.7) * 12,
      size: Math.random() * 7 + 4,
      color: colors[Math.floor(Math.random() * colors.length)],
      rot: Math.random() * 360,
      vrot: (Math.random() - 0.5) * 12
    }));

    let frame = 0;
    function loop() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      pieces.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.4;
        p.rot += p.vrot;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate((p.rot * Math.PI) / 180);
        ctx.fillStyle = p.color;
        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
        ctx.restore();
      });
      frame++;
      if (frame < 50) requestAnimationFrame(loop);
      else canvas.remove();
    }
    requestAnimationFrame(loop);
  }

  // ==============================================
  // QUICK MODAL INTERACTIVE LOGIC
  // ==============================================
  let currentModalType = 'task';
  let datePickerInstance = null;

  // Searchable Combobox Data
  const comboboxData = {
    assignee: [
      @foreach($members as $m)
        { 
          id: {{ $m->id }}, 
          value: {{ $m->id }}, 
          name: @json($m->name),
          text: @json(($m->role === 'Ayah' ? '👨 ' : ($m->role === 'Ibu' ? '👩 ' : '👦 ')) . $m->name . ' (' . ($m->role ?? 'Anggota') . ')') 
        },
      @endforeach
    ],
    category: {
      task: [
        { value: 'Dapur', text: '🍳 Dapur' },
        { value: 'Ruang Tamu', text: '🛋️ Ruang Tamu' },
        { value: 'Kamar Mandi', text: '🚿 Kamar Mandi' },
        { value: 'Halaman', text: '🌿 Halaman & Kebun' },
        { value: 'Kamar Tidur', text: '🛏️ Kamar Tidur' },
        { value: 'Umum', text: '📦 Umum / Lainnya' }
      ],
      expense: [
        @if(($categories ?? collect())->where('type', 'expense')->count() > 0)
          @foreach($categories->where('type', 'expense') as $cat)
            { id: {{ $cat->id }}, value: {{ $cat->id }}, name: @json($cat->name), text: @json(($cat->icon ?: '🏷️') . ' ' . $cat->name) },
          @endforeach
        @else
          { id: 'Belanja / Kebutuhan Pokok', value: 'Belanja / Kebutuhan Pokok', text: '🛒 Belanja / Kebutuhan Pokok' },
          { id: 'Tagihan', value: 'Tagihan', text: '⚡ Tagihan' },
          { id: 'Servis Kendaraan', value: 'Servis Kendaraan', text: '🚗 Servis Kendaraan' },
          { id: 'Hiburan', value: 'Hiburan', text: '🎬 Hiburan' },
          { id: 'Kesehatan', value: 'Kesehatan', text: '🏥 Kesehatan' },
          { id: 'Pendidikan', value: 'Pendidikan', text: '🎓 Pendidikan' },
          { id: 'Transportasi', value: 'Transportasi', text: '🚗 Transportasi' },
          { id: 'Sedekah', value: 'Sedekah', text: '🤲 Sedekah' },
          { id: 'Hadiah', value: 'Hadiah', text: '🎁 Hadiah' },
          { id: 'Keluarga', value: 'Keluarga', text: '🏠 Keluarga' },
          { id: 'Lain-lain', value: 'Lain-lain', text: '🏷️ Lain-lain' }
        @endif
      ],
      income: [
        @if(($categories ?? collect())->where('type', 'income')->count() > 0)
          @foreach($categories->where('type', 'income') as $cat)
            { id: {{ $cat->id }}, value: {{ $cat->id }}, name: @json($cat->name), text: @json(($cat->icon ?: '💰') . ' ' . $cat->name) },
          @endforeach
        @else
          { id: 'Gaji', value: 'Gaji', text: '💰 Gaji' },
          { id: 'Usaha', value: 'Usaha', text: '💼 Usaha' },
          { id: 'Bonus', value: 'Bonus', text: '🎁 Bonus' },
          { id: 'Hadiah', value: 'Hadiah', text: '🎁 Hadiah' },
          { id: 'Refund/Cashback', value: 'Refund/Cashback', text: '💸 Refund/Cashback' },
          { id: 'Komisi', value: 'Komisi', text: '🤝 Komisi' },
          { id: 'Lain-lain', value: 'Lain-lain', text: '🏷️ Lain-lain' }
        @endif
      ],
      saving: [
        @if(($categories ?? collect())->where('type', 'saving')->count() > 0)
          @foreach($categories->where('type', 'saving') as $cat)
            { id: {{ $cat->id }}, value: {{ $cat->id }}, name: @json($cat->name), text: @json(($cat->icon ?: '🐷') . ' ' . $cat->name) },
          @endforeach
        @else
          { id: 'Tabungan & Impian', value: 'Tabungan & Impian', text: '🐷 Tabungan & Impian' },
          { id: 'Dana Darurat', value: 'Dana Darurat', text: '🛡️ Dana Darurat' },
          { id: 'Investasi', value: 'Investasi', text: '📈 Investasi' }
        @endif
      ],
      trip: [
        @if(($categories ?? collect())->where('type', 'timeline')->count() > 0)
          @foreach($categories->where('type', 'timeline') as $cat)
            { id: {{ $cat->id }}, value: {{ $cat->id }}, name: @json($cat->name), text: @json(($cat->icon ?: '📸') . ' ' . $cat->name) },
          @endforeach
        @else
          { id: 'Wisata Alam', value: 'Wisata Alam', text: '🌲 Wisata Alam' },
          { id: 'Kuliner', value: 'Kuliner', text: '🍜 Kuliner' },
          { id: 'Staycation', value: 'Staycation', text: '🏨 Staycation' },
          { id: 'Silaturahmi & Mudik', value: 'Silaturahmi & Mudik', text: '🚗 Silaturahmi & Mudik' },
          { id: 'Edukasi & Budaya', value: 'Edukasi & Budaya', text: '🏛️ Edukasi & Budaya' },
          { id: 'Hiburan & Rekreasi', value: 'Hiburan & Rekreasi', text: '🎡 Hiburan & Rekreasi' },
          { id: 'Lain-lain', value: 'Lain-lain', text: '📸 Lain-lain' }
        @endif
      ],
      event: [
        { value: 'Keluarga Inti', text: '🏠 Keluarga Inti' },
        { value: 'Sekolah', text: '🎒 Agenda Sekolah Anak' },
        { value: 'RT/Komunitas', text: '👥 RT / RW Lingkungan' },
        { value: 'Keluarga Besar', text: '👨‍👩‍👧‍👦 Pertemuan Keluarga' },
        { value: 'Kesehatan', text: '🏥 Jadwal Dokter / Imunisasi' }
      ]
    }
  };

  window.comboboxData = comboboxData;

  window.updateCategoryInCombobox = function(cat) {
    if (!cat) return;
    const catType = cat.type || 'expense';
    if (!comboboxData.category[catType]) comboboxData.category[catType] = [];
    const existingIdx = comboboxData.category[catType].findIndex(c => String(c.id) === String(cat.id));
    const item = {
      id: cat.id,
      value: cat.id,
      name: cat.name,
      text: (cat.icon || '🏷️') + ' ' + cat.name
    };
    if (existingIdx >= 0) {
      comboboxData.category[catType][existingIdx] = item;
    } else {
      comboboxData.category[catType].push(item);
    }
    if (document.getElementById('list-category') && !document.getElementById('popover-category')?.classList.contains('hidden')) {
      renderComboboxOptions('category');
    }
  };

  function getActiveCategoryList() {
    return comboboxData.category[currentModalType] || comboboxData.category.expense || comboboxData.category.task;
  }

  function renderComboboxOptions(type, query = '') {
    const listEl = document.getElementById(`list-${type}`);
    if (!listEl) return;
    
    let items = [];
    if (type === 'assignee') {
      items = currentModalType === 'task' 
        ? [{ id: '', value: '', name: 'Semua Anggota', text: '👥 Semua Anggota' }, ...comboboxData.assignee]
        : comboboxData.assignee;
    } else {
      items = getActiveCategoryList();
    }
    const lowerQuery = query.toLowerCase().trim();
    const filtered = items.filter(item => 
      (item.text && item.text.toLowerCase().includes(lowerQuery)) || 
      (item.name && item.name.toLowerCase().includes(lowerQuery)) || 
      (item.value !== undefined && String(item.value).toLowerCase().includes(lowerQuery))
    );
    
    listEl.innerHTML = '';
    
    if (filtered.length === 0) {
      if (type === 'category' && lowerQuery) {
        const customBtn = document.createElement('button');
        customBtn.type = 'button';
        customBtn.className = 'w-full text-left px-2.5 py-1.5 bg-accent brutal-border text-xs font-bold text-ink hover:bg-accent/80 transition-colors';
        customBtn.innerText = `+ Pakai kategori baru: "${query.trim()}"`;
        customBtn.onclick = () => selectComboboxOption('category', query.trim(), `🏷️ ${query.trim()}`);
        listEl.appendChild(customBtn);
        return;
      }
      listEl.innerHTML = '<div class="p-2 text-center text-xs text-ink/60 font-medium">Tidak ada hasil cocok</div>';
      return;
    }
    
    filtered.forEach(item => {
      const btn = document.createElement('button');
      btn.type = 'button';
      const hiddenEl = document.getElementById(`minput-${type}`);
      const currentVal = hiddenEl ? hiddenEl.value : '';
      const targetVal = item.id !== undefined ? item.id : item.value;
      const isSelected = String(currentVal) === String(targetVal);
      btn.className = `w-full text-left px-2.5 py-1.5 text-xs font-bold transition-colors brutal-border flex items-center justify-between ${isSelected ? 'bg-accent text-ink' : 'bg-white hover:bg-cream text-ink'}`;
      btn.innerHTML = `<span>${item.text}</span> ${isSelected ? '<span class="text-xs">✓</span>' : ''}`;
      btn.onclick = () => selectComboboxOption(type, targetVal, item.text);
      listEl.appendChild(btn);
    });
  }

  function toggleCombobox(type) {
    const popover = document.getElementById(`popover-${type}`);
    if (!popover) return;
    const otherType = type === 'assignee' ? 'category' : 'assignee';
    const otherPopover = document.getElementById(`popover-${otherType}`);
    if (otherPopover) otherPopover.classList.add('hidden');
    
    if (popover.classList.contains('hidden')) {
      renderComboboxOptions(type);
      popover.classList.remove('hidden');
      const searchInput = document.getElementById(`search-${type}`);
      if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 50);
      }
    } else {
      popover.classList.add('hidden');
    }
  }

  function filterCombobox(type, val) {
    renderComboboxOptions(type, val);
  }

  function selectComboboxOption(type, value, text) {
    const hiddenInput = document.getElementById(`minput-${type}`);
    const labelSpan = document.getElementById(`label-${type}`);
    if (hiddenInput) hiddenInput.value = value;
    if (labelSpan) labelSpan.innerText = text;
    const popover = document.getElementById(`popover-${type}`);
    if (popover) popover.classList.add('hidden');
  }

  // Close popovers when clicking outside
  document.addEventListener('click', (e) => {
    const rootAssignee = document.getElementById('combobox-assignee-root');
    const rootCategory = document.getElementById('combobox-category-root');
    if (rootAssignee && !rootAssignee.contains(e.target)) {
      document.getElementById('popover-assignee')?.classList.add('hidden');
    }
    if (rootCategory && !rootCategory.contains(e.target)) {
      document.getElementById('popover-category')?.classList.add('hidden');
    }
  });

  // CURRENCY FORMATTER (RUPIAH)
  function formatRupiah(val) {
    if (!val) return '';
    const clean = val.toString().replace(/[^0-9]/g, '');
    if (!clean) return '';
    const num = parseInt(clean, 10);
    if (isNaN(num)) return '';
    return 'Rp ' + num.toLocaleString('id-ID');
  }

  function handleCurrencyInput(input) {
    const clean = input.value.replace(/[^0-9]/g, '');
    if (!clean) {
      input.value = '';
      return;
    }
    input.value = 'Rp ' + parseInt(clean, 10).toLocaleString('id-ID');
  }

  // Custom Datepicker (Flatpickr)
  function initFlatpickr(enableTime = false) {
    if (typeof flatpickr === 'undefined') return;
    if (datePickerInstance) {
      datePickerInstance.destroy();
    }
    datePickerInstance = flatpickr('#minput-date', {
      locale: 'id',
      enableTime: enableTime,
      time_24hr: true,
      dateFormat: enableTime ? 'Y-m-d H:i:00' : 'Y-m-d',
      altInput: true,
      altFormat: enableTime ? 'd M Y, H:i' : 'd M Y',
      defaultDate: new Date(),
      disableMobile: true
    });
  }

  // File Upload & Preview Handler
  function handleFileSelect(e) {
    const file = e.target.files && e.target.files[0];
    if (file) {
      const removeReceiptEl = document.getElementById('finance-input-remove-receipt');
      if (removeReceiptEl) removeReceiptEl.value = '0';
      previewFile(file);
    }
  }

  function previewFile(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const previewImg = document.getElementById('upload-preview-img');
      const fileName = document.getElementById('upload-file-name');
      const fileSize = document.getElementById('upload-file-size');
      const dropzone = document.getElementById('upload-dropzone');
      const previewCard = document.getElementById('upload-preview-card');
      if (previewImg) previewImg.src = e.target.result;
      if (fileName) fileName.innerText = file.name;
      if (fileSize) fileSize.innerText = (file.size / 1024).toFixed(1) + ' KB';
      if (dropzone) dropzone.classList.add('hidden');
      if (previewCard) previewCard.classList.remove('hidden');
      const removeReceiptEl = document.getElementById('finance-input-remove-receipt');
      if (removeReceiptEl) removeReceiptEl.value = '0';
    };
    reader.readAsDataURL(file);
  }

  function removeUploadedFile(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const fileInput = document.getElementById('minput-file');
    if (fileInput) fileInput.value = '';
    const previewImg = document.getElementById('upload-preview-img');
    if (previewImg) previewImg.src = '';
    const previewCard = document.getElementById('upload-preview-card');
    if (previewCard) previewCard.classList.add('hidden');
    const dropzone = document.getElementById('upload-dropzone');
    if (dropzone) dropzone.classList.remove('hidden');
    const removeReceiptEl = document.getElementById('finance-input-remove-receipt');
    if (removeReceiptEl) removeReceiptEl.value = '1';
    const removeAttachmentEl = document.getElementById('chore-input-remove-attachment');
    if (removeAttachmentEl) removeAttachmentEl.value = '1';
  }

  function initDragAndDrop() {
    const dropzone = document.getElementById('upload-dropzone');
    if (!dropzone) return;
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('bg-accent/40');
      }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('bg-accent/40');
      }, false);
    });
    dropzone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files[0]) {
        document.getElementById('minput-file').files = files;
        previewFile(files[0]);
      }
    });
  }

  // Header Quick Menu toggle
  function toggleHeaderQuickMenu() {
    $('#header-quick-menu').toggleClass('hidden');
  }

  $(document).on('click', function(e) {
    if (!$(e.target).closest('#btn-header-quick-action, #header-quick-menu').length) {
      $('#header-quick-menu').addClass('hidden');
    }
  });

  // Global helper fallback for modals
  function openModal(type = 'task') {
    if (type === 'task' && typeof window.openChoreModal === 'function') {
      window.openChoreModal();
    } else if (type === 'expense' && typeof window.openFinanceModal === 'function') {
      window.openFinanceModal();
    } else if (type === 'trip' && typeof window.openTimelineModal === 'function') {
      window.openTimelineModal();
    } else if (type === 'event' && typeof window.openCalendarModal === 'function') {
      window.openCalendarModal();
    } else {
      toggleHeaderQuickMenu();
    }
  }

  function closeModal() {
    if (typeof window.closeChoreModal === 'function') window.closeChoreModal();
    if (typeof window.closeFinanceModal === 'function') window.closeFinanceModal();
    if (typeof window.closeCalendarModal === 'function') window.closeCalendarModal();
    if (typeof window.closeTimelineModal === 'function') window.closeTimelineModal();
    closePaywallModal();
  }

  function showToast(message, type = 'success') {
    const $toast = $('#ajax-toast');
    const $text = $('#ajax-toast-text');
    const $icon = $('#ajax-toast-icon');
    if (!$toast.length || !$text.length) return;

    $text.text(message);
    if (type === 'success') {
      $toast.attr('class', 'fixed top-5 right-5 z-[100] px-4 py-2.5 brutal-card bg-accent text-ink text-xs font-mono font-black flex items-center gap-2 shadow-brutal');
      $icon.text('✨');
    } else {
      $toast.attr('class', 'fixed top-5 right-5 z-[100] px-4 py-2.5 brutal-card bg-coral text-white text-xs font-mono font-black flex items-center gap-2 shadow-brutal');
      $icon.text('⚠️');
    }
    $toast.removeClass('hidden');

    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(function() {
      $toast.addClass('hidden');
    }, 3500);
  }

  let deleteTargetUrl = null;

  function confirmDelete(url, label = 'data ini') {
    deleteTargetUrl = url;
    $('#confirm-delete-label').text(label);
    $('#confirm-delete-modal').removeClass('hidden');
    $('#confirm-delete-btn').focus();
  }

  function closeDeleteModal() {
    $('#confirm-delete-modal').addClass('hidden');
    deleteTargetUrl = null;
  }

  $(document).on('click', '#confirm-delete-btn', function() {
    if (!deleteTargetUrl) return;
    const url = deleteTargetUrl;
    closeDeleteModal();
    showLoading('Menghapus data...');

    $.ajax({
      url: url,
      type: 'DELETE',
      dataType: 'json',
      success: function(data) {
        if (data.success === false) {
          showToast(data.message || 'Gagal menghapus data.', 'error');
        } else {
          showToast(data.message || 'Data berhasil dihapus.');
          if (typeof window.reloadCurrentModuleData === 'function') {
            window.reloadCurrentModuleData();
          }
        }
      },
      error: function(xhr) {
        let msg = 'Gagal menghapus data.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        showToast(msg, 'error');
      },
      complete: function() {
        hideLoading();
      }
    });
  });

  window.updateUserPoints = function(points) {
    if (points === undefined || points === null) return;
    const formatted = Number(points).toLocaleString('id-ID');
    $('#desktop-header-points-val').text(formatted);
    $('#mobile-header-points-val').text(formatted);
    $('#sidebar-user-points-val').text(formatted);
    const $ptsBadge = $('#mobile-points-badge, #desktop-points-badge');
    if ($ptsBadge.length) {
      $ptsBadge.addClass('animate-spring-pop bg-accent');
      setTimeout(() => $ptsBadge.removeClass('animate-spring-pop bg-accent'), 500);
    }
  };

  window.reloadCurrentModuleData = function(moduleHint) {
    const userMeta = document.querySelector('meta[name="user-id"]');
    const currentUserId = userMeta ? Number(userMeta.getAttribute('content')) : null;

    // 1. Chores module
    if (window.choresDataTable) {
      window.choresDataTable.ajax.reload(null, false);
    }
    if (typeof refreshChoresMobile === 'function') {
      refreshChoresMobile();
    }
    if (moduleHint && moduleHint.data) {
      if (moduleHint.data.pending_count !== undefined) {
        if (typeof updateSidebarChoreCount === 'function') {
          updateSidebarChoreCount(moduleHint.data.pending_count);
        }
        if ($('#dash-stat-pending-chores').length) {
          $('#dash-stat-pending-chores').text(moduleHint.data.pending_count);
        }
      }
      if (moduleHint.data.assignee_id && currentUserId && Number(moduleHint.data.assignee_id) === currentUserId && moduleHint.data.assignee_points !== undefined) {
        window.updateUserPoints(moduleHint.data.assignee_points);
      } else if (moduleHint.data.user_points !== undefined && (!moduleHint.userId || Number(moduleHint.userId) === currentUserId)) {
        window.updateUserPoints(moduleHint.data.user_points);
      }
    }

    // Selalu sinkronkan poin pengguna saat ada pembaruan
    if ($('#desktop-header-points-val').length || $('#mobile-header-points-val').length) {
      $.get('/dashboard', function(res) {
        if (res && res.success && res.data && res.data.user_points !== undefined) {
          window.updateUserPoints(res.data.user_points);
        }
      });
    }

    const $choreBanner = $('#chores-kids-banner');
    const $choreStat = $('#chores-completion-stat');
    if ($choreBanner.length || $choreStat.length) {
      $.get(window.location.href, function(html) {
        if ($choreBanner.length) {
          const newBanner = $(html).find('#chores-kids-banner').html();
          if (newBanner) $choreBanner.html(newBanner);
        }
        if ($choreStat.length) {
          const newStat = $(html).find('#chores-completion-stat').html();
          if (newStat) $choreStat.html(newStat);
        }
      });
    }

    // 2. Finance module
    if (window.financeDataTable) {
      window.financeDataTable.ajax.reload(null, false);
    }
    if (typeof refreshFinanceMobile === 'function') {
      refreshFinanceMobile();
    }
    if (window.budgetDataTable) {
      window.budgetDataTable.ajax.reload(null, false);
    }
    if (typeof refreshBudgetMobile === 'function') {
      refreshBudgetMobile();
    }
    if (window.categoryDataTable) {
      window.categoryDataTable.ajax.reload(null, false);
    }
    if (typeof refreshCategoryMobile === 'function') {
      refreshCategoryMobile();
    }
    const $summarySection = $('#finance-summary-cards');
    const $goalsSection = $('#finance-goals-section');
    if ($summarySection.length || $goalsSection.length) {
      $.get(window.location.href, function(html) {
        if ($summarySection.length) {
          const newSummary = $(html).find('#finance-summary-cards').html();
          if (newSummary) $summarySection.html(newSummary);
        }
        if ($goalsSection.length) {
          const newGoals = $(html).find('#finance-goals-section').html();
          if (newGoals) $goalsSection.html(newGoals);
        }
      });
    }
    if (typeof window.fetchReportData === 'function') {
      window.fetchReportData();
    }

    // 3. Calendar module
    if (window.eventsDataTable) {
      window.eventsDataTable.ajax.reload(null, false);
    }
    if (typeof refreshEventsMobile === 'function') {
      refreshEventsMobile();
    }
    const $calGrid = $('#calendar-grid-section');
    if ($calGrid.length) {
      $.get(window.location.href, function(html) {
        const newGrid = $(html).find('#calendar-grid-section').html();
        if (newGrid) $calGrid.html(newGrid);
      });
    }

    // 4. Timeline module
    if (window.timelineDataTable) {
      window.timelineDataTable.ajax.reload(null, false);
    }
    if (typeof loadTimelineMapPoints === 'function') {
      loadTimelineMapPoints();
    } else if (typeof loadMainMapPoints === 'function') {
      loadMainMapPoints();
    }

    // 5. Dashboard refresh
    if ($('#view-overview').length || $('#dashboard-overview-container').length || window.location.pathname.includes('/dashboard')) {
      $.get('/dashboard', function(res) {
        if (res && res.success && res.data) {
          if (res.data.pending_chores !== undefined && typeof updateSidebarChoreCount === 'function') {
            updateSidebarChoreCount(res.data.pending_chores);
          }
        }
      });

      const $overviewCards = $('#overview-stat-cards');
      const $overviewChores = $('#overview-chore-widget');
      const $overviewProgress = $('#overview-chore-progress');
      const $overviewFinance = $('#overview-finance-widget');
      const $overviewCal = $('#overview-calendar-widget');
      if ($overviewCards.length || $overviewChores.length || $overviewProgress.length || $overviewFinance.length || $overviewCal.length) {
        $.get(window.location.href, function(html) {
          if ($overviewCards.length) {
            const newCards = $(html).find('#overview-stat-cards').html();
            if (newCards) $overviewCards.html(newCards);
          }
          if ($overviewChores.length) {
            const newWidget = $(html).find('#overview-chore-widget').html();
            if (newWidget) {
              $overviewChores.html(newWidget);
              if (typeof initChoreRingChart === 'function') initChoreRingChart();
            }
          }
          if ($overviewProgress.length) {
            const newProg = $(html).find('#overview-chore-progress').html();
            if (newProg) $overviewProgress.html(newProg);
          }
          if ($overviewFinance.length) {
            const newFin = $(html).find('#overview-finance-widget').html();
            if (newFin) $overviewFinance.html(newFin);
          }
          if ($overviewCal.length) {
            const newCal = $(html).find('#overview-calendar-widget').html();
            if (newCal) $overviewCal.html(newCal);
          }
        });
      }
    }

    // 6. Family members refresh
    const $membersSection = $('#members-list-section');
    if ($membersSection.length) {
      $.get(window.location.href, function(html) {
        const newMembers = $(html).find('#members-list-section').html();
        if (newMembers) $membersSection.html(newMembers);
      });
    }
  };

  function showImagePreview(url, title = 'Lampiran File') {
    if (!url) return;
    $('#image-preview-src').attr('src', url);
    $('#image-preview-title').text(title);
    $('#image-preview-link').attr('href', url);
    $('#image-preview-modal').removeClass('hidden');
  }

  function closeImagePreviewModal() {
    $('#image-preview-modal').addClass('hidden');
    $('#image-preview-src').attr('src', '');
  }

  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      if (!$('#confirm-delete-modal').hasClass('hidden')) {
        closeDeleteModal();
      }
      if (!$('#image-preview-modal').hasClass('hidden')) {
        closeImagePreviewModal();
      }
    }
  });

  // ==============================================
  // PWA & MOBILE INTERACTIVE MICRO-ACTIONS
  // ==============================================
  window.vibrateDevice = function(ms = 12) {
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
      try { navigator.vibrate(ms); } catch (e) {}
    }
  };

  window.toggleMobileFabMenu = function(forceState) {
    const $menu = $('#fab-speed-dial-menu');
    const $backdrop = $('#fab-backdrop');
    const $icon = $('#bottom-nav-plus-icon, #fab-main-icon');
    if (!$menu.length) return;

    const willOpen = (forceState !== undefined) ? forceState : $menu.hasClass('hidden');

    if (willOpen) {
      window.vibrateDevice(15);
      $backdrop.removeClass('hidden');
      $menu.removeClass('hidden');
      $menu.css({
        'transform': 'translateY(100%)',
        'transition': 'none'
      });
      // Force reflow
      $menu[0].offsetHeight;
      $menu.css({
        'transform': 'translateY(0)',
        'transition': 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1)'
      });
      $icon.addClass('rotate-45');
    } else {
      window.vibrateDevice(10);
      $menu.css({
        'transform': 'translateY(100%)',
        'transition': 'transform 0.2s cubic-bezier(0.4, 0, 1, 1)'
      });
      $icon.removeClass('rotate-45');
      setTimeout(() => {
        $backdrop.addClass('hidden');
        $menu.addClass('hidden');
        $menu.css({ 'transform': '', 'transition': '' });
      }, 200);
    }
  };

  // SWIPE / PULL DOWN GESTURE PADA BOTTOM SHEET
  (function initBottomSheetGesture() {
    let startY = 0;
    let currentY = 0;
    let isDragging = false;
    let menuEl = null;

    $(document).on('touchstart', '#fab-speed-dial-menu, #fab-sheet-handle', function(e) {
      menuEl = document.getElementById('fab-speed-dial-menu');
      if (!menuEl || $(menuEl).hasClass('hidden')) return;
      startY = e.originalEvent.touches[0].clientY;
      currentY = startY;
      isDragging = true;
      menuEl.style.transition = 'none';
    });

    $(document).on('touchmove', function(e) {
      if (!isDragging || !menuEl) return;
      currentY = e.originalEvent.touches[0].clientY;
      const deltaY = currentY - startY;
      if (deltaY > 0) {
        menuEl.style.transform = `translateY(${deltaY}px)`;
      }
    });

    $(document).on('touchend touchcancel', function(e) {
      if (!isDragging || !menuEl) return;
      isDragging = false;
      const deltaY = currentY - startY;
      if (deltaY > 60) {
        window.vibrateDevice(10);
        window.toggleMobileFabMenu(false);
      } else {
        menuEl.style.transition = 'transform 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        menuEl.style.transform = 'translateY(0)';
      }
    });
  })();

  window.animatePointsPill = function(points) {
    window.vibrateDevice(20);
    const $star = $('#mobile-points-star');
    const $badge = $('#mobile-points-badge');
    if ($star.length) {
      $star.addClass('animate-wiggle');
      setTimeout(() => $star.removeClass('animate-wiggle'), 600);
    }
    if ($badge.length) {
      $badge.addClass('animate-spring-pop');
      setTimeout(() => $badge.removeClass('animate-spring-pop'), 400);
    }
    showToast(`⭐ Poin Keluarga: ${Number(points).toLocaleString('id-ID')} poin. Tuntaskan tugas harian untuk mengumpulkan lebih banyak!`);
  };

  window.triggerQuickAction = function(type) {
    window.toggleMobileFabMenu(false);
    if (window.vibrateDevice) window.vibrateDevice(15);

    if (type === 'chore') {
      if (typeof openChoreModal === 'function') {
        openChoreModal();
      } else {
        window.location.href = "{{ route('chores.index') }}?open_create=1";
      }
    } else if (type === 'finance') {
      if (typeof openFinanceModal === 'function') {
        openFinanceModal();
      } else {
        window.location.href = "{{ route('finances.index') }}?open_create=1";
      }
    } else if (type === 'event') {
      @if($isPro)
        if (typeof openCalendarModal === 'function') {
          openCalendarModal();
        } else {
          window.location.href = "{{ route('events.index') }}?open_create=1";
        }
      @else
        if (typeof openPaywallModal === 'function') {
          openPaywallModal('calendar');
        } else {
          alert('Fitur Agenda Jadwal khusus Famespace PRO!');
        }
      @endif
    } else if (type === 'timeline') {
      @if($isPro)
        if (typeof openTimelineModal === 'function') {
          openTimelineModal();
        } else {
          window.location.href = "{{ route('timeline.index') }}?open_create=1";
        }
      @else
        if (typeof openPaywallModal === 'function') {
          openPaywallModal('timeline');
        } else {
          alert('Fitur Linimasa khusus Famespace PRO!');
        }
      @endif
    }
  };

  window.copyInviteCode = function(code) {
    if (window.vibrateDevice) window.vibrateDevice(15);
    navigator.clipboard.writeText(code).then(() => {
      const $icon = $('#sidebar-copy-icon');
      if ($icon.length) $icon.text('✓');

      showToast(`Kode keluarga "${code}" berhasil disalin!`);

      setTimeout(() => {
        if ($icon.length) $icon.text('📋');
      }, 2000);
    }).catch(() => {
      showToast(`Gagal menyalin kode ${code}`, 'error');
    });
  };

  window.copyInviteCodeMobile = function(code) {
    return window.copyInviteCode(code);
  };

  function initMobileScrollShadow() {
    const mainEl = document.getElementById('main-content');
    const headerEl = document.getElementById('mobile-top-bar');
    if (mainEl && headerEl) {
      mainEl.addEventListener('scroll', () => {
        if (mainEl.scrollTop > 10) {
          headerEl.classList.add('shadow-[0_4px_12px_rgba(18,22,20,0.08)]');
        } else {
          headerEl.classList.remove('shadow-[0_4px_12px_rgba(18,22,20,0.08)]');
        }
      }, { passive: true });
    }
  }

  function initOnlineStatusWatcher() {
    const badge = document.getElementById('mobile-offline-badge');
    function checkStatus(e) {
      if (!badge) return;
      if (!navigator.onLine) {
        badge.classList.remove('hidden');
        badge.classList.add('flex');
        showToast('Koneksi internet terputus (PWA Offline)', 'error');
      } else {
        badge.classList.add('hidden');
        badge.classList.remove('flex');
        if (e && e.type === 'online') {
          showToast('Koneksi internet terhubung kembali');
        }
      }
    }
    window.addEventListener('online', checkStatus);
    window.addEventListener('offline', checkStatus);
    checkStatus();
  }

  function initMobileSidebarSwipe() {
    const aside = document.getElementById('sidebar-aside');
    if (!aside) return;
    let startX = 0;
    let startY = 0;

    aside.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
    }, { passive: true });

    aside.addEventListener('touchend', (e) => {
      const endX = e.changedTouches[0].clientX;
      const endY = e.changedTouches[0].clientY;
      const diffX = startX - endX;
      const diffY = Math.abs(startY - endY);

      // Swipe ke kiri untuk menutup sidebar
      if (diffX > 45 && diffX > diffY) {
        toggleMobileSidebar();
      }
    }, { passive: true });
  }

  // PROFILE & AVATAR MODAL
  window.openProfileModal = function() {
    const $modal = $('#profile-modal');
    if ($modal.length) {
      $modal.removeClass('hidden');
    }
  };

  window.closeProfileModal = function() {
    const $modal = $('#profile-modal');
    if ($modal.length) {
      $modal.addClass('hidden');
    }
  };

  window.previewProfileAvatar = function(input) {
    if (input.files && input.files[0]) {
      const file = input.files[0];
      if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran foto terlalu besar. Maksimal 2MB!', 'error');
        input.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#profile-modal-avatar-img').attr('src', e.target.result).removeClass('hidden');
        $('#profile-modal-avatar-placeholder').addClass('hidden');
        $('#profile-remove-avatar-btn').removeClass('hidden');
        $('#profile-remove-avatar-input').val('0');
      };
      reader.readAsDataURL(file);
    }
  };

  window.resetProfileAvatar = function() {
    $('#profile-avatar-input').val('');
    $('#profile-remove-avatar-input').val('1');
    $('#profile-modal-avatar-img').attr('src', '').addClass('hidden');
    $('#profile-modal-avatar-placeholder').removeClass('hidden');
    $('#profile-remove-avatar-btn').addClass('hidden');
  };

  window.handleProfileSubmit = function(e) {
    e.preventDefault();
    const form = document.getElementById('profile-form');
    if (!form) return;

    const formData = new FormData(form);
    const $btn = $('#profile-submit-btn');
    const $spinner = $('#profile-submit-spinner');
    const $text = $('#profile-submit-text');

    $btn.prop('disabled', true);
    $spinner.removeClass('hidden');
    $text.text('Menyimpan...');

    fetch("{{ route('profile.update') }}", {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      body: formData
    })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const errorMsg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal memperbarui profil.');
        throw new Error(errorMsg);
      }
      return data;
    })
    .then((data) => {
      showToast(data.message || 'Profil berhasil diperbarui!');
      
      const newName = data.user?.name;
      const newAvatar = data.user?.avatar_url;
      const currentUserId = $('meta[name="user-id"]').attr('content');

      // Update sidebar
      if (newName) {
        $('#sidebar-user-name').text(newName).attr('title', newName);
      }
      if (newAvatar) {
        $('#sidebar-user-avatar').html(`<img src="${newAvatar}" alt="${newName}" class="w-full h-full object-cover">`);
      } else if (data.user) {
        const initial = (newName || 'U').charAt(0).toUpperCase();
        $('#sidebar-user-avatar').html(`<span>${initial}</span>`);
      }

      // Update mobile header
      if (newName) {
        $('#mobile-header-user-name').text(newName);
      }
      if (newAvatar) {
        $('#mobile-profile-btn-content').html(`<img src="${newAvatar}" alt="${newName}" class="w-full h-full object-cover">`);
        $('#mobile-dropdown-avatar').html(`<img src="${newAvatar}" alt="${newName}" class="w-full h-full object-cover">`);
      } else if (data.user) {
        const initial = (newName || 'U').charAt(0).toUpperCase();
        $('#mobile-profile-btn-content').html(initial);
        $('#mobile-dropdown-avatar').html(`<span>${initial}</span>`);
      }

      // Update members page if present
      if (currentUserId) {
        if (newName) {
          $(`#member-name-${currentUserId}`).text(newName);
        }
        if (newAvatar) {
          $(`#member-avatar-${currentUserId}`).html(`<img src="${newAvatar}" alt="${newName}" class="w-full h-full object-cover">`);
        }
      }

      // Reset modal file input and hidden flag
      $('#profile-avatar-input').val('');
      $('#profile-remove-avatar-input').val('0');

      closeProfileModal();
    })
    .catch((err) => {
      showToast(err.message || 'Terjadi kesalahan saat menyimpan profil.', 'error');
    })
    .finally(() => {
      $btn.prop('disabled', false);
      $spinner.addClass('hidden');
      $text.text('💾 Simpan');
    });
  };

  // Auto-init on page load
  window.addEventListener('DOMContentLoaded', () => {
    updateSubscriptionUI();
    initDragAndDrop();
    initMobileScrollShadow();
    initOnlineStatusWatcher();
    initMobileSidebarSwipe();

    // Registrasi PWA Service Worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js')
        .then((reg) => console.log('Famespace PWA Service Worker aktif:', reg.scope))
        .catch((err) => console.warn('PWA registration error:', err));
    }
  });
</script>
