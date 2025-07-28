<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl text-white font-semibold mb-4">📄 My Submitted Documents</h2>
        <p class="text-white text-sm">View and track the status of your final document submissions here.</p>
    </div>

    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($submittedDocuments->isEmpty())
            <div class="text-center py-12 bg-white rounded-lg shadow">
                <h3 class="text-xl font-semibold text-gray-700">No documents submitted yet.</h3>
                <p class="text-gray-500 mt-2">Submit a final document to see it listed here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg shadow overflow-hidden">
                    <thead class="bg-gray-100 text-gray-600 text-sm">
                        <tr>
                            <th class="px-6 py-3 text-left">📌 Title</th>
                            <th class="px-6 py-3 text-left">📚 Type</th>
                            <th class="px-6 py-3 text-left">📅 Submitted At</th>
                            <th class="px-6 py-3 text-left">📊 Status</th>
                            <th class="px-6 py-3 text-center">🔎 Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        @foreach($submittedDocuments as $doc)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $doc->title }}</td>
                                <td class="px-6 py-4">{{ $doc->research_type }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($doc->submitted_at)->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'text-yellow-600',
                                            'approved' => 'text-green-600',
                                            'rejected' => 'text-red-600',
                                        ];
                                        $color = $statusColors[$doc->status] ?? 'text-gray-600';
                                    @endphp
                                    <span class="font-semibold {{ $color }}">{{ ucfirst($doc->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="{{ route('submitted_documents.show', $doc->id) }}"
                                       class="inline-block px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        View
                                    </a>
                                    @if($doc->document_path)
                                        <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank"
                                           class="inline-block px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                                            Download
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-userlayout>
