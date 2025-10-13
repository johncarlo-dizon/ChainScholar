@props([
  'user' => Auth::user(),
])

@php
  $avatar = $user?->avatar
      ? asset('storage/avatars/' . $user->avatar)
      : asset('storage/avatars/default.png');
@endphp

<!-- Pill -->
<!-- Pill -->
<button id="accountPill"
  class="flex items-center gap-2 px-2 pr-3 h-10 rounded-lg bg-white/10 hover:bg-white/20  transition cursor-pointer
         w-24 basis-24 flex-none overflow-hidden">
  <span class="sr-only">Open account menu</span>
  <div class="w-6 h-6 shrink-0 rounded-full overflow-hidden border border-gray-400">
    <img src="{{ $avatar }}" alt="User avatar" class="w-full h-full object-cover" />
  </div>
  <span class="flex-1 min-w-0 text-sm font-medium text-white truncate whitespace-nowrap">
    {{ $user?->name ?? 'User' }}
  </span>
</button>


<!-- Dialog - No backdrop, matches notification styling -->
<div id="profileDialog"
     class="hidden fixed z-[99999] w-80 bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden" style="z-index:99999">

  <!-- Header -->
  <div class="px-4 py-3 border-b border-gray-200 bg-white">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold text-gray-900">Account</h3>
      <button class="rounded-lg p-1 hover:bg-gray-100 transition" type="button" id="profileDialogClose" aria-label="Close">
        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Content -->
  <div class="p-4 space-y-4">
    <!-- User Info -->
    <div class="flex items-center gap-3">
     <div class="w-12 h-12 shrink-0 rounded-full overflow-hidden border-2 border-gray-200 ">
    <img src="{{ $avatar }}" alt="User avatar" class="w-full h-full object-cover " />
  </div>
      <div class="min-w-0 flex-1">
        <p class="font-medium text-gray-900 truncate">{{ $user?->name ?? 'User' }}</p>
        @if($user?->email)
          <p class="text-sm text-gray-600 mt-1 truncate">{{ $user->email }}</p>
        @endif
      </div>
    </div>

    <!-- Logout Button -->
    <button type="button"
            class="w-full rounded-lg text-gray-600 bg-white px-4 py-2.5 hover:text-red-700 transition font-medium text-sm border border-gray-300 mt-4"
            id="profileLogoutBtn">
      Logout
    </button>
  </div>
</div>

@push('scripts')
<script>
  (function(){
    const pill = document.getElementById('accountPill');
    const dialog = document.getElementById('profileDialog');
    const close = document.getElementById('profileDialogClose');
    const logoutBtn = document.getElementById('profileLogoutBtn');

    // Position the dialog
    function positionProfileDialog(){
      if (!pill || !dialog) return;
      const r = pill.getBoundingClientRect();
      // 8px gap below the pill
      const top = r.bottom + 8 + window.scrollY;
      // Align right edges so it looks attached to the pill
      const panelW = dialog.offsetWidth || 320; // w-80 = 320px
      let left = r.right - panelW + window.scrollX;

      // Clamp to viewport (8px padding)
      const minL = 8 + window.scrollX;
      const maxL = window.scrollX + window.innerWidth - panelW - 8;
      left = Math.max(minL, Math.min(maxL, left));

      dialog.style.top = `${top}px`;
      dialog.style.left = `${left}px`;
    }

    // Open the dialog under the pill
    pill?.addEventListener('click', (e) => {
      e.stopPropagation();
      dialog.classList.toggle('hidden');
      
      if (!dialog.classList.contains('hidden')) {
        positionProfileDialog();
      }
    });

    // Keep it anchored on resize/scroll while open
    window.addEventListener('resize', () => { 
      if (!dialog?.classList.contains('hidden')) positionProfileDialog(); 
    }, { passive:true });
    
    window.addEventListener('scroll', () => { 
      if (!dialog?.classList.contains('hidden')) positionProfileDialog(); 
    }, { passive:true });

    // Close button
    close?.addEventListener('click', () => {
      dialog?.classList.add('hidden');
    });

    // Click outside to close
    document.addEventListener('click', (e) => {
      if (!dialog?.contains(e.target) && !pill?.contains(e.target)) {
        dialog?.classList.add('hidden');
      }
    });

    // Logout => call global confirmation
    logoutBtn?.addEventListener('click', () => {
      dialog?.classList.add('hidden');
      if (typeof window.showLogoutModal === 'function') {
        window.showLogoutModal();
      } else {
        // fallback: submit directly if global modal not present
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = "{{ route('logout') }}";
        const t = document.createElement('input');
        t.type = 'hidden';
        t.name = '_token';
        t.value = "{{ csrf_token() }}";
        f.appendChild(t);
        document.body.appendChild(f);
        f.submit();
      }
    });
  })();
</script>
@endpush