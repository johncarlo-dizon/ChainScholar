<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">Browse Open Titles</h2>
       
    </div>



    <div class="container mx-auto px-4 py-6 space-y-4">
     <form method="GET" class="mt-2">
            <input name="search" value="{{ request('search') }}" placeholder="Search title, keywords, category..."
                 class="w-full border border-gray-300 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-1 focus:ring-blue-400" />
</form>
        @forelse($titles as $t)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $t->title }}</h3>
                        <div class="text-sm text-gray-500">Owner: {{ $t->owner->name }}</div>
                        @if($t->keywords)
                            <div class="text-xs text-gray-500 mt-1">Keywords: {{ $t->keywords }}</div>
                        @endif
                        <div class="text-xs mt-1">
                            <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $t->status }}</span>
                            @if($t->verified_at)
                                <span class="ml-2 text-gray-500">Verified: {{ $t->verified_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('adviser.titles.request', $t) }}">
                        @csrf
                        <button class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            Request to Advise
                        </button>
                    </form>
                </div>
                @if($t->abstract)
                    <p class="text-sm text-gray-700 mt-4 line-clamp-3">{{ $t->abstract }}</p>
                @endif
            </div>
        @empty
            <div class="text-gray-500">No open titles found.</div>
        @endforelse

        <div>{{ $titles->links() }}</div>
    </div>
</x-userlayout>
