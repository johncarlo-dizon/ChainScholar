<x-userlayout>
    <!-- Header -->
<x-header.bar
  title="{{ $title->title }}"
  subtitle="Authors: {{ $title->authors }}"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

<div class="pb-3">
 <a
        href="{{ route('adviser.advised.index') }}"
        class="inline-flex items-center gap-1.5 px-3 py-2 text-gray-500  rounded-lg bg-white   text-sm hover:bg-white/20 transition shadow"
      >
        ← Back to list
      </a>
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
