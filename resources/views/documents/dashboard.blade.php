<x-userlayout>
    <div class="container mx-auto px-4 py-10">
        <!-- Header -->
        <div class="bg-gradient-to-r bg-white shadow-sm rounded-2xl shadow-lg p-8 text-center mb-10">
            <h2 class="text-5xl font-bold text-blue-600 tracking-wide">ChainScholar</h2>
            <p class="text-blue-400 mt-2 text-lg ">Explore and compare research titles with ease</p>
        </div>

        <!-- Search Bar -->
        <form method="POST" action="{{ route('dashboard.search') }}" class="flex justify-center items-center mb-10">
            @csrf
            <div class="flex w-full max-w-3xl border border-blue-500 focus-within:ring-2 focus-within:ring-blue-400 rounded-xl overflow-hidden shadow-sm transition-all">
                <input 
                    type="text" 
                    name="query" 
                    value="{{ old('query', $query ?? '') }}" 
                    class="w-full px-5 py-3 text-lg focus:outline-none bg-white placeholder-gray-400"
                    placeholder="Search research title..."
                >
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 transition px-6 flex items-center justify-center">
                   <i data-feather="search" class="w-6 h-6 mr-3 text-white"></i>
                </button>
            </div>
        </form>

        <!-- Search Results -->
        @if(isset($results))
            <div class="bg-white rounded-2xl shadow-lg p-8 max-w-6xl mx-auto transition">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">🔍 Search Results ({{ count($results) }})</h3>

                @forelse ($results as $result)
                    <a href="{{ route('dashboard.view', $result['id']) }}"
                        class="block mb-4 p-4 rounded-lg hover:bg-blue-50 transition border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-700 font-medium text-lg">
                                {{ $result['title'] }}
                                @if($loop->first)
                                    <span class="ml-2 text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-semibold">🔥 Highest Match</span>
                                @endif
                            </span>
                            <span class="text-sm text-gray-500">{{ number_format($result['similarity'] * 100, 2) }}%</span>
                        </div>
                    </a>
                @empty
                    <p class="text-center text-gray-500">No similar titles found.</p>
                @endforelse
            </div>
        @endif
    </div>
</x-userlayout>
