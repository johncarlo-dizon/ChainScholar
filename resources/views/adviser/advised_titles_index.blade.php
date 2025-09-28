<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-1 text-white">Advised Titles</h2>
        <p class="text-blue-100">Titles where you are the primary adviser.</p>
    </div>

    <div class="container mx-auto px-4 py-6">
        <form method="GET" class="mb-4">
            <div class="flex gap-2">
                <input name="q" value="{{ request('q') }}"
                       placeholder="Search by title or student name"
                         class="w-full md:w-1/2 border border-gray-300 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-1 focus:ring-blue-400" />
                     
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Search</button>
            </div>
        </form>

        @if($titles->isEmpty())
            <div class="bg-white rounded-xl shadow p-8 text-center text-gray-600">
                You are not advising any titles yet.
            </div>
        @else
          <div class="space-y-3">
    @foreach($titles as $t)
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 hover:shadow-md transition p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $t->title }}
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                        Student: <span class="font-medium">{{ $t->owner->name }}</span>
                    </div>
                    {{-- Authors under the Student line --}}
@if(filled($t->authors))
  @php
    // Split comma-separated authors nicely into chips
    $authorsList = collect(preg_split('/\s*,\s*/', (string)$t->authors, -1, PREG_SPLIT_NO_EMPTY));
  @endphp

  <div class="mt-1 text-xs text-gray-500">
    Authors:
  </div>
  <div class="mt-0.5 flex flex-wrap gap-1.5">
    @foreach($authorsList as $a)
      <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px]">
        {{ $a }}
      </span>
    @endforeach
  </div>
@else
  <div class="mt-1 text-xs text-gray-400">
    Authors: —
  </div>
@endif

           <div class="mt-1 text-xs text-gray-500 flex gap-2 flex-wrap items-center">
    @if($t->adviser_assigned_at)
        <span>Assigned: {{ $t->adviser_assigned_at->format('M d, Y h:ia') }}</span>
    @endif

    @php
        $statusChipClass = match($t->status) {
            'awaiting_admin' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
            'submitted', 'in_progress', 'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        };

        $finalDoc = $t->finalDocument;
        $int = $finalDoc?->plagiarism_internal;
        $ext = $finalDoc?->plagiarism_external;

        $badge = function($v){
            if ($v === null) return 'bg-gray-100 text-gray-600';
            if ($v < 20)     return 'bg-green-100 text-green-800';
            if ($v < 40)     return 'bg-yellow-100 text-yellow-800';
            return            'bg-red-100 text-red-800';
        };
    @endphp

    @if($t->status)
        <span class="px-2 py-0.5 rounded-full {{ $statusChipClass }}">
            {{ $t->status }}
        </span>
    @endif

    {{-- If submitted (or a final doc exists), show similarity + final_document_id --}}
    @if($t->status === 'submitted' || $finalDoc)
        <span class="inline-flex items-center gap-1">
            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge($int) }}">
                Int: {{ is_null($int) ? '—' : number_format($int, 2).'%' }}
            </span>
            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge($ext) }}">
                Ext: {{ is_null($ext) ? '—' : number_format($ext, 2).'%' }}
            </span>
        </span>

        @if($finalDoc)
            <span class="text-gray-500">    
                @if($finalDoc->chapter)
                    <span class="text-gray-400"> {{ $finalDoc->chapter }}</span>
                @endif
            </span>
            {{-- quick link to the final doc if you have a route --}}
            <a href="{{ route('documents.view', ['id' => $finalDoc->id]) }}"
               class="text-indigo-600 hover:underline ml-1 font-medium text-sm">Open</a>
        @endif
    @endif
</div>

                </div>

             @php
    $lockedForAdviser = ($t->status === 'awaiting_admin');
@endphp
<div class="shrink-0 text-right">
    @if(!$lockedForAdviser)
        <a href="{{ route('adviser.advised.show', $t) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
            View chapters
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    @else
        <span
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-300 text-gray-600 text-sm cursor-not-allowed opacity-70 select-none"
            aria-disabled="true"
            title="Waiting for admin approval">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
            </svg>
            View chapters
        </span>
        <div class="text-xs text-gray-500 mt-1">
            Waiting for admin approval.
        </div>
    @endif
</div>

            </div>
        </div>
    @endforeach
</div>


            <div class="mt-4">
                {{ $titles->links() }}
            </div>
        @endif
    </div>
</x-userlayout>
