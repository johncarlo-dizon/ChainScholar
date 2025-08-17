<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">Adviser Dashboard</h2>
    </div>

    <div class="container mx-auto px-4 py-6 grid md:grid-cols-3 gap-4">
        <a href="{{ route('adviser.requests.pending') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">Pending Requests</div>
            <div class="text-3xl font-bold">{{ $pendingCount }}</div>
        </a>

      <a href="{{ route('adviser.advised.index') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">My Advised Titles</div>
            <div class="text-3xl font-bold">{{ $myAdvisedCount }}</div>
         </a>


        

        <a href="{{ route('adviser.titles.browse') }}" class="block bg-white p-5 rounded-xl shadow hover:shadow-md">
            <div class="text-gray-500 text-sm">Browse Open Titles</div>
            <div class="text-xl mt-1">Request to Advise →</div>
        </a>
    </div>

    <div class="container mx-auto px-4 pb-10 grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-semibold mb-3">Incoming Pending Requests</h3>
            <ul class="space-y-3">
                @forelse($incomingRequests as $r)
                    <li class="border rounded-lg p-3">
                        <div class="font-medium">{{ $r->title->title }}</div>
                        <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>
                        <div class="mt-2 flex gap-2">
                            <form method="POST" action="{{ route('adviser.requests.accept', $r) }}">
                                @csrf
                                <button class="px-3 py-1 bg-green-600 text-white rounded">Accept</button>
                            </form>
                            <form method="POST" action="{{ route('adviser.requests.decline', $r) }}">
                                @csrf
                                <button class="px-3 py-1 bg-red-600 text-white rounded">Decline</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="text-gray-500">No pending requests.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-semibold mb-3">My Pending Sent Requests</h3>
            <ul class="space-y-3">
                @forelse($myPendingSent as $r)
                    <li class="border rounded-lg p-3">
                        <div class="font-medium">{{ $r->title->title }}</div>
                        <div class="text-sm text-gray-500">Owner: {{ $r->title->owner->name }}</div>
                        <span class="inline-block text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800">Pending</span>
                    </li>
                @empty
                    <li class="text-gray-500">You have not requested any titles yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-userlayout>
