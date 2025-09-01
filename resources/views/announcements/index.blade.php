<x-userlayout>
   
        <!-- Page Header -->
        <div class="bg-blue-600 rounded-lg shadow p-6 mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-white">Announcements</h1>

            @can('isAdmin')
                <a href="{{ route('announcements.create') }}"
                   class="bg-white text-blue-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
                    + New Announcement
                </a>
            @endcan
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg mb-6 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Announcements List -->
        <div class="space-y-6">
            @forelse($announcements as $announcement)
                <div class="bg-white rounded-xl shadow p-6">
                    <!-- Title -->
                    <h2 class="text-xl font-semibold text-gray-900">{{ $announcement->title }}</h2>

                    <!-- Body -->
                    <p class="text-gray-700 mt-3">{{ $announcement->body }}</p>

                    <!-- Event Date -->
                    @if($announcement->event_date)
                        <p class="mt-3 text-sm text-gray-600 flex items-center">
                            📅 <span class="ml-1">
                                {{ \Carbon\Carbon::parse($announcement->event_date)->format('F d, Y') }}
                            </span>
                        </p>
                    @endif

                    <!-- Meta -->
                    <p class="text-xs text-gray-500 mt-3">
                        Posted by <span class="font-medium">{{ $announcement->author->name }}</span>
                        on {{ $announcement->created_at->format('M d, Y h:i A') }}
                    </p>

                    <!-- Admin Actions -->
                   @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="flex gap-3 mt-4 hidden">
                            <a href="{{ route('announcements.edit', $announcement) }}"
                               class="bg-yellow-500 text-white px-4 py-1.5 rounded-lg shadow hover:bg-yellow-600 transition">
                                Edit
                            </a>

                            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-600 text-white px-4 py-1.5 rounded-lg shadow hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">
                    No announcements available.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
   
</x-userlayout>
