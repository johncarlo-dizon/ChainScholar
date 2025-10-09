<x-userlayout>
    <div class="container mx-auto">
        <!-- Header / Hero -->
<div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div class="flex items-center gap-6 flex-1">
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">
        Research Library
      </h2>
    </div>

    <div class="hidden md:block text-right shrink-0">
      <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-white/10 text-white">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10m-8 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2h-2M7 5H5a2 2 0 00-2 2v12a2 2 0 002 2h2"/>
        </svg>
        Tip: Press <kbd class="px-1.5 py-0.5 bg-white/20 rounded">/</kbd> to search
      </span>
    </div>
  </div>
</div>



     <!-- Search Bar -->
      <form method="GET" action="{{ route('dashboard.search') }}" id="search-form" class="flex-1 max-w-2xl mb-4">
      @foreach(request()->except(['query','page']) as $k => $v)
  <input type="hidden" name="{{ $k }}" value="{{ $v }}">
@endforeach

        <div class="flex items-center rounded-lg border border-blue-200 bg-white shadow-sm focus-within:ring-2 focus-within:ring-blue-400">
          <input
            type="text"
            name="query"
            id="queryInput"
            value="{{ old('query', $query ?? '') }}"
            autocomplete="off"
            class="w-full px-4 py-2 rounded-l-lg focus:outline-none text-gray-800 placeholder-gray-400 text-base"
            placeholder="Search titles, abstracts, authors…"

            aria-label="Search research title"
          />
          <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-r-lg flex items-center justify-center">
            <i data-feather="search" class="w-5 h-5"></i>
          </button>
        </div>
      </form>





        <!-- Search -->
 

    <!-- Results -->
@if(isset($results))
<div class="">
  <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

    <!-- LEFT FILTER RAIL -->
    <!-- LEFT FILTER RAIL (Google Scholar style: no Apply button) -->
<aside class="md:col-span-3">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Articles</h4>

    {{-- keep the query in URL --}}
@php
  $q    = request('query', $query ?? '');
  $base = url()->current();
  $qs   = request()->query();

  // Merge, then remove null/empty so "Any time" actually clears params
  $link = function(array $merge) use ($base, $qs, $q) {
  unset($qs['page']); // reset to page 1 when changing time filters
  $merged = array_merge($qs, $merge, ['query' => $q]);
  $clean  = array_filter($merged, fn($v) => !is_null($v) && $v !== '');
  return $base.'?'.http_build_query($clean);
};


  // Use controller-sanitized filters for UI state
  $yearFrom = (int) (($filters['yearFrom'] ?? 0) ?: 0);
  $yearTo   = (int) (($filters['yearTo']   ?? 0) ?: 0);
  $hasYear  = ($yearFrom || $yearTo);
@endphp



    {{-- TIME --}}
    <div class="mb-5">
      <div class="text-xs font-semibold text-gray-500 mb-2">Any time</div>
      <ul class="space-y-1 text-sm">
        <li>
          <a href="{{ $link(['year_from'=>null,'year_to'=>null]) }}"
          class="{{ !$hasYear ? 'text-blue-700' : 'text-gray-700 hover:underline' }}"
>
            Any time
          </a>
        </li>
        <li>
          <a href="{{ $link(['year_from'=>now()->year, 'year_to'=>null]) }}"
           class="{{ ($yearFrom === now()->year && $yearTo === 0) ? 'text-blue-700' : 'text-gray-700 hover:underline' }}"
>

            Since {{ now()->year }}
          </a>
        </li>
        <li>
          <a href="{{ $link(['year_from'=>now()->subYears(3)->year, 'year_to'=>null]) }}"
   class="{{ ($yearFrom === now()->subYears(3)->year && $yearTo === 0) ? 'text-blue-700' : 'text-gray-700 hover:underline' }}"

>
            Since {{ now()->subYears(3)->year }}
          </a>
        </li>
      </ul>

      {{-- Custom year (Enter to submit, no button) --}}
  <form method="GET" action="{{ route('dashboard.search') }}" class="mt-2 flex items-center gap-2">
  @foreach(request()->except(['year_from','year_to','page']) as $k => $v)
    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
  @endforeach

  {{-- show controller-sanitized values --}}
  <input type="number" name="year_from" placeholder="From"
         value="{{ ($yearFrom ?? 0) && !in_array(($yearFrom ?? 0), [now()->year, now()->subYears(3)->year]) ? $yearFrom : '' }}"
         class="w-24 border border-gray-300 rounded px-2 py-1 text-xs"
         onchange="this.form.submit()"
         onkeydown="if(event.key==='Enter'){ this.form.submit(); }">

  <input type="number" name="year_to" placeholder="To"
         value="{{ $yearTo ?? '' }}"
         class="w-24 border border-gray-300 rounded px-2 py-1 text-xs"
         onchange="this.form.submit()"
         onkeydown="if(event.key==='Enter'){ this.form.submit(); }">
</form>

    </div>

    {{-- TYPE (auto submit on change) --}}
    <div class="mb-5">
      <div class="text-xs font-semibold text-gray-500 mb-2">Any type</div>
      <form method="GET" action="{{ route('dashboard.search') }}">
        @foreach(request()->except(['type']) as $k => $v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <select name="type" class="w-full border  border-gray-300 rounded px-2 py-1 text-sm"
                onchange="this.form.submit()">
          <option value="all"   {{ request('type','all')==='all'?'selected':'' }}>All results</option>
          <option value="paper" {{ request('type')==='paper'?'selected':'' }}>[PDF] only</option>
          <option value="title" {{ request('type')==='title'?'selected':'' }}>Titles only</option>
        </select>
      </form>
    </div>

    {{-- SORT (auto submit on change) --}}
    <div>
      <div class="text-xs font-semibold text-gray-500 mb-2">Sort by</div>
      <form method="GET" action="{{ route('dashboard.search') }}">
        @foreach(request()->except(['sort']) as $k => $v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <select name="sort" class="w-full border   border-gray-300 rounded px-2 py-1 text-sm"
                onchange="this.form.submit()">
          <option value="relevance" {{ request('sort','relevance')==='relevance'?'selected':'' }}>Relevance</option>
          <option value="date"      {{ request('sort')==='date'?'selected':'' }}>Date</option>
        </select>
      </form>
    </div>
  </div>
</aside>


    <!-- RESULTS LIST -->
    <main class="md:col-span-9">
      <div class="mb-3 text-sm text-gray-500">
        About <span class="font-medium">{{ $paginator?->total() ?? count($results) }}</span> results
        @if(!empty($query)) for “<span class="text-gray-700">{{ $query }}</span>” @endif
      </div>

      @forelse ($results as $result)
      @php
  $isPaper = ($result['type'] ?? 'title') === 'paper';

  // Prefer a server route that streams from Storage (works local & prod).
  $openUrl = $result['open_url']
      ?? (!empty($result['paper_id']) ? route('papers.view', $result['paper_id']) : null)
      ?? (!empty($result['id'])       ? route('papers.view', $result['id'])       : null)
      // fallback: absolute PDF links (e.g., external results)
      ?? ($result['file_url'] ?? null);

  // NEW: direct download route if available
  $downloadUrl = $isPaper
      ? (
          $result['download_url']
          ?? (!empty($result['paper_id']) ? route('papers.download', $result['paper_id']) : null)
          ?? (!empty($result['id'])       ? route('papers.download', $result['id'])       : null)
        )
      : null;

  $link   = $isPaper ? ($openUrl ?? 'javascript:void(0)') : route('dashboard.view', $result['id']);
  $target = $isPaper ? '_blank' : '_self';
@endphp


        <div class="group border-b border-gray-200/70 py-4">
          <div class="flex items-start justify-between gap-4">
            <a href="{{ $link }}" target="{{ $target }}" class="text-[#1a0dab] hover:underline text-lg leading-6 font-medium">
             {!! $result['title_html'] ?? e($result['title']) !!}

            </a>
           @if($isPaper)
  <div class="flex items-center gap-2">
    <a href="{{ $link }}" target="_blank"
       class="shrink-0 text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">[PDF]</a>
    @if($downloadUrl)
      <a href="{{ $downloadUrl }}"
         class="shrink-0 text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Download</a>
    @endif
  </div>
@else
  <a href="{{ $link }}" target="_blank"
     class="shrink-0 text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">View</a>
@endif

          </div>

          @if(!empty($result['authors']))
  <div class="mt-1 text-sm text-gray-600">{!! $result['authors_html'] ?? e($result['authors']) !!}</div>
@endif


  @if(!empty($result['abstract']))
  <div class="mt-1 text-sm text-gray-700 line-clamp-2">
    {!! $result['abstract_html'] ?? e($result['abstract']) !!}
  </div>
@endif




          <div class="mt-1 text-xs text-gray-500 flex items-center gap-3">
            @if(!empty($result['date'])) <span>{{ \Carbon\Carbon::parse($result['date'])->format('Y') }}</span>@endif
          </div>
        </div>
      @empty
        <div class="bg-white rounded-xl shadow p-8 text-center text-gray-600">
          <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <i data-feather="search" class="w-6 h-6 text-gray-400"></i>
          </div>
          <h4 class="text-lg font-semibold mb-1">No results</h4>
          <p class="text-gray-600">Try broader words or remove uncommon acronyms.</p>
        </div>
      @endforelse

      <!-- PAGINATION -->
      @if(isset($paginator))
        <div class="mt-6">
       {{ $paginator->onEachSide(1)->links() }}

        </div>
      @endif
    </main>

  </div>
</div>


@else
  <!-- Default Explore Content -->
  <div class="bg-white rounded-xl shadow p-10 text-center">
    <div class="mx-auto w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-4">
      <i data-feather="book-open" class="w-8 h-8 text-blue-500"></i>
    </div>
    <h3 class="text-2xl font-semibold text-gray-800 mb-2">Welcome to ChainScholar</h3>
    <p class="text-gray-600 mb-6">
      Start exploring research papers and titles by using the search bar above.  
      Or browse through featured categories below:
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-left">
      <div class="p-5 rounded-lg border border-gray-200 hover:shadow transition">
        <h4 class="text-lg font-semibold text-blue-600 mb-1">Latest Papers</h4>
        <p class="text-gray-500 text-sm">View recently uploaded research papers by students and advisers.</p>
      </div>

      <div class="p-5 rounded-lg border border-gray-200 hover:shadow transition">
        <h4 class="text-lg font-semibold text-blue-600 mb-1">Top Titles</h4>
        <p class="text-gray-500 text-sm">Explore trending research titles and see what’s popular right now.</p>
      </div>

      <div class="p-5 rounded-lg border border-gray-200 hover:shadow transition">
        <h4 class="text-lg font-semibold text-blue-600 mb-1">By Field</h4>
        <p class="text-gray-500 text-sm">Browse works grouped under IT, Engineering, Education, and more.</p>
      </div>
    </div>
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


      
        });
    </script>
</x-userlayout>
