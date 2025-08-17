<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-1 text-white">My Advised Titles</h2>
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
                    <a href="{{ route('adviser.advised.show', $t) }}"
                       class="block bg-white rounded-xl shadow-sm ring-1 ring-gray-200 hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">{{ $t->title }}</div>
                                <div class="mt-1 text-sm text-gray-600">
                                    Student: <span class="font-medium">{{ $t->owner->name }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 flex gap-2 flex-wrap">
                                    @if($t->adviser_assigned_at)
                                        <span>Assigned: {{ $t->adviser_assigned_at->format('M d, Y h:ia') }}</span>
                                    @endif
                                    @if($t->status)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                            {{ $t->status }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm">
                                    View chapters
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $titles->links() }}
            </div>
        @endif
    </div>
</x-userlayout>
