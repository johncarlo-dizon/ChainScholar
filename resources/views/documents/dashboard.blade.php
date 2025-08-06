<x-userlayout>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-blue-600 rounded-lg shadow p-6 mb-8">
            <h2 class="text-5xl text-center text-white font-semibold my-3">ChainScholar</h2>

          
        </div>


        
          <form method="POST" action="{{ route('dashboard.search') }}" class="flex justify-center items-center w-full my-7">
    @csrf
    <div class="flex border border-blue-500 rounded overflow-hidden w-full max-w-3xl">
        <input 
            type="text" 
            name="query" 
            value="{{ old('query', $query ?? '') }}" 
            class="w-full px-4 py-3 text-lg focus:outline-none"
            placeholder="Search research title..."
        >
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-4 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M13.293 14.707a1 1 0 001.414-1.414l-3.387-3.387A5 5 0 1014 10a5 5 0 00-1.293 4.707l3.387 3.387zM10 12a4 4 0 110-8 4 4 0 010 8z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</form>


        @if(isset($results))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Search Results ({{ count($results) }})</h3>
                @forelse ($results as $result)
                    <a href="{{ route('dashboard.view', $result['id']) }}"
                        class="block mb-3 text-blue-600 hover:underline text-lg">
                        🔍 {{ $result['title'] }}
                        @if($loop->first)
                            <span class="ml-2 text-xs text-red-500 font-bold">🔥 Highest Match</span>
                        @endif
                        <span class="text-sm text-gray-500">({{ number_format($result['similarity'] * 100, 2) }}%)</span>
                    </a>
                @empty
                    <p class="text-gray-600">No similar titles found.</p>
                @endforelse
            </div>
        @endif
    </div>
</x-userlayout>
