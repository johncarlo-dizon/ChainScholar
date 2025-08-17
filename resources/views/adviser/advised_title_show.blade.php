<x-userlayout>
    <!-- Header -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $title->title }}</h2>
                <div class="mt-2 text-sm text-gray-600">
                    Student: <span class="font-medium">{{ $title->owner->name }}</span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    @if($title->status)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                            {{ $title->status }}
                        </span>
                    @endif
                    @if($title->adviser_assigned_at)
                        <span class="text-gray-500">Assigned {{ $title->adviser_assigned_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="md:text-right">
                <a href="{{ route('adviser.advised.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">
                    ← Back to list
                </a>
            </div>
        </div>
    </div>

    <!-- Chapters -->
    <div class="container mx-auto px-0 md:px-0 py-6">
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
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
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
