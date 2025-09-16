<x-userlayout>
    <!-- Page Header -->
    <div class="bg-blue-600 rounded-lg shadow p-6 mb-6">
        <h1 class="text-3xl font-semibold text-white">Manage Announcements</h1>
    </div>

    <!-- Top Bar: Create + Filters -->
    <div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('announcements.create') }}"
           class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            + Create New Announcement
        </a>

        @php
            $activeTier = request('tier');
            $activeAud  = request('audience');
            $tierChips = [
                'ALL'       => ['label' => 'All Tiers', 'q' => null],
                'URGENT'    => ['label' => 'Urgent',    'q' => 'URGENT'],
                'IMPORTANT' => ['label' => 'Important', 'q' => 'IMPORTANT'],
                'GENERAL'   => ['label' => 'General',   'q' => 'GENERAL'],
            ];
            $audChips = [
                'ALL'     => ['label' => 'Everyone', 'q' => 'ALL'],
                'STUDENT' => ['label' => 'Students', 'q' => 'STUDENT'],
                'ADVISER' => ['label' => 'Advisers', 'q' => 'ADVISER'],
                'ADMIN'   => ['label' => 'Admins',   'q' => 'ADMIN'],
            ];
        @endphp
        <div class="flex gap-2">
            @foreach($tierChips as $key => $opt)
                @php
                    $isActive = ($activeTier === $opt['q']) || ($key === 'ALL' && $activeTier === null);
                    $url = $opt['q'] ? request()->fullUrlWithQuery(['tier' => $opt['q']]) : route('announcements.manage');
                    $base = 'px-3 py-1.5 rounded-full text-sm border';
                    $active = 'bg-blue-600 text-white border-blue-600';
                    $idle = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                @endphp
                <a href="{{ $url }}" class="{{ $base }} {{ $isActive ? $active : $idle }}">{{ $opt['label'] }}</a>
            @endforeach
        </div>
        <div class="flex gap-2">
            @foreach($audChips as $key => $opt)
                @php
                    $isActive = ($activeAud === $opt['q']) || ($key === 'ALL' && $activeAud === null);
                    $url = $opt['q'] ? request()->fullUrlWithQuery(['audience' => $opt['q']]) : route('announcements.manage');
                    $base = 'px-3 py-1.5 rounded-full text-sm border';
                    $active = 'bg-gray-900 text-white border-gray-900';
                    $idle = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                @endphp
                <a href="{{ $url }}" class="{{ $base }} {{ $isActive ? $active : $idle }}">{{ $opt['label'] }}</a>
            @endforeach
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Tier</th>
                        <th class="p-3 text-left">Audience</th>
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
                            <td class="p-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $announcement->tier_badge_classes }}">
                                    {{ $announcement->tier_label }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $announcement->audience_badge_classes }}">
                                    {{ $announcement->audience_label }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-600 truncate max-w-xs">
                                {{ Str::limit($announcement->body, 50) }}
                            </td>
                            <td class="p-3 text-gray-700">
                                @if($announcement->event_date)
                                    {{ \Carbon\Carbon::parse($announcement->event_date)->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3 text-gray-700">
                                {{ $announcement->author->name ?? 'Unknown' }}
                            </td>
                            <td class="p-3 flex space-x-3">
                                <a href="{{ route('announcements.edit', $announcement) }}"
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>

                                <button
                                    type="button"
                                    class="text-red-600 hover:text-red-800 font-medium btn-delete-ann"
                                    title="Delete"
                                    data-action="{{ route('announcements.destroy', $announcement) }}"
                                    data-title="{{ e(Str::limit($announcement->title, 100)) }}"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500">No announcements found.</td>
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

    {{-- Keep your existing delete modal + script --}}
</x-userlayout>
