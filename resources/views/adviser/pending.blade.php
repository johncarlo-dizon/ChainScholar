<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">Pending Requests</h2>
    </div>

    <div class="container mx-auto px-4 py-6 space-y-4">
        @forelse($requests as $r)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $r->title->title }}</h3>
                        <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">Requested by: {{ ucfirst($r->requested_by) }}</div>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('adviser.requests.accept', $r) }}">
                            @csrf
                            <button class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('adviser.requests.decline', $r) }}">
                            @csrf
                            <button class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Decline</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-gray-500">No pending requests at the moment.</div>
        @endforelse

        <div>{{ $requests->links() }}</div>
    </div>
</x-userlayout>
