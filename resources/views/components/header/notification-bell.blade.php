@props([
  'unreadCount' => 0,
  'notifications' => collect(),
])

<div class="relative">
  <!-- Your Original Bell Button -->
  <button id="topNotifToggle"
          class="relative flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 transition"
          aria-haspopup="true" aria-expanded="false">
    <i data-feather="bell" class="w-5 h-5"></i>
    @if(($unreadCount ?? 0) > 0)
      <span id="topNotifBadge"
            data-count="{{ $unreadCount }}"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-400 rounded-full leading-none shadow">
        {{ ($unreadCount ?? 0) > 99 ? '99+' : $unreadCount }}
      </span>
    @endif
  </button>

  <!-- Responsive Notification Panel -->
  <div id="topNotificationPanel"
       class="hidden fixed z-[9999] bg-white shadow-2xl rounded-lg border border-gray-200 overflow-hidden"
       style="max-height: 32rem; width: 95vw; max-width: 24rem;">

    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 bg-white">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">Notifications</h3>
        <span class="text-sm text-gray-500">Click to mark as read</span>
      </div>
    </div>

    <!-- Notification List -->
    <div id="topNotifList" class="overflow-y-auto" style="max-height: 20rem;">
      @forelse($notifications as $notif)
        <div class="border-b border-gray-100 last:border-b-0 {{ !$notif->is_read ? 'bg-blue-50' : 'hover:bg-gray-50' }} transition cursor-pointer"
             data-is-read="{{ $notif->is_read ? '1' : '0' }}"
             data-id="{{ $notif->id }}"
             onclick="handleTopNotifClick(event, {{ $notif->id }}, this)">
          <div class="px-4 py-3">
            <div class="flex items-start space-x-3">
              <!-- Notification Icon -->
              <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              
              <!-- Notification Content -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 {{ !$notif->is_read ? 'font-semibold' : '' }}">
                  {{ $notif->title }}
                </p>
                <p class="text-sm text-gray-600 mt-1">{{ $notif->message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
              </div>
              
              <!-- Unread Indicator -->
              @if(!$notif->is_read)
                <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <!-- Empty State -->
        <div class="px-4 py-8 text-center">
          <div class="flex justify-center mb-3">
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM8.515 6.53a4 4 0 11-5.657 5.656 4 4 0 015.657-5.656z"/>
            </svg>
          </div>
          <p class="text-sm text-gray-500">No notifications yet</p>
          <p class="text-xs text-gray-400 mt-1">We'll notify you when something arrives</p>
        </div>
      @endforelse
    </div>

    <!-- Footer with Load Older / Show New Toggle -->
    <div id="topNotifFooter" class="border-t border-gray-200 bg-gray-50">
      <button id="topLoadOlderNotifs"
              class="w-full text-sm text-gray-600 hover:text-gray-800 transition flex items-center justify-between px-4 py-3 hover:bg-gray-100"
              data-showing="new">
        <span id="topOlderUnreadText">Load older notifications</span>
        <svg id="loadOlderIcon" class="w-4 h-4 transform rotate-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const toggle = document.getElementById('topNotifToggle');
  const panel = document.getElementById('topNotificationPanel');
  const badge = document.getElementById('topNotifBadge');
  const list = document.getElementById('topNotifList');
  const footer = document.getElementById('topNotifFooter');
  const olderText = document.getElementById('topOlderUnreadText');
  const loadOlderBtn = document.getElementById('topLoadOlderNotifs');
  const loadOlderIcon = document.getElementById('loadOlderIcon');

  let showingOlder = false;
  let olderNotifications = [];

  // Toggle panel with responsive positioning
  toggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    panel.classList.toggle('hidden');
    const expanded = !panel.classList.contains('hidden');
    toggle.setAttribute('aria-expanded', expanded);

    if (!expanded) return;

    positionPanel();
    computeHiddenUnreadHint();
  });

  // Responsive positioning function
  function positionPanel() {
    const r = toggle.getBoundingClientRect();
    const panelWidth = panel.offsetWidth || 320;
    const panelHeight = panel.offsetHeight || 400;
    
    // Position below button with spacing
    let top = r.bottom + 15;
    let left = r.right - panelWidth;
    
    // Ensure panel stays within viewport on small screens
    const viewportPadding = 8;
    
    if (left < viewportPadding) {
      left = viewportPadding;
    }
    
    if (left + panelWidth > window.innerWidth - viewportPadding) {
      left = window.innerWidth - panelWidth - viewportPadding;
    }
    
    // If panel would go below viewport, position above button
    if (top + panelHeight > window.innerHeight - viewportPadding) {
      top = r.top - panelHeight - 15;
    }
    
    panel.style.top = `${top + window.scrollY}px`;
    panel.style.left = `${left + window.scrollX}px`;
  }

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (!panel?.contains(e.target) && !toggle?.contains(e.target)) {
      panel?.classList.add('hidden');
      toggle?.setAttribute('aria-expanded', 'false');
      // Reset to showing new notifications when closed
      resetToNewNotifications();
    }
  });

  // Reposition on resize
  window.addEventListener('resize', () => {
    if (!panel || panel.classList.contains('hidden')) return;
    positionPanel();
  }, { passive:true });

  // Reset to new notifications
  function resetToNewNotifications() {
    if (showingOlder) {
      showingOlder = false;
      loadOlderBtn.setAttribute('data-showing', 'new');
      olderText.textContent = 'Load older notifications';
      loadOlderIcon.classList.remove('rotate-180');
      loadOlderIcon.classList.add('rotate-0');
    }
  }

  // === Your Original Mark-as-Read Functionality ===
  function updateTopUnreadBadge(delta) {
    if (!badge) return;
    const current = parseInt(badge.getAttribute('data-count') || '0', 10);
    const next = Math.max(0, current + delta);
    badge.setAttribute('data-count', String(next));
    if (next <= 0) {
      badge.remove();
    } else {
      badge.textContent = (next > 99) ? '99+' : String(next);
    }
  }

  async function markAsReadRequest(id) {
    const res = await fetch(`/notifications/read/${id}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    if (!res.ok) throw new Error('Failed to mark as read');
    return res.json().catch(() => ({}));
  }

  async function handleTopNotifClick(e, id, el) {
    e.stopPropagation();
    if (el.dataset.isRead === '1') return;

    el.dataset.isRead = '1';
    el.classList.remove('bg-blue-50');
    el.classList.add('hover:bg-gray-50');
    
    // Remove unread indicator
    const indicator = el.querySelector('.bg-blue-600');
    if (indicator) indicator.remove();
    
    // Remove bold from title
    const titleEl = el.querySelector('p.font-semibold');
    if (titleEl) titleEl.classList.remove('font-semibold');

    updateTopUnreadBadge(-1);

    try {
      await markAsReadRequest(id);
      computeHiddenUnreadHint();
    } catch (err) {
      // revert on error
      el.dataset.isRead = '0';
      el.classList.remove('hover:bg-gray-50');
      el.classList.add('bg-blue-50');
      if (titleEl) titleEl.classList.add('font-semibold');
      updateTopUnreadBadge(+1);
      console.error(err);
    }
  }
  window.handleTopNotifClick = handleTopNotifClick;

  // === Enhanced Load Older / Show New Toggle ===
  function computeHiddenUnreadHint(){
    if (!list) return;
    const badgeCount = parseInt(badge?.getAttribute('data-count') || '0', 10);
    const visibleUnread = Array.from(list.querySelectorAll('[data-is-read="0"]')).length;
    const hiddenUnread = Math.max(0, badgeCount - visibleUnread);

    if (!showingOlder) {
      if (hiddenUnread > 0) {
        olderText.textContent = `Load older (${hiddenUnread} unread not shown)`;
      } else {
        olderText.textContent = 'Load older notifications';
      }
    }
  }

  loadOlderBtn?.addEventListener('click', async () => {
    if (!showingOlder) {
      // Load older notifications
      const last = list?.querySelector('[data-id]:last-child');
      const lastId = last ? last.getAttribute('data-id') : '';

      try {
        const res = await fetch(`/notifications/list?after=${encodeURIComponent(lastId)}`, {
          headers: { 'Accept': 'application/json' }
        });
        
        if (!res.ok) throw new Error('No JSON endpoint yet');

        const data = await res.json();
        if (!data.items || !Array.isArray(data.items) || data.items.length === 0) {
          olderText.textContent = 'No older notifications';
          return;
        }

        // Store current notifications
        olderNotifications = Array.from(list.children);
        
        // Clear and show older notifications
        list.innerHTML = '';
        for (const n of data.items) {
          const card = document.createElement('div');
          card.className = `border-b border-gray-100 last:border-b-0 ${!n.is_read ? 'bg-blue-50' : 'hover:bg-gray-50'} transition cursor-pointer`;
          card.setAttribute('data-is-read', n.is_read ? '1' : '0');
          card.setAttribute('data-id', n.id);
          card.onclick = (e) => handleTopNotifClick(e, n.id, card);
          
          card.innerHTML = `
            <div class="px-4 py-3">
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 ${!n.is_read ? 'font-semibold' : ''}">${n.title}</p>
                  <p class="text-sm text-gray-600 mt-1">${n.message}</p>
                  <p class="text-xs text-gray-400 mt-1">${n.created_at}</p>
                </div>
                ${!n.is_read ? '<div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></div>' : ''}
              </div>
            </div>
          `;
          list.appendChild(card);
        }

        showingOlder = true;
        loadOlderBtn.setAttribute('data-showing', 'old');
        olderText.textContent = 'Show new notifications';
        loadOlderIcon.classList.remove('rotate-0');
        loadOlderIcon.classList.add('rotate-180');

      } catch (e) {
        console.error('Failed to load older notifications:', e);
        olderText.textContent = 'Failed to load older';
      }
    } else {
      // Show new notifications again
      list.innerHTML = '';
      olderNotifications.forEach(child => list.appendChild(child));
      
      showingOlder = false;
      loadOlderBtn.setAttribute('data-showing', 'new');
      olderText.textContent = 'Load older notifications';
      loadOlderIcon.classList.remove('rotate-180');
      loadOlderIcon.classList.add('rotate-0');
      computeHiddenUnreadHint();
    }
  });

})();
</script>
@endpush