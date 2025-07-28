<x-userlayout>
    <div class="bg-green-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-2 text-white">✅ Approved Titles</h2>
        <p class="text-sm text-green-100">List of final documents that have been approved.</p>
    </div>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No approved titles</h4>
                    <p class="text-gray-600">All approved documents will show up here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $title->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $title->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $title->approved_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('admin.documents.view', $title->finaldocument_id) }}"
                                           class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-userlayout>
