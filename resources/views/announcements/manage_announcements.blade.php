<x-userlayout>
 
        <!-- Page Header -->
        <div class="bg-blue-600 rounded-lg shadow p-6 mb-6">
            <h1 class="text-3xl font-semibold text-white">Manage Announcements</h1>
        </div>

        <!-- Create Button -->
        <div class="mb-6 flex justify-end">
            <a href="{{ route('announcements.create') }}"
               class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                + Create New Announcement
            </a>
        </div>

        <!-- Announcements Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="p-3 text-left">Title</th>
                            <th class="p-3 text-left">Body</th>
                            <th class="p-3 text-left">Event Date</th>
                            <th class="p-3 text-left">Posted By</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-900">{{ $announcement->title }}</td>
                                <td class="p-3 text-gray-600 truncate max-w-xs">
                                    {{ Str::limit($announcement->body, 50) }}
                                </td>
                                <td class="p-3 text-gray-700">
                                    {{ $announcement->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="p-3 text-gray-700">
                                    {{ $announcement->author->name ?? 'Unknown' }}
                                </td>
                                <td class="p-3 flex space-x-3">
                                    <a href="{{ route('announcements.edit', $announcement) }}"
                                       class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>

                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500">No announcements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
 
</x-userlayout>
