{{-- resources/views/components/sidebar.blade.php --}}
@props(['highlight' => false])

@php
    $user = Auth::user();
    $roleMap = ['ADMIN' => 'Admin', 'ADVISER' => 'Adviser', 'STUDENT' => 'Student'];
    $roleLabel = $roleMap[$user->role ?? ''] ?? 'User';

    // Safe fallbacks so the badge/panel won’t error if not provided
    $unreadCount = $unreadCount ?? 0;
    $notifications = $notifications ?? collect();
@endphp

<!-- ===== Responsive Sidebar (mobile drawer + desktop sticky) ===== -->
<!-- Mobile toggle button (shows only < md) -->
<button
    id="sidebarToggle"
    class="md:hidden fixed bottom-6 left-6 z-50 inline-flex items-center gap-2 rounded-full shadow-lg bg-indigo-600 text-white px-4 py-3"
    aria-controls="appSidebar"
    aria-expanded="false"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
    </svg>
    Menu
</button>

<!-- Dark overlay for mobile drawer -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-40 md:hidden"></div>

<!-- Sidebar container -->
<div
    id="appSidebar"
    class="
        fixed inset-y-0 left-0 z-50
        w-72 max-w-[85vw]
        -translate-x-full md:translate-x-0
        transition-transform duration-200
        bg-white shadow-lg flex flex-col
        md:static md:inset-auto
        md:w-64
        md:min-h-screen md:sticky md:top-0
    "
    role="navigation"
    aria-label="Primary"
>
    <!-- Logo/Brand + close (mobile) -->
    <div class="p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img
                class="w-14 h-14 rounded-full shadow-sm object-cover"
                src="{{  Auth::user()->avatar ? asset('storage/avatars/' .  Auth::user()->avatar) : asset('storage/avatars/default.png') }}"
                alt="Avatar"
            />
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $roleLabel }}</p>
            </div>
        </div>

        <!-- Close button (mobile only) -->
        <button id="sidebarClose" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <hr class="mx-auto w-[90%] border-gray-200">

    <!-- Scroll area -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-4">
            <ul class="space-y-2">
                @auth
                    @if(auth()->user()->role === 'ADMIN')
                        <li>
                            <a href="{{ route('admin.index') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.users.index','admin.users.create','admin.users.store','admin.users.edit','admin.users.update','admin.users.destroy') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="users" class="w-4 h-4 mr-3"></i>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                                Templates
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.titles.submitted') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.titles.submitted','admin.titles.submitted.view','admin.documents.review') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="file-text" class="w-4 h-4 mr-3"></i>
                                Submitted Titles
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'STUDENT')
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? ' bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.verify')}}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('documents.create','templates.use','titles.verify','titles.chapters') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="edit-3" class="mr-3 w-4 h-4"></i>
                                Create
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.awaiting')}}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('titles.awaiting') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="folder" class="mr-3 w-4 h-4"></i>
                                Awaiting Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.index')}}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('titles.index','documents.show','documents.edit','open.chapters','templates.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="folder" class="mr-3 w-4 h-4"></i>
                                Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{route('documents.submitted')}}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('documents.submitted','documents.view') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="folder" class="mr-3 w-4 h-4"></i>
                                Submitted Documents
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('research-papers.create') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('research-papers.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                                Research Papers
                            </a>
                        </li>
                        <li>
                            <a href="{{route('templates.index')}}"
                               class="hidden flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                                Templates
                            </a>
                        </li>
                    @endif

                    {{-- ================== ADVISER ================== --}}
                    @if($user->role === 'ADVISER')
                        <li>
                            <a href="{{ route('adviser.index') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('adviser.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.advised.index') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('adviser.advised.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="book-open" class="w-4 h-4 mr-3"></i>
                                My Advised Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.titles.browse') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('adviser.titles.browse') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="search" class="w-4 h-4 mr-3"></i>
                                Browse Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.requests.pending') }}"
                               class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('adviser.requests.pending') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <i data-feather="inbox" class="w-4 h-4 mr-3"></i>
                                Pending Requests
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>

        <hr class="mx-auto w-[90%] border-gray-200">

        <!-- Secondary nav -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="#"
                       class="flex hidden items-center p-2 text-gray-700 text-sm hover:bg-indigo-50 rounded-lg transition">
                        <i data-feather="settings" class="mr-3 w-4 h-4"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <!-- Notification Toggle Button -->
                    <a id="toggleNotification"
                       class="flex items-center gap-2 p-2 text-gray-700 text-sm hover:bg-indigo-50 rounded-lg transition cursor-pointer relative"
                    >
                        <div class="relative">
                            <i data-feather="bell" class="w-5 h-5 transition-all" id="notificationIcon"></i>
                        @if($unreadCount > 0)
                            <span id="notifBadge"
                                data-count="{{ $unreadCount }}"
                                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-400 rounded-full leading-none shadow">
                                {{ $unreadCount }}
                            </span>
                        @endif
                        </div>
                        <span>Notification</span>
                    </a>
                </li>
                <li>
                    <a href="{{route('profile.show')}}"
                       class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('profile.show') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="user" class="mr-3 w-4 h-4"></i>
                        Profile
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <hr class="mx-auto w-[90%] border-gray-200">
    <div class="p-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center text-sm w-full p-2 text-gray-700 hover:bg-red-50 rounded-lg transition">
                <i data-feather="log-out" class="w-4 h-4 mr-3"></i>
                Logout
            </button>
        </form>
    </div>
</div>

<!-- Notification Panel: fixed on mobile, anchored on desktop -->
<div
    id="notificationPanel"
    class="hidden fixed top-20 left-4 right-4 md:absolute md:top-4 md:left-[17rem] md:right-auto
           w-auto md:w-96 bg-white shadow-lg rounded-xl p-6 z-[2000] overflow-hidden"
>
    <!-- Header (non-scrolling) -->
    <div class="mb-3">
        <div class="font-semibold text-lg">Updates</div>
        <div class="text-sm text-gray-500">Click notification to mark as read.</div>
    </div>

    <!-- Scrollable list only -->
    <div id="notifList" class="space-y-3 max-h-72 overflow-y-auto pr-1">
        @forelse($notifications as $notif)
            <div class="bg-white border rounded-lg p-3 hover:bg-indigo-50 transition cursor-pointer"
                 data-is-read="{{ $notif->is_read ? '1' : '0' }}"
                 data-id="{{ $notif->id }}"
                 onclick="handleNotifClick(event, {{ $notif->id }}, this)">
                <h4 class="font-semibold text-sm {{ $notif->is_read ? 'text-gray-600' : 'text-indigo-600' }}">
                    {{ $notif->title }}
                </h4>
                <p class="text-xs text-gray-600">{{ $notif->message }}</p>
                <span class="text-[10px] text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <div class="text-center space-y-2 text-gray-500">
                <div class="flex justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01M19 13a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold">Nothing to see here (yet)!</h3>
                <p class="text-xs">You have no notifications at the moment.</p>
            </div>
        @endforelse
    </div>

    <!-- Footer: shows when there are older unread we didn't render -->
    <div id="notifFooter" class="mt-3 hidden">
        <button id="loadOlderNotifs"
                class="w-full text-sm px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-between">
            <span id="olderUnreadText">Load older</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v9.586l3.293-3.293a1 1 0 111.414 1.414l-5 5a1 1 0 01-1.414 0l-5-5A1 1 0 014.707 10.293L8 13.586V4a1 1 0 011-1z"/></svg>
        </button>
        <!-- Optional “view all” link if your JSON endpoint isn’t implemented yet -->
        <a href="{{ url('/notifications') }}" class="mt-2 block text-center text-xs text-indigo-600 hover:underline">
            View all notifications
        </a>
    </div>
</div>


<!-- ===== Scripts (scoped to sidebar only) ===== -->
<script>
    // ===== Drawer bits (unchanged behavior) =====
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        openBtn?.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('overflow-hidden','md:overflow-auto');
        document.body.classList.add('overflow-hidden','md:overflow-auto');
    }
    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        openBtn?.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('overflow-hidden');
        document.body.classList.remove('overflow-hidden');
    }
    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });

    // ===== Notification panel toggle (keep panel open on item click) =====
    const toggleBtn = document.getElementById('toggleNotification');
    const panel = document.getElementById('notificationPanel');
    const icon = document.getElementById('notificationIcon');

    toggleBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        const isHidden = panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        if (isHidden) {
            toggleBtn.classList.add('bg-indigo-100','text-indigo-600');
            icon?.classList.add('text-indigo-600');
        } else {
            toggleBtn.classList.remove('bg-indigo-100','text-indigo-600');
            icon?.classList.remove('text-indigo-600');
        }
    });

    // Close when clicking outside ONLY
    document.addEventListener('click', function (event) {
        if (!panel?.contains(event.target) && !toggleBtn?.contains(event.target)) {
            panel?.classList.add('hidden');
            toggleBtn?.classList.remove('bg-indigo-100','text-indigo-600');
            icon?.classList.remove('text-indigo-600');
        }
    });

    // ===== Mark-as-read without closing or reloading =====
    const badge = document.getElementById('notifBadge');

    function updateUnreadBadge(delta) {
        if (!badge) return;
        const current = parseInt(badge.getAttribute('data-count') || '0', 10);
        const next = Math.max(0, current + delta);
        badge.setAttribute('data-count', String(next));
        if (next <= 0) {
            // hide/remove badge when zero
            badge.remove();
        } else {
            badge.textContent = String(next);
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

    // Click handler on each notification card
    async function handleNotifClick(e, id, el) {
        // Keep the panel open
        e.stopPropagation();

        // If already marked, do nothing
        if (el.dataset.isRead === '1') return;

        // Optimistic UI: mark styles immediately
        el.dataset.isRead = '1';
        const titleEl = el.querySelector('h4');
        titleEl?.classList.remove('text-indigo-600');
        titleEl?.classList.add('text-gray-600');
        el.classList.add('opacity-80');

        // Decrement unread badge
        updateUnreadBadge(-1);

        try {
            await markAsReadRequest(id);
            // success: do nothing else (panel remains open)
        } catch (err) {
            // Revert UI on error
            el.dataset.isRead = '0';
            titleEl?.classList.remove('text-gray-600');
            titleEl?.classList.add('text-indigo-600');
            el.classList.remove('opacity-80');
            updateUnreadBadge(+1);
            console.error(err);
        }
    }

    // Make available globally for inline onclick
    window.handleNotifClick = handleNotifClick;
</script>




<script>
    // Count visible unread vs. badge count; if fewer, show footer hint
    (function computeHiddenUnreadHint(){
        const badge = document.getElementById('notifBadge');
        const list  = document.getElementById('notifList');
        const footer = document.getElementById('notifFooter');
        const text = document.getElementById('olderUnreadText');
        if (!badge || !list || !footer || !text) return;

        const badgeCount = parseInt(badge.getAttribute('data-count') || '0', 10);
        const visibleUnread = Array.from(list.querySelectorAll('[data-is-read="0"]')).length;
        const hiddenUnread = Math.max(0, badgeCount - visibleUnread);

        if (hiddenUnread > 0) {
            text.textContent = `Load older (${hiddenUnread} unread not shown)`;
            footer.classList.remove('hidden');
        } else {
            footer.classList.add('hidden');
        }
    })();

    // Progressive enhancement: try to fetch older items, else fallback opens /notifications
    document.getElementById('loadOlderNotifs')?.addEventListener('click', async () => {
        const list  = document.getElementById('notifList');
        const last  = list.querySelector('[data-id]:last-child');
        const lastId = last ? last.getAttribute('data-id') : '';

        try {
            // Adjust this URL to your real JSON endpoint when available.
            // Expected JSON: { items: [{id, title, message, is_read, created_at}], next_after: "id-or-null" }
            const res = await fetch(`/notifications/list?after=${encodeURIComponent(lastId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('No JSON endpoint yet');

            const data = await res.json();
            if (!data.items || !Array.isArray(data.items) || data.items.length === 0) return;

            // Append new cards
            for (const n of data.items) {
                const card = document.createElement('div');
                card.className = 'bg-white border rounded-lg p-3 hover:bg-indigo-50 transition cursor-pointer';
                card.setAttribute('data-is-read', n.is_read ? '1' : '0');
                card.setAttribute('data-id', n.id);
                card.onclick = (e) => handleNotifClick(e, n.id, card);
                card.innerHTML = `
                    <h4 class="font-semibold text-sm ${n.is_read ? 'text-gray-600' : 'text-indigo-600'}">${n.title}</h4>
                    <p class="text-xs text-gray-600">${n.message}</p>
                    <span class="text-[10px] text-gray-400">${n.created_at}</span>
                `;
                list.appendChild(card);
            }

            // Recompute footer hint after appending
            (function recompute(){
                const badge = document.getElementById('notifBadge');
                const footer = document.getElementById('notifFooter');
                const text = document.getElementById('olderUnreadText');
                const badgeCount = parseInt(badge?.getAttribute('data-count') || '0', 10);
                const visibleUnread = Array.from(list.querySelectorAll('[data-is-read="0"]')).length;
                const hiddenUnread = Math.max(0, badgeCount - visibleUnread);
                if (hiddenUnread > 0) {
                    text.textContent = `Load older (${hiddenUnread} unread not shown)`;
                    footer.classList.remove('hidden');
                } else {
                    footer.classList.add('hidden');
                }
            })();
        } catch (e) {
            // If JSON route not implemented yet, just go to the full page
            window.location.href = '/notifications';
        }
    });
</script>

<script>
/**
 * CKEditor z-index fix when mobile drawer is open.
 * - Injects a <style> with !important z-index overrides while drawer is visible on small screens
 * - Cleans up on close, resize to ≥ md, or navigation
 */

(function () {
  const STYLE_ID = 'ck-zfix-style';
  const SIDEBAR_ID = 'appSidebar';
  const OPEN_BTN_ID = 'sidebarToggle';
  const CLOSE_BTN_ID = 'sidebarClose';
  const OVERLAY_ID = 'sidebarOverlay';

  const mdDown = () => window.matchMedia('(max-width: 767.98px)').matches;
  const sidebar = document.getElementById(SIDEBAR_ID);
  const openBtn = document.getElementById(OPEN_BTN_ID);
  const closeBtn = document.getElementById(CLOSE_BTN_ID);
  const overlay = document.getElementById(OVERLAY_ID);

  // The actual CSS override (keep z-index lower than your drawer/overlay).
  const STYLE_CSS = `
/* ↓ Force CKEditor surfaces below the drawer on small screens */
@media (max-width: 767.98px) {
  .ck.ck-reset_all,
  .ck.ck-editor__top,
  .ck.ck-toolbar,
  .ck.ck-dropdown__panel,
  .ck.ck-balloon-panel,
  .ck.ck-panel,
  .ck-editor__editable,
  .ck.ck-body-wrapper {
    z-index: 20 !important;
  }
}
`;

  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    const tag = document.createElement('style');
    tag.id = STYLE_ID;
    tag.type = 'text/css';
    tag.appendChild(document.createTextNode(STYLE_CSS));
    document.head.appendChild(tag);
  }

  function removeStyle() {
    const tag = document.getElementById(STYLE_ID);
    if (tag) tag.remove();
  }

  function sidebarIsOpen() {
    // Open == drawer DOES NOT have -translate-x-full
    return sidebar && !sidebar.classList.contains('-translate-x-full');
  }

  function maybeApplyFix() {
    if (mdDown() && sidebarIsOpen()) {
      injectStyle();
    } else {
      removeStyle();
    }
  }

  // Hook into your existing open/close flows
  openBtn?.addEventListener('click', () => {
    // The class toggle happens in your openSidebar(); defer slightly
    setTimeout(maybeApplyFix, 0);
  });

  closeBtn?.addEventListener('click', () => {
    setTimeout(maybeApplyFix, 0);
  });

  overlay?.addEventListener('click', () => {
    setTimeout(maybeApplyFix, 0);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setTimeout(maybeApplyFix, 0);
  });

  // In case classes change via transition or programmatically
  const mo = new MutationObserver(() => maybeApplyFix());
  if (sidebar) mo.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

  // Remove fix if user rotates or resizes to desktop
  window.addEventListener('resize', maybeApplyFix);

  // Safety: cleanup on page show (bfcache restores)
  window.addEventListener('pageshow', maybeApplyFix);

  // Initialize once on load
  maybeApplyFix();
})();
</script>

