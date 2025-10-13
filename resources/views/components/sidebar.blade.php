{{-- resources/views/components/sidebar.blade.php --}}
@props(['highlight' => false])

@php
    $user = Auth::user();
    $roleMap = ['ADMIN' => 'Admin', 'ADVISER' => 'Adviser', 'STUDENT' => 'Student'];
    $roleLabel = $roleMap[$user->role ?? ''] ?? 'User';

    // Safe fallbacks
    $unreadCount = $unreadCount ?? 0;
    $notifications = $notifications ?? collect();

    // Announcements data
    $announcementsCount = $announcementsCount ?? 0;
    $recentAnnouncements = $recentAnnouncements ?? collect();
    $awaitingAdminCount = $awaitingAdminCount ?? 0;

    // Adviser profile completeness indicator
    $profileIncomplete = false;
    if ($user && $user->role === 'ADVISER') {
        $p = $user->adviserProfile; // lazy-load ok
        $profileIncomplete = !$p
            || empty($p->department)
            || empty($p->field_of_expertise)
            || empty($p->highest_degree)
            || empty($p->degree_school)
            || empty($p->degree_year);
    }
@endphp

<style>
    .sidebar-container {
        background: #1e293b;
        border-right: 1px solid #334155;
    }
    
    .sidebar-item {
        transition: all 0.2s ease;
    }
    
    .sidebar-item:hover {
        background: rgba(255, 255, 255, 0.08);
    }
    
    .sidebar-item.active {
        background: #3b82f6;
        color: white;
    }
    
    .sidebar-divider {
        border-color: #334155;
    }
    
    .brand-gradient {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    }
    
    .notification-badge {
        background: #ef4444;
        color: white;
    }
    
    .incomplete-badge {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }
</style>

<!-- ===== Responsive Sidebar (mobile drawer + desktop sticky) ===== -->
<!-- Mobile toggle button (shows only < md) -->
<button
    id="sidebarToggle"
    class="md:hidden fixed bottom-6 left-6 z-50 inline-flex items-center gap-2 rounded-full shadow-lg text-white px-4 py-3 brand-gradient"
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
        w-70 max-w-[85vw]
        -translate-x-full md:translate-x-0
        transition-transform duration-200
        text-white flex border-r border-slate-200 flex-col
        md:static md:inset-auto
        md:w-64
        md:min-h-screen md:sticky md:top-0
        sidebar-container
    "
    role="navigation"
    aria-label="Primary"
>
    <!-- Logo/Brand + close (mobile) -->
    <div class="p-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <!-- Logo container -->
            <div class="w-10 h-10 rounded-2xl overflow-hidden ring-1 ring-slate-200/20 shadow-sm flex items-center justify-center bg-white">
                <img
                    src="{{ asset('storage/images/chainlogo.png') }}"
                    alt="Chainscholar Logo"
                    class="w-8 h-8 object-contain"
                />
            </div>

            <!-- Brand text -->
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">Chainscholar</p>
                <p class="text-xs text-white/70 truncate">Research platform</p>
            </div>
        </a>

        <!-- Close button (mobile only) -->
        <button id="sidebarClose" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-white/70 hover:bg-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <hr class="sidebar-divider mx-auto w-[90%]">

    <!-- Scroll area -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-4">
            <ul class="space-y-2">
                @auth
                    @if(auth()->user()->role === 'ADMIN')
                        <li>
                            <a href="{{ route('admin.index') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('admin.index') ? 'active' : 'text-white/80' }}">
                                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? 'active' : 'text-white/80' }}">
                                <i data-feather="layout" class="w-4 h-4 mr-3"></i>
                                Research Library
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('admin.users.index','admin.users.create','admin.users.store','admin.users.edit','admin.users.update','admin.users.destroy') ? 'active' : 'text-white/80' }}">
                                <i data-feather="users" class="w-4 h-4 mr-3"></i>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'active' : 'text-white/80' }}">
                                <i data-feather="layers" class="mr-3 w-4 h-4"></i>
                                Templates
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.titles.awaiting') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('admin.titles.awaiting') ? 'active' : 'text-white/80' }}">
                                <span class="relative mr-3">
                                    <i data-feather="shield" class="w-5 h-5"></i>
                                    @if($awaitingAdminCount > 0)
                                    <span
                                        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white notification-badge rounded-full leading-none shadow"
                                        aria-label="{{ $awaitingAdminCount }} awaiting admin">
                                        {{ $awaitingAdminCount > 99 ? '99+' : $awaitingAdminCount }}
                                    </span>
                                    @endif
                                </span>
                                <span>Pending Titles</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.titles.submitted') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('admin.titles.submitted','admin.titles.submitted.view','admin.documents.review','documents.submitted') ? 'active' : 'text-white/80' }}">
                                <i data-feather="inbox" class="w-4 h-4 mr-3"></i>
                                Submitted Titles
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'STUDENT')
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? 'active' : 'text-white/80' }}">
                                <i data-feather="layout" class="w-4 h-4 mr-3"></i>
                                Research Library
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.verify')}}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('documents.create','templates.use','titles.verify') ? 'active' : 'text-white/80' }}">
                                <i data-feather="file-plus" class="mr-3 w-4 h-4"></i>
                                New Title
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.awaiting')}}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('titles.awaiting') ? 'active' : 'text-white/80' }}">
                                <i data-feather="clock" class="mr-3 w-4 h-4"></i>
                                Pending Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{route('titles.index')}}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('titles.index','documents.show','documents.edit','open.chapters','templates.index','titles.chapters') ? 'active' : 'text-white/80' }}">
                                <i data-feather="folder" class="mr-3 w-4 h-4"></i>
                                Approved Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{route('documents.submitted')}}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('documents.submitted','documents.view') ? 'active' : 'text-white/80' }}">
                                <i data-feather="send" class="mr-3 w-4 h-4"></i>
                                Final Papers
                            </a>
                        </li>
                        <li>
                            <a href="{{route('templates.index')}}"
                               class="hidden flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'active' : 'text-white/80' }}">
                                <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                                Templates
                            </a>
                        </li>
                    @endif

                    {{-- ================== ADVISER ================== --}}
                    @if($user->role === 'ADVISER')
                        <li>
                            <a href="{{ route('adviser.index') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('adviser.index') ? 'active' : 'text-white/80' }}">
                                <i data-feather="layout" class="w-4 h-4 mr-3"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? 'active' : 'text-white/80' }}">
                                <i data-feather="layout" class="w-4 h-4 mr-3"></i>
                                Research Library
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.advised.index') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('adviser.advised.*','documents.view') ? 'active' : 'text-white/80' }}">
                                <i data-feather="bookmark" class="w-4 h-4 mr-3"></i>
                                Advised Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.titles.browse') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('adviser.titles.browse') ? 'active' : 'text-white/80' }}">
                                <i data-feather="search" class="w-4 h-4 mr-3"></i>
                                Open Titles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('adviser.requests.pending') }}"
                               class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('adviser.requests.pending') ? 'active' : 'text-white/80' }}">
                                <i data-feather="alert-circle" class="w-4 h-4 mr-3"></i>
                                Approval Requests
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>

        <hr class="sidebar-divider mx-auto w-[90%]">

        <!-- Research Papers (flat links) -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('research-papers.create') }}"
                       class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('research-papers.create') ? 'active' : 'text-white/80' }}">
                        <i data-feather="upload" class="mr-3 w-4 h-4"></i>
                        Upload Research
                    </a>
                </li>
                <li>
                    <a href="{{ route('research-papers.student-index') }}"
                       class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('research-papers.student-index') ? 'active' : 'text-white/80' }}">
                        <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                        My Research Papers
                    </a>
                </li>
                @if(auth()->user()->role === 'ADMIN')
                    <li>
                        <a href="{{ route('research-papers.admin-index') }}"
                           class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('research-papers.admin-index') ? 'active' : 'text-white/80' }}">
                            <i data-feather="book-open" class="w-4 h-4 mr-3"></i>
                            All Research Papers
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <hr class="sidebar-divider mx-auto w-[90%]">

        <!-- Secondary nav -->
        <nav class="p-4">
            <ul class="space-y-2">
                {{-- Announcements (flat links) --}}
                @if(auth()->check() && auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('announcements.index') }}"
                           class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('announcements.*') ? 'active' : 'text-white/80' }}">
                            <i data-feather="calendar" class="mr-3 w-4 h-4"></i>
                            View Announcements
                            @if($announcementsCount > 0)
                                <span class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white notification-badge rounded-full">
                                    {{ $announcementsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('announcements.index') }}"
                           class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('announcements.index') ? 'active' : 'text-white/80' }}">
                            <i data-feather="calendar" class="mr-3 w-4 h-4"></i>
                            View Announcements
                            @if($announcementsCount > 0)
                                <span class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white notification-badge rounded-full">
                                    {{ $announcementsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endif

                <!-- Account (flat links) -->
                @if($user->role === 'ADVISER')
                    <li>
                        <a href="{{ route('adviser.profile.edit') }}"
                           class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('adviser.profile.edit') ? 'active' : 'text-white/80' }}">
                            <span class="relative mr-3">
                                <i data-feather="user-check" class="w-4 h-4"></i>
                                @if($profileIncomplete)
                                    <span class="absolute -top-1 -right-1 inline-block w-2 h-2 bg-amber-300 rounded-full" title="Complete your adviser profile"></span>
                                @endif
                            </span>
                            Info
                            @if($profileIncomplete)
                                <span class="ml-auto text-[11px] font-semibold incomplete-badge px-2 py-0.5 rounded">
                                    Incomplete
                                </span>
                            @endif
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('activity.index') }}"
                       class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('activity.index') ? 'active' : 'text-white/80' }}">
                        <i data-feather="activity" class="w-4 h-4 mr-3"></i>
                        Activity Logs
                    </a>
                </li>

                <li>
                    <a href="{{ route('profile.show') }}"
                       class="flex items-center py-1.5 px-2 rounded-lg transition text-sm sidebar-item {{ request()->routeIs('profile.show') ? 'active' : 'text-white/80' }}">
                        <i data-feather="user" class="mr-3 w-4 h-4"></i>
                        Profile
                    </a>
                </li>

                <li>
                    <a href="#"
                       class="flex hidden items-center py-1.5 px-2 text-white/80 text-sm sidebar-item rounded-lg transition">
                        <i data-feather="settings" class="mr-3 w-4 h-4"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <hr class="sidebar-divider mx-auto w-[90%]">
    <div class="p-4">
        <!-- Logout button (triggers GLOBAL modal) -->
        <button
            type="button"
            onclick="window.showLogoutModal && window.showLogoutModal()"
            class="flex items-center text-sm w-full p-2 text-white/80 hover:bg-red-500/20 rounded-lg transition sidebar-item"
        >
            <i data-feather="log-out" class="w-4 h-4 mr-3"></i>
            Logout
        </button>
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