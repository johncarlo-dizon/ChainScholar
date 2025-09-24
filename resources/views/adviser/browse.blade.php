<x-userlayout>
  <div class="bg-blue-600 rounded-lg shadow p-6">
    <h2 class="text-3xl font-semibold mb-2 text-white">Browse Open Titles</h2>
    <p class="text-white/80 text-sm">Filter by status, assignment, and search keywords.</p>
  </div>

  <div class="container mx-auto px-4 py-6 space-y-4">
    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 grid gap-3 md:grid-cols-12 items-end">
      <!-- Search -->
      <div class="md:col-span-5">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input id="search" name="search" value="{{ request('search') }}"
               placeholder="Search title, keywords, category..."
               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" />
      </div>

      <!-- Status -->
      <div class="md:col-span-3">
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          @php $s = request('status','all'); @endphp
          <option value="all" {{ $s==='all'?'selected':'' }}>All</option>
          <option value="verified" {{ $s==='verified'?'selected':'' }}>Verified (open)</option>
          <option value="awaiting_adviser" {{ $s==='awaiting_adviser'?'selected':'' }}>Awaiting adviser</option>
          <option value="awaiting_admin" {{ $s==='awaiting_admin'?'selected':'' }}>Waiting for admin</option>
        </select>
      </div>

      <!-- Assignment -->
      <div class="md:col-span-2">
        <label for="assign" class="block text-sm font-medium text-gray-700 mb-1">Assignment</label>
        @php $a = request('assign','all'); @endphp
        <select id="assign" name="assign"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          <option value="all" {{ $a==='all'?'selected':'' }}>All</option>
          <option value="unassigned" {{ $a==='unassigned'?'selected':'' }}>No adviser yet</option>
          <option value="assigned" {{ $a==='assigned'?'selected':'' }}>Already assigned</option>
        </select>
      </div>

      <!-- Sort -->
      <div class="md:col-span-2">
        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort by</label>
        @php $o = request('sort','verified'); @endphp
        <select id="sort" name="sort"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
          <option value="verified" {{ $o==='verified'?'selected':'' }}>Recently verified</option>
          <option value="title" {{ $o==='title'?'selected':'' }}>Title (A–Z)</option>
          <option value="owner" {{ $o==='owner'?'selected':'' }}>Owner (A–Z)</option>
        </select>
      </div>

      <!-- Actions -->
      <div class="md:col-span-12 flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Apply</button>
        <a href="{{ route('adviser.titles.browse') }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Reset</a>
      </div>
    </form>

    @forelse($titles as $t)
      <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <h3 class="font-semibold text-lg truncate">{{ $t->title }}</h3>
            <div class="text-sm text-gray-500">Owner: {{ $t->owner->name }}</div>

            @if($t->keywords)
              <div class="text-xs text-gray-500 mt-1 truncate">
                <span class="font-medium text-gray-600">Keywords:</span> {{ $t->keywords }}
              </div>
            @endif

           {{-- Chips --}}
@php
  $requestable    = is_null($t->primary_adviser_id) && in_array($t->status, ['verified','awaiting_adviser']);
  $sentPending    = !empty($t->has_my_pending_request);
  $sentAccepted   = !empty($t->has_my_accepted_request);
  $assignedNow    = !is_null($t->primary_adviser_id);
  $assignedToMe   = $assignedNow && ((int)$t->primary_adviser_id === (int)auth()->id());
@endphp

<div class="text-xs mt-2 flex flex-wrap gap-2">
  <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $t->status }}</span>

  @if($t->verified_at)
    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">
      Verified: {{ $t->verified_at->format('M d, Y') }}
    </span>
  @endif

  {{-- show "Assigned to: ..." only if assigned to someone ELSE --}}
  @if($assignedNow && $t->status === 'awaiting_admin' && !$assignedToMe)
    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">
      Assigned to: {{ optional($t->primaryAdviser)->name ?? 'another adviser' }}
    </span>
  @endif

  @if($sentPending)
    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">Request sent</span>
  @endif

  @if($sentAccepted)
    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800">You were accepted</span>
  @endif
</div>

          </div>

          {{-- Right-side action --}}
          <div class="shrink-0">
            @if($requestable && !$sentPending && !$sentAccepted)
              <form method="POST" action="{{ route('adviser.titles.request', $t) }}">
                @csrf
                <button class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                  Request to Advise
                </button>
              </form>
            @elseif($requestable && ($sentPending || $sentAccepted))
              <button class="px-4 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled
                      title="{{ $sentAccepted ? 'Already accepted for you.' : 'You already sent a request.' }}">
                {{ $sentAccepted ? 'Already Accepted' : 'Request Sent' }}
              </button>
            @else
              <button class="px-4 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled
                    title="{{ $assignedNow ? ($assignedToMe ? 'You are the assigned adviser.' : 'Already assigned to another adviser.') : 'Not requestable right now.' }}">
            {{ $assignedNow ? ($assignedToMe ? 'Already Accepted' : 'Assigned') : 'Not Requestable' }}
            </button>

            @endif
          </div>
        </div>

        @if($t->abstract)
          <p class="text-sm text-gray-700 mt-4 line-clamp-3">{{ $t->abstract }}</p>
        @endif
      </div>
    @empty
      <div class="text-gray-500">No titles found with the current filters.</div>
    @endforelse

    <div>{{ $titles->links() }}</div>
  </div>
</x-userlayout>
