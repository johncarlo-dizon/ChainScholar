<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-2 text-white">📄 Submitted Titles</h2>
        <p class="text-sm text-blue-100">All final documents students have submitted. No approval required.</p>
    </div>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔍 Search + Per-page -->
        <form method="GET" class="flex flex-wrap items-center gap-3 mb-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="w-full sm:w-72 border border-gray-300 rounded px-4 py-2 text-sm"
                placeholder="Search by title or student…"
            />
            <select name="per_page" class="border border-gray-300 rounded px-2 py-2 text-sm" onchange="this.form.submit()">
                @foreach([5,10,20,50,100] as $pp)
                    <option value="{{ $pp }}" {{ (int)request('per_page', 5) === $pp ? 'selected' : '' }}>
                        {{ $pp }}/page
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                Search
            </button>
        </form>

        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $titles->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $titles->lastItem() ?? 0 }}</span>
                of <span class="font-medium">{{ $titles->total() }}</span>
            </p>
            @if(request('search'))
                <a href="{{ url()->current() }}" class="text-sm text-gray-600 hover:underline">Clear search</a>
            @endif
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No submitted titles</h4>
                    <p class="text-gray-600">When students submit their final document, it will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $t)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $t->title }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ optional($t->owner)->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ optional($t->submitted_at)->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($t->finalDocument)
                                            <a href="{{ route('admin.titles.submitted.view', $t->finalDocument) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded hover:bg-indigo-50">
                                                View
                                            </a>
                                        @else
                                            <span class="text-gray-500">No final document attached</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 🔽 Pagination -->
                <div class="px-6 py-4">
                    {{ $titles->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-userlayout>
