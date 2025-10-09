<x-userlayout>
    <!-- Header -->
 <div
  class="rounded-2xl shadow-lg text-white mb-6"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">{{ $title->title }}</h2>

      <div class="mt-2 text-sm text-gray-300">
        Authors: <span class="font-semibold text-white">{{ $title->authors }}</span>
      </div>

      <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
        @if($title->status)
          <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-gray-800 ring-1 ring-emerald-200">
            {{ $title->status }}
          </span>
        @endif

        @if($title->adviser_assigned_at)
          <span class="text-gray-400">
            Assigned {{ $title->adviser_assigned_at->diffForHumans() }}
          </span>
        @endif
      </div>
    </div>

    <div class="md:text-right">
      <a
        href="{{ route('adviser.advised.index') }}"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20 transition"
      >
        ← Back to list
      </a>
    </div>
  </div>
</div>


    <!-- Chapters -->
    <div class="container mx-auto px-0 md:px-0 pt-2 pb-6">
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Chapters</h3>
                <p class="text-sm text-gray-500">All chapter documents submitted by the student.</p>
            </div>

            @if($chapters->isEmpty())
                <div class="p-6 text-gray-600">No chapters yet.</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($chapters as $doc)
                        <li class="px-6 py-4 flex items-start justify-between gap-4 hover:bg-gray-50">
                            <div>
                                <div class="font-medium text-gray-900">{{ $doc->chapter }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Updated {{ optional($doc->updated_at)->diffForHumans() ?? '—' }}
                                    @if(!is_null($doc->plagiarism_score))
                                        • Similarity: {{ $doc->plagiarism_score }}%
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if(Route::has('documents.show'))
                               <a href="{{ route('adviser.advised.chapter.show', [$title, $doc]) }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
    View
 
</a>

                                @else
                                    <span class="text-xs text-gray-500">Viewer route not configured</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-userlayout>
