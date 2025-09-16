{{-- resources/views/admin/titles/awaiting_admin.blade.php --}}
{{-- If you use a different admin layout, swap <x-adminlayout> for your layout --}}
<x-userlayout>
 
        <div class="bg-blue-600 rounded-lg shadow p-5">
            <h2 class="text-2xl font-semibold text-white">Awaiting Admin Approval</h2>
            <p class="text-blue-100 text-sm mt-1">Titles with an accepted adviser, pending final admin approval.</p>
        </div>

        {{-- Filters / Search --}}
        <form method="GET" action="{{ route('admin.titles.awaiting') }}" class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Search by title or student name...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per page</label>
                <select name="per_page"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @foreach([5,10,15,25,50] as $n)
                        <option value="{{ $n }}" @selected(request('per_page', 10)==$n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                    Apply
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    @if($titles->total() > 0)
                        Showing <span class="font-semibold">{{ $titles->firstItem() }}</span>–
                        <span class="font-semibold">{{ $titles->lastItem() }}</span> of
                        <span class="font-semibold">{{ $titles->total() }}</span>
                    @else
                        No records found.
                    @endif
                </div>
                <span class="inline-flex items-center text-xs font-semibold px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                    Waiting for: Admin approval
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Adviser</th>
                            <th class="px-6 py-3">Assigned</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($titles as $t)
                            <tr>
                                <td class="px-6 py-4 align-top">
                                    <div class="font-semibold text-gray-900">{{ $t->title }}</div>
                                    @if($t->abstract)
                                        <div class="text-gray-500 text-xs mt-1 line-clamp-2">{{ Str::limit(strip_tags($t->abstract), 150) }}</div>
                                    @endif
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            awaiting_admin
                                        </span>
                                        @if($t->finalDocument)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                                has combined doc
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-gray-900 font-medium">{{ $t->owner->name ?? '—' }}</div>
                                    <div class="text-gray-500 text-xs">{{ $t->owner->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-gray-900 font-medium">{{ optional($t->adviser)->name ?? '—' }}</div>
                                    @if(optional($t->adviser)->department)
                                        <div class="text-gray-500 text-xs">{{ $t->adviser->department }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-gray-900">
                                        {{ $t->adviser_assigned_at ? $t->adviser_assigned_at->format('M d, Y') : '—' }}
                                    </div>
                                    @if($t->adviser_assigned_at)
                                        <div class="text-gray-500 text-xs">{{ $t->adviser_assigned_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-center justify-end gap-2">

                                        {{-- Approve --}}
                                        <form method="POST" action="{{ route('admin.titles.approve', $t) }}"
                                              onsubmit="return confirm('Approve this adviser assignment and unlock editing for the student?');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                Approve
                                            </button>
                                        </form>

                                        {{-- Return (with reason) --}}
                                        <details class="relative">
                                            <summary
                                                class="cursor-pointer inline-flex list-none items-center px-3 py-1.5 rounded-md bg-red-600 text-white text-xs font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                Return
                                            </summary>
                                            <div class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow p-3 z-10">
                                                <form method="POST" action="{{ route('admin.titles.return', $t) }}">
                                                    @csrf
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Reason (optional)
                                                    </label>
                                                    <textarea name="reason" rows="3"
                                                        class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"
                                                        placeholder="What needs to be revised?"></textarea>
                                                    <div class="mt-3 flex items-center justify-end gap-2">
                                                        <button type="button"
                                                            onclick="this.closest('details').removeAttribute('open')"
                                                            class="px-3 py-1.5 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                            class="px-3 py-1.5 text-xs rounded-md bg-red-600 text-white font-semibold hover:bg-red-700">
                                                            Send Back
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    Nothing to approve right now.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($titles->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $titles->links() }}
                </div>
            @endif
        </div>
 
</x-userlayout>


