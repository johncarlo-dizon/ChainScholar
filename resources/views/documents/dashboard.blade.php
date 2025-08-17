<x-userlayout>
    <div class="container mx-auto px-4 py-10">
        <!-- Header / Hero -->
        <div class="rounded-3xl shadow-lg bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-500 p-8 md:p-10 mb-10 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight">ChainScholar</h1>
                    <p class="mt-2 text-blue-100 text-base md:text-lg">Explore and compare research titles with ease.</p>
                </div>
                <div class="hidden md:block text-right">
                    <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-8 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2h-2M7 5H5a2 2 0 00-2 2v12a2 2 0 002 2h2"/>
                        </svg>
                        Tip: Press <kbd class="px-1.5 py-0.5 bg-white/20 rounded">/</kbd> to focus search
                    </span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <form method="POST" action="{{ route('dashboard.search') }}" class="max-w-3xl mx-auto mb-10" id="search-form">
            @csrf
            <div class="group relative flex items-center rounded-2xl border border-blue-200 bg-white focus-within:ring-2 focus-within:ring-blue-400 shadow-sm transition">
                <span class="pl-4 text-blue-500">
                    <i data-feather="search" class="w-5 h-5"></i>
                </span>
                <input
                    type="text"
                    name="query"
                    id="queryInput"
                    value="{{ old('query', $query ?? '') }}"
                    autocomplete="off"
                    class="w-full px-3 py-3 rounded-2xl focus:outline-none text-gray-800 placeholder-gray-400"
                    placeholder="Search research title…"
                    aria-label="Search research title"
                />
                <button type="submit"
                        class="ml-auto bg-blue-600 hover:bg-blue-700 text-white rounded-r-2xl px-5 py-3 transition flex items-center gap-2">
                    <span class="hidden sm:inline">Search</span>
                    <i data-feather="arrow-right" class="w-5 h-5"></i>
                </button>
            </div>
        </form>

        <!-- Results -->
        @if(isset($results))
            <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-lg p-6 md:p-8">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-6">
                    <h3 class="text-xl md:text-2xl font-semibold text-gray-800">
                        🔍 Results <span class="text-gray-500">({{ count($results) }})</span>
                    </h3>
                    @if(!empty($query))
                        <span class="text-sm text-gray-500">for “<span class="font-medium text-gray-700">{{ $query }}</span>”</span>
                    @endif
                </div>

                @forelse ($results as $result)
                    @php
                        $pct = round(($result['similarity'] ?? 0) * 100, 2);
                        $isTop = $loop->first;
                        $badgeColor = $pct >= 70 ? 'bg-green-100 text-green-700' : ($pct >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                    @endphp

                    <a href="{{ route('dashboard.view', $result['id']) }}"
                       class="block mb-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition bg-white">
                        <div class="p-3 md:p-5">
                        
                                <div class="min-w-0">
                                    <h4 class="text-base md:text-lg font-semibold text-blue-700 truncate">
                                        {{ $result['title'] }}
                                        @if($isTop)
                                            <span class="ml-2 align-middle text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-600 font-semibold">🔥 Highest Match</span>
                                        @endif
                                    </h4>
                                    <div class="mt-2">
                                        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-2 bg-blue-500 rounded-full similarity-bar" style="width:0%" data-target="{{ $pct }}"></div>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                            <span>Similarity</span>
                                            <span class="inline-flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded {{ $badgeColor }}">{{ number_format($pct, 2) }}%</span>
                                                <i data-feather="chevron-right" class="w-4 h-4 text-gray-400"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-14">
                        <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <i data-feather="search" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <h4 class="text-lg font-semibold mb-1">No similar titles found</h4>
                        <p class="text-gray-600 mb-4">Try a broader or alternate phrasing, or remove uncommon acronyms.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    <!-- Small helpers -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.feather) { feather.replace(); }

            const input = document.getElementById('queryInput');

            // Keyboard shortcut: focus with "/"
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement !== input) {
                    e.preventDefault();
                    input.focus();
                }
            });

            // Animate similarity bars
            document.querySelectorAll('.similarity-bar').forEach(bar => {
                const target = Number(bar.dataset.target || 0);
                let cur = 0;
                const step = () => {
                    cur += Math.max(1, (target - cur) * 0.12);
                    if (cur >= target) { cur = target; }
                    bar.style.width = cur + '%';
                    if (cur < target) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            });

      
        });
    </script>
</x-userlayout>
