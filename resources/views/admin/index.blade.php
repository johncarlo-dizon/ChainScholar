<x-userlayout>
@php
    // Light helpers (no controller changes needed)
    $totalTitles      = (int)($metrics['titles_total']        ?? 0);
    $titlesAdvising   = (int)($metrics['titles_in_advising']  ?? 0);
    $titlesSubmitted  = (int)($metrics['titles_submitted']    ?? 0);
    $titlesAwaiting   = (int)($metrics['titles_awaiting']     ?? 0);
    $titlesWithAdv    = (int)($metrics['titles_with_adviser'] ?? 0);
    $titlesFinal      = (int)($metrics['titles_final']        ?? 0);

    $papersTotal      = (int)($metrics['papers_total']        ?? 0);
    $usersTotal       = (int)($metrics['users_total']         ?? 0);
    $adminsCount      = (int)($metrics['admins']              ?? 0);
    $advisersCount    = (int)($metrics['advisers']            ?? 0);
    $studentsCount    = (int)($metrics['students']            ?? 0);
    $pendingAR        = (int)($pendingAdviserRequests         ?? 0);

    // Percent helpers (safe divide)
    $pc = fn($num,$den) => $den>0 ? round(($num/$den)*100) : 0;

    $pctWithAdviser   = $pc($titlesWithAdv, $totalTitles);
    $pctFinalized     = $pc($titlesFinal,   $totalTitles);
    $pctAwaiting      = $pc($titlesAwaiting,$totalTitles);
    $pctSubmitted     = $pc($titlesSubmitted,$totalTitles);
@endphp

    <!-- Header / Hero -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-500 to-blue-600">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -top-10 -left-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-2xl"></div>
        </div>
        <div class="relative p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-white text-2xl sm:text-3xl font-bold tracking-tight">Admin Dashboard</h2>
                    <p class="mt-1 text-indigo-100 text-sm">High-level view of Titles, Research Papers, Announcements, and Adviser flow.</p>
                </div>

                <!-- Compact Announcement Pill -->
                <a href="{{ route('announcements.index') }}"
                   class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-full bg-white/10 text-white text-xs font-medium hover:bg-white/20 transition">
                    <i data-feather="volume-2" class="w-4 h-4"></i>
                    <span>Announcements</span>
                    <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-white/20 text-[11px]">
                        {{ $announcementsTotal ?? 0 }}
                    </span>
                </a>
            </div>

            <!-- Quick progress (Titles) -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 hidden">
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-white">
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-90">With Adviser</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10">{{ $pctWithAdviser }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded bg-white/20 overflow-hidden">
                        <div class="h-2 bg-white/90" style="width: {{ $pctWithAdviser }}%"></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-white">
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-90">Finalized</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10">{{ $pctFinalized }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded bg-white/20 overflow-hidden">
                        <div class="h-2 bg-white/90" style="width: {{ $pctFinalized }}%"></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-white">
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-90">Awaiting Admin</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10">{{ $pctAwaiting }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded bg-white/20 overflow-hidden">
                        <div class="h-2 bg-white/90" style="width: {{ $pctAwaiting }}%"></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-white">
                    <div class="flex items-center justify-between">
                        <span class="text-sm opacity-90">Submitted</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10">{{ $pctSubmitted }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded bg-white/20 overflow-hidden">
                        <div class="h-2 bg-white/90" style="width: {{ $pctSubmitted }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <!-- Users -->
        <div class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600">
                        <i data-feather="users" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $usersTotal }}</p>
                    </div>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <div>Admins: <span class="font-medium text-gray-700">{{ $adminsCount }}</span></div>
                    <div>Advisers: <span class="font-medium text-gray-700">{{ $advisersCount }}</span></div>
                    <div>Students: <span class="font-medium text-gray-700">{{ $studentsCount }}</span></div>
                </div>
            </div>
        </div>

        <!-- Titles -->
        <a href="{{ route('admin.titles.submitted') }}"
           class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600">
                        <i data-feather="file-text" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Titles</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalTitles }}</p>
                    </div>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <div>Submitted: <span class="font-medium text-gray-700">{{ $titlesSubmitted }}</span></div>
                    <div>In Advising: <span class="font-medium text-gray-700">{{ $titlesAdvising }}</span></div>
                    <div>Finalized: <span class="font-medium text-gray-700">{{ $titlesFinal }}</span></div>
                </div>
            </div>
        </a>

        <!-- Awaiting Admin -->
        <a href="{{ route('admin.titles.awaiting') }}"
           class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 text-amber-600">
                        <i data-feather="shield" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Awaiting Admin</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $titlesAwaiting }}</p>
                    </div>
                </div>
                <span class="text-amber-700 bg-amber-100 px-2 py-1 rounded text-xs font-semibold">Review Now</span>
            </div>
        </a>

         <a href="{{ route('research-papers.admin-index') }}"
           class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
             <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-feather="book-open" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Research Papers (PDF)</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $papersTotal }}</p>
                    </div>
                </div>
            <span 
                class="text-emerald-700 bg-emerald-100 px-2 py-1 rounded text-xs font-semibold hover:bg-emerald-200">
                Open 
                </span>

            </div>
        </a>

     

        <!-- Adviser Requests -->
        <div class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-fuchsia-50 text-fuchsia-600">
                        <i data-feather="inbox" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Adviser Requests</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $pendingAR }}</p>
                    </div>
                </div>
                <span class="text-fuchsia-700 bg-fuchsia-100 px-2 py-1 rounded text-xs font-semibold">Pending</span>
            </div>
        </div>

        <!-- Announcements -->
        <a href="{{ route('announcements.manage') }}"
           class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600">
                        <i data-feather="calendar" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Announcements</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $announcementsTotal ?? 0 }}</p>
                    </div>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <div>Urgent: <span class="font-medium text-gray-700">{{ $annByTier['urgent'] ?? 0 }}</span></div>
                    <div>Important: <span class="font-medium text-gray-700">{{ $annByTier['important'] ?? 0 }}</span></div>
                    <div>General: <span class="font-medium text-gray-700">{{ $annByTier['general'] ?? 0 }}</span></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Content Rows -->
    <div class="mt-6 grid grid-cols-1 2xl:grid-cols-2 gap-6">
        <!-- Recent Titles / Research Papers Toggle -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="p-3 border-b border-gray-300 flex items-center justify-between">
                <h3 id="recentTableTitle" class="font-semibold text-gray-900">Recent System Research</h3>
                <div class="flex items-center gap-2">
                    <button type="button" id="toggleRecent"
                            class="text-xs px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition">
                     Show PDF Uploads
                    </button>
                </div>
            </div>
            <div id="recentTitlesWrapper" class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500 ">
                        <tr>
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Owner</th>
                            <th class="py-2 pr-4">Adviser</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTitles ?? [] as $t)
                            <tr class="border-t border-gray-300 hover:bg-gray-50">
                                <td class="py-2 pr-4 font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($t->title, 60) }}</td>
                                <td class="py-2 pr-4 text-gray-700">{{ $t->owner?->name ?? '—' }}</td>
                                <td class="py-2 pr-4 text-gray-700">{{ $t->primaryAdviser?->name ?? '—' }}</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded text-xs
                                        @class([
                                            'bg-slate-100 text-slate-700' => !in_array($t->status, ['awaiting_admin','submitted','in_advising']),
                                            'bg-amber-100 text-amber-700' => $t->status === 'awaiting_admin',
                                            'bg-blue-100 text-blue-700'   => $t->status === 'submitted',
                                            'bg-emerald-100 text-emerald-700' => $t->status === 'in_advising',
                                        ])">
                                        {{ $t->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-gray-500">No recent titles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="recentPapersWrapper" class="hidden p-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Authors</th>
                            <th class="py-2 pr-4">Program</th>
                            <th class="py-2">Uploaded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPapers ?? [] as $p)
                            <tr class="border-t  border-gray-300 hover:bg-gray-50">
                                <td class="py-2 pr-4 font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($p->title, 72) }}</td>
                                <td class="py-2 pr-4 text-gray-700">{{ \Illuminate\Support\Str::limit($p->authors ?? '—', 48) }}</td>
                                <td class="py-2 pr-4 text-gray-700">{{ $p->program ?? '—' }}</td>
                                <td class="py-2 text-gray-700">{{ $p->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-gray-500">No recent research PDFs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Announcements (timeline style) -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="p-3 border-b border-gray-300 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Announcements</h3>
                <a href="{{ route('announcements.manage') }}" class="text-xs text-indigo-600 hover:underline">Manage</a>
            </div>

            <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming by event date -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i data-feather="clock" class="w-4 h-4 text-gray-500"></i> Upcoming
                    </h4>
                    <ul class="relative space-y-4">
                        @forelse($upcomingAnnouncements ?? [] as $a)
                            <li class="relative pl-6">
                                <span class="absolute left-0 top-1.5 w-3 h-3 rounded-full {{ $a->tier === 'URGENT' ? 'bg-red-400' : ($a->tier === 'IMPORTANT' ? 'bg-amber-400' : 'bg-blue-400') }}"></span>
                                <div class="flex items-start gap-2">
                                    <span class="px-2 py-0.5 rounded text-[11px] {{ $a->tier_badge_classes }}">{{ $a->tier_label }}</span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $a->title }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ optional($a->event_date)->format('M d, Y') ?? 'No date' }} • Audience: {{ $a->audience_label }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No upcoming announcements.</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Recent created -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i data-feather="clock" class="w-4 h-4 text-gray-500"></i> Recent
                    </h4>
                    <ul class="space-y-3">
                        @forelse($recentAnnouncements ?? [] as $a)
                            <li class="flex items-start gap-3">
                                <span class="px-2 py-0.5 rounded text-[11px] {{ $a->tier_badge_classes }}">{{ $a->tier_label }}</span>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $a->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $a->created_at->diffForHumans() }} • Audience: {{ $a->audience_label }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No recent announcements.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
  function attachRecentToggle() {
    const toggleBtn   = document.getElementById('toggleRecent');
    const titleEl     = document.getElementById('recentTableTitle');
    const titlesWrap  = document.getElementById('recentTitlesWrapper');
    const papersWrap  = document.getElementById('recentPapersWrapper');
    if (!toggleBtn || !titleEl || !titlesWrap || !papersWrap) return;

    // Avoid duplicate listeners on re-renders
    if (toggleBtn.dataset.bound === '1') return;
    toggleBtn.dataset.bound = '1';

    function flip(e) {
      if (e) { e.preventDefault(); e.stopPropagation(); }
      const showingTitles = !titlesWrap.classList.contains('hidden');
      if (showingTitles) {
        titlesWrap.classList.add('hidden');
        papersWrap.classList.remove('hidden');
        titleEl.textContent = 'Recent PDF Research';
        toggleBtn.textContent = 'Show System Entries';
        toggleBtn.setAttribute('aria-pressed', 'true');
      } else {
        papersWrap.classList.add('hidden');
        titlesWrap.classList.remove('hidden');
        titleEl.textContent = 'Recent System Research';
        toggleBtn.textContent = 'Show PDF Uploads';
        toggleBtn.setAttribute('aria-pressed', 'false');
      }
    }

    toggleBtn.addEventListener('click', flip);
    // Optional: expose a global fallback (e.g., onclick="window.__toggleRecent()")
    window.__toggleRecent = flip;
  }

  // Bind for various stacks
  document.addEventListener('DOMContentLoaded', attachRecentToggle);
  document.addEventListener('turbo:load', attachRecentToggle);
  document.addEventListener('turbolinks:load', attachRecentToggle);
  document.addEventListener('livewire:navigated', attachRecentToggle);
  document.addEventListener('alpine:init', attachRecentToggle);
})();
</script>

</x-userlayout>


 