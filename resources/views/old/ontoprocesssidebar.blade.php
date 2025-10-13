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
    class="sidebar-container fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full md:translate-x-0 transition-all duration-300 bg-white flex border-r border-slate-200 flex-col md:static md:inset-auto md:w-64 md:min-h-screen md:sticky md:top-0 overflow-hidden"
    role="navigation"
    aria-label="Primary"
>
    <!-- Logo/Brand + close (mobile) + collapse button -->
    <div class="p-4 flex items-center justify-between relative">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0 sidebar-logo">
            <!-- Logo container -->
            <div class="w-10 h-10 rounded-2xl overflow-hidden ring-1 ring-slate-200 shadow-sm flex items-center justify-center bg-white">
                <img
                    src="{{ asset('storage/images/chainlogo.png') }}"
                    alt="Chainscholar Logo"
                    class="w-8 h-8 object-contain"
                />
            </div>

            <!-- Brand text -->
            <div class="min-w-0 sidebar-text transition-all duration-300">
                <p class="text-sm font-semibold text-gray-800 truncate">Chainscholar</p>
                <p class="text-xs text-gray-500 truncate">Research platform</p>
            </div>
        </a>

        <!-- Close button (mobile only) -->
        <button id="sidebarClose" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        
        <!-- Collapse button (desktop only) - Fixed positioning -->
        <button id="sidebarCollapse" class="hidden md:flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 transition-colors sidebar-collapse-btn" title="Collapse sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <hr class="sidebar-divider mx-auto w-[90%] border-gray-200 transition-all duration-300">

    <!-- Scroll area -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden">
        <div class="p-4 sidebar-content">
            <ul class="space-y-1">
                @auth
                    @if(auth()->user()->role === 'ADMIN')
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('admin.index') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('admin.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="home" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('dashboard') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? ' bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="layout" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Research Library</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('admin.users.index') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('admin.users.index','admin.users.create','admin.users.store','admin.users.edit','admin.users.update','admin.users.destroy') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="users" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Users</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('templates.index') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="layers" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Templates</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('admin.titles.awaiting') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('admin.titles.awaiting') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="relative flex items-center">
                                    <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                        <i data-feather="shield" class="w-4 h-4 sidebar-icon"></i>
                                    </div>
                                    @if($awaitingAdminCount > 0)
                                        <span
                                            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-400 rounded-full leading-none shadow sidebar-badge"
                                            aria-label="{{ $awaitingAdminCount }} awaiting admin">
                                            {{ $awaitingAdminCount > 99 ? '99+' : $awaitingAdminCount }}
                                        </span>
                                    @endif
                                </div>
                                <span class="sidebar-text transition-all duration-300">Pending Titles</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('admin.titles.submitted') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('admin.titles.submitted','admin.titles.submitted.view','admin.documents.review','documents.submitted') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="inbox" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Submitted Titles</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'STUDENT')
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('dashboard') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? ' bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="layout" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Research Library</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{route('titles.verify')}}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('documents.create','templates.use','titles.verify') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="file-plus" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">New Title</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{route('titles.awaiting')}}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('titles.awaiting') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="clock" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Pending Titles</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{route('titles.index')}}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('titles.index','documents.show','documents.edit','open.chapters','templates.index','titles.chapters') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="folder" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Approved Titles</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{route('documents.submitted')}}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('documents.submitted','documents.view') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="send" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Final Papers</span>
                            </a>
                        </li>
                    @endif

                    {{-- ================== ADVISER ================== --}}
                    @if($user->role === 'ADVISER')
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('adviser.index') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('adviser.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="layout" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('dashboard') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('dashboard','dashboard.search','dashboard.view') ? ' bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="layout" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Research Library</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('adviser.advised.index') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('adviser.advised.*','documents.view') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="bookmark" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Advised Titles</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('adviser.titles.browse') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('adviser.titles.browse') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="search" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Open Titles</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item-wrapper">
                            <a href="{{ route('adviser.requests.pending') }}"
                               class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('adviser.requests.pending') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="alert-circle" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                <span class="sidebar-text transition-all duration-300">Approval Requests</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>

        <hr class="sidebar-divider mx-auto w-[90%] border-gray-200 transition-all duration-300">

        <!-- Research Papers -->
        <nav class="p-4 sidebar-content">
            <ul class="space-y-1">
                <li class="sidebar-menu-item-wrapper">
                    <a href="{{ route('research-papers.create') }}"
                       class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('research-papers.create') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                            <i data-feather="upload" class="w-4 h-4 sidebar-icon"></i>
                        </div>
                        <span class="sidebar-text transition-all duration-300">Upload Research</span>
                    </a>
                </li>
                <li class="sidebar-menu-item-wrapper">
                    <a href="{{ route('research-papers.student-index') }}"
                       class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('research-papers.student-index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                            <i data-feather="file-text" class="w-4 h-4 sidebar-icon"></i>
                        </div>
                        <span class="sidebar-text transition-all duration-300">My Research Papers</span>
                    </a>
                </li>
                @if(auth()->user()->role === 'ADMIN')
                    <li class="sidebar-menu-item-wrapper">
                        <a href="{{ route('research-papers.admin-index') }}"
                           class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('research-papers.admin-index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                            <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                <i data-feather="book-open" class="w-4 h-4 sidebar-icon"></i>
                            </div>
                            <span class="sidebar-text transition-all duration-300">All Research Papers</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <hr class="sidebar-divider mx-auto w-[90%] border-gray-200 transition-all duration-300">

        <!-- Secondary nav -->
        <nav class="p-4 sidebar-content">
            <ul class="space-y-1">
                {{-- Announcements --}}
                @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="sidebar-menu-item-wrapper">
                        <a href="{{ route('announcements.index') }}"
                           class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('announcements.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                            <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                <i data-feather="calendar" class="w-4 h-4 sidebar-icon"></i>
                            </div>
                            <span class="sidebar-text transition-all duration-300">View Announcements</span>
                            @if($announcementsCount > 0)
                                <span class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-indigo-500 rounded-full sidebar-badge transition-all duration-300">
                                    {{ $announcementsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @else
                    <li class="sidebar-menu-item-wrapper">
                        <a href="{{ route('announcements.index') }}"
                           class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('announcements.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                            <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                <i data-feather="calendar" class="w-4 h-4 sidebar-icon"></i>
                            </div>
                            <span class="sidebar-text transition-all duration-300">View Announcements</span>
                            @if($announcementsCount > 0)
                                <span class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-indigo-500 rounded-full sidebar-badge transition-all duration-300">
                                    {{ $announcementsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endif

                <!-- Account links -->
                @if($user->role === 'ADVISER')
                    <li class="sidebar-menu-item-wrapper">
                        <a href="{{ route('adviser.profile.edit') }}"
                           class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('adviser.profile.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                            <div class="relative flex items-center">
                                <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                                    <i data-feather="user-check" class="w-4 h-4 sidebar-icon"></i>
                                </div>
                                @if($profileIncomplete)
                                    <span class="absolute -top-1 -right-1 inline-block w-2 h-2 bg-amber-500 rounded-full" title="Complete your adviser profile"></span>
                                @endif
                            </div>
                            <span class="sidebar-text transition-all duration-300">Info</span>
                            @if($profileIncomplete)
                                <span class="ml-auto text-[11px] font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded sidebar-badge transition-all duration-300">
                                    Incomplete
                                </span>
                            @endif
                        </a>
                    </li>
                @endif

                <li class="sidebar-menu-item-wrapper">
                    <a href="{{ route('activity.index') }}"
                       class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('activity.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                            <i data-feather="activity" class="w-4 h-4 sidebar-icon"></i>
                        </div>
                        <span class="sidebar-text transition-all duration-300">Activity Logs</span>
                    </a>
                </li>

                <li class="sidebar-menu-item-wrapper">
                    <a href="{{ route('profile.show') }}"
                       class="sidebar-menu-item flex items-center py-2 px-3 rounded-lg transition text-sm {{ request()->routeIs('profile.show') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                            <i data-feather="user" class="w-4 h-4 sidebar-icon"></i>
                        </div>
                        <span class="sidebar-text transition-all duration-300">Profile</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <hr class="sidebar-divider mx-auto w-[90%] border-gray-200 transition-all duration-300">
    
    <div class="p-4 sidebar-content">
        <!-- Logout button -->
        <button
            type="button"
            onclick="window.showLogoutModal && window.showLogoutModal()"
            class="sidebar-menu-item flex items-center text-sm w-full py-2 px-3 text-gray-700 hover:bg-red-50 rounded-lg transition"
        >
            <div class="sidebar-icon-wrapper w-4 h-4 flex items-center justify-center mr-3">
                <i data-feather="log-out" class="w-4 h-4 sidebar-icon"></i>
            </div>
            <span class="sidebar-text transition-all duration-300">Logout</span>
        </button>
    </div>
</div>

<!-- ===== Enhanced Styles for Sidebar Collapse ===== -->
<style>
    /* Collapsed sidebar styles */
    .sidebar-collapsed .sidebar-container {
        width: 3.5rem !important;
    }
    
    .sidebar-collapsed .sidebar-text {
        opacity: 0;
        visibility: hidden;
        width: 0;
        margin: 0;
    }
    
    .sidebar-collapsed .sidebar-badge {
        opacity: 0;
        visibility: hidden;
    }
    
    .sidebar-collapsed .sidebar-divider {
        width: 2rem;
        margin: 0.25rem auto;
    }
    
    .sidebar-collapsed .sidebar-collapse-btn svg {
        transform: rotate(180deg);
    }
    
    /* CONSISTENT MENU ITEM SIZING */
    .sidebar-menu-item-wrapper {
        height: 2.5rem;
        display: flex;
        align-items: center;
    }
    
    .sidebar-menu-item {
        width: 100%;
        height: 2.5rem;
        display: flex;
        align-items: center;
        min-height: auto !important;
    }
    
    /* Menu item adjustments for collapsed state - CONSISTENT SIZING */
    .sidebar-collapsed .sidebar-menu-item-wrapper {
        height: 2.5rem;
        justify-content: center;
    }
    
    .sidebar-collapsed .sidebar-menu-item {
        width: 2.5rem;
        height: 2.5rem;
        padding: 0.5rem !important;
        justify-content: center;
        border-radius: 0.5rem;
    }
    
    .sidebar-collapsed .sidebar-icon-wrapper {
        margin-right: 0 !important;
    }
    
    /* Reduce spacing in collapsed state */
    .sidebar-collapsed .sidebar-content {
        padding: 0.5rem !important;
    }
    
    .sidebar-collapsed .space-y-1 > * + * {
        margin-top: 0.125rem;
    }
    
    /* Ensure icons are properly visible and centered */
    .sidebar-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .sidebar-icon {
        display: block;
        width: 1rem;
        height: 1rem;
    }
    
    /* Smooth transitions */
    .sidebar-container,
    .sidebar-text,
    .sidebar-badge,
    .sidebar-divider,
    .sidebar-menu-item,
    .sidebar-menu-item-wrapper,
    .sidebar-icon-wrapper,
    .sidebar-content {
        transition: all 0.3s ease-in-out;
    }
    
    /* Collapse button styling */
    .sidebar-collapse-btn {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
    }

    /* Fix for badge positioning in Pending Titles */
    .sidebar-menu-item .relative {
        display: flex;
        align-items: center;
    }

    /* Prevent horizontal scrollbar */
    .sidebar-container {
        overflow-x: hidden;
    }
</style>

<!-- ===== Enhanced JavaScript ===== -->
<script>
    // ===== Drawer functionality =====
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

    // ===== Enhanced Collapse functionality =====
    const collapseBtn = document.getElementById('sidebarCollapse');
    const SIDEBAR_COLLAPSED_KEY = 'sidebarCollapsed';
    
    // Check localStorage for saved state
    function isSidebarCollapsed() {
        return localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true';
    }
    
    // Toggle sidebar collapse
    function toggleSidebarCollapse() {
        const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed);
        
        // Update button title and aria-label
        const title = isCollapsed ? 'Expand sidebar' : 'Collapse sidebar';
        collapseBtn.setAttribute('title', title);
        collapseBtn.setAttribute('aria-label', title);
        
        // Refresh Feather icons if needed
        if (typeof feather !== 'undefined') {
            setTimeout(() => feather.replace(), 50);
        }
    }
    
    // Initialize sidebar state
    function initSidebarState() {
        if (isSidebarCollapsed()) {
            document.documentElement.classList.add('sidebar-collapsed');
            collapseBtn.setAttribute('title', 'Expand sidebar');
            collapseBtn.setAttribute('aria-label', 'Expand sidebar');
        } else {
            collapseBtn.setAttribute('title', 'Collapse sidebar');
            collapseBtn.setAttribute('aria-label', 'Collapse sidebar');
        }
        
        // Ensure collapse button is visible on desktop
        if (window.innerWidth >= 768) {
            collapseBtn?.classList.remove('hidden');
            collapseBtn?.classList.add('flex');
        }
    }
    
    // Set up event listener
    collapseBtn?.addEventListener('click', toggleSidebarCollapse);
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            collapseBtn?.classList.remove('hidden');
            collapseBtn?.classList.add('flex');
        } else {
            collapseBtn?.classList.add('hidden');
            collapseBtn?.classList.remove('flex');
            // Ensure sidebar is expanded on mobile
            document.documentElement.classList.remove('sidebar-collapsed');
        }
    });
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initSidebarState();
        
        // Re-initialize after Feather icons are loaded
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

<!-- CKEditor z-index fix script remains the same -->
<script>
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

  const STYLE_CSS = `
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
    return sidebar && !sidebar.classList.contains('-translate-x-full');
  }

  function maybeApplyFix() {
    if (mdDown() && sidebarIsOpen()) {
      injectStyle();
    } else {
      removeStyle();
    }
  }

  openBtn?.addEventListener('click', () => {
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

  const mo = new MutationObserver(() => maybeApplyFix());
  if (sidebar) mo.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

  window.addEventListener('resize', maybeApplyFix);
  window.addEventListener('pageshow', maybeApplyFix);
  maybeApplyFix();
})();
</script>