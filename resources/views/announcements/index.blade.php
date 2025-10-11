<x-userlayout>
    <!-- Page Header -->
    <x-header.bar
      title="Announcements"
      subtitle="View important updates and platform notifications"
      :unread-count="$unreadCount ?? 0"
      :notifications="$notifications ?? collect()"
      :user="Auth::user()"
    />

    <!-- Success Alert -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg mb-6 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters and Actions Row -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Tier Filters -->
            @php
                $activeTier = request('tier');
                $tierChips = [
                    'ALL'       => ['label' => 'All Tiers', 'q' => null],
                    'URGENT'    => ['label' => 'Urgent',    'q' => 'URGENT'],
                    'IMPORTANT' => ['label' => 'Important', 'q' => 'IMPORTANT'],
                    'GENERAL'   => ['label' => 'General',   'q' => 'GENERAL'],
                ];
            @endphp
            @foreach($tierChips as $key => $opt)
                @php
                    $isActive = ($activeTier === $opt['q']) || ($key === 'ALL' && $activeTier === null);
                    $url = $opt['q'] ? request()->fullUrlWithQuery(['tier' => $opt['q']]) : route('announcements.index');
                    $base = 'px-3 py-1.5 rounded-full text-sm border';
                    $active = 'bg-blue-600 text-white border-blue-600';
                    $idle = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                @endphp
                <a href="{{ $url }}" class="{{ $base }} {{ $isActive ? $active : $idle }}">{{ $opt['label'] }}</a>
            @endforeach

            <!-- Audience Filters (Admin only) -->
            @can('isAdmin')
                @php
                    $activeAud = request('audience');
                    $audChips = [
                        'ALL'     => ['label' => 'Everyone', 'q' => 'ALL'],
                        'STUDENT' => ['label' => 'Students', 'q' => 'STUDENT'],
                        'ADVISER' => ['label' => 'Advisers', 'q' => 'ADVISER'],
                        'ADMIN'   => ['label' => 'Admins',   'q' => 'ADMIN'],
                    ];
                @endphp
                @foreach($audChips as $key => $opt)
                    @php
                        $isActive = ($activeAud === $opt['q']) || ($key === 'ALL' && $activeAud === null);
                        $url = $opt['q'] ? request()->fullUrlWithQuery(['audience' => $opt['q']]) : route('announcements.index');
                        $base = 'px-3 py-1.5 rounded-full text-sm border';
                        $active = 'bg-gray-900 text-white border-gray-900';
                        $idle = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                    @endphp
                    <a href="{{ $url }}" class="{{ $base }} {{ $isActive ? $active : $idle }}">
                        {{ $opt['label'] }}
                    </a>
                @endforeach
            @endcan
        </div>

            <!-- Actions -->
        @can('isAdmin')

            <a
              href="{{ route('announcements.manage') }}"
             class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition"
            >
              Manage
            </a>
   
        @endcan

    </div>

    <!-- List -->
    <div class="space-y-6">
        @forelse($announcements as $announcement)
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $announcement->title }}</h2>

                    <div class="flex gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $announcement->tier_badge_classes }}">
                            @if($announcement->tier === \App\Models\Announcement::TIER_URGENT) ⚠️ @endif
                            {{ $announcement->tier_label }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $announcement->audience_badge_classes }}">
                            {{ $announcement->audience_label }}
                        </span>
                    </div>
                </div>

                <p class="text-gray-700 mt-3">{{ $announcement->body }}</p>

                @if($announcement->event_date)
                    <p class="mt-3 text-sm text-gray-600 flex items-center">
                        📅 <span class="ml-1">
                            {{ \Carbon\Carbon::parse($announcement->event_date)->format('F d, Y') }}
                        </span>
                    </p>
                @endif

                <p class="text-xs text-gray-500 mt-3">
                    Posted by <span class="font-medium">{{ $announcement->author->name }}</span>
                    on {{ $announcement->created_at->format('M d, Y h:i A') }}
                </p>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="flex gap-3 mt-4">
                        <a href="{{ route('announcements.edit', $announcement) }}"
                           class="bg-yellow-500 text-white px-4 py-1.5 rounded-lg shadow hover:bg-yellow-600 transition">
                            Edit
                        </a>
                      <button
                    type="button"
                    class="bg-red-600 text-white px-4 py-1.5 rounded-lg shadow hover:bg-red-700 transition js-open-delete"
                    data-action="{{ route('announcements.destroy', $announcement) }}"
                    data-name="{{ $announcement->title }}"
                >
                    Delete
                </button>

                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">
                No announcements available.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $announcements->links() }}
    </div>

    <!-- Global Delete Modal -->
<div id="delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div id="modal-overlay" class="absolute inset-0 backdrop-blur-sm bg-black/10"></div>
  <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
    <p id="delete-prompt" class="text-lg font-semibold mb-5 text-gray-900">
      Are you sure you want to delete this item?
    </p>
    <div class="flex justify-end space-x-3">
      <button id="cancel-btn"
        class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
        Cancel
      </button>
      <form id="delete-form" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit"
          class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition">
          Delete
        </button>
      </form>
    </div>
  </div>
</div>



<script>
(function () {
  const modal      = document.getElementById('delete-modal');
  const overlay    = document.getElementById('modal-overlay');
  const cancelBtn  = document.getElementById('cancel-btn');
  const deleteForm = document.getElementById('delete-form');
  const promptEl   = document.getElementById('delete-prompt');

  function ensureInBody() {
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  }

  function showModalWith(action, name) {
    if (!modal || !deleteForm) return;
    ensureInBody();
    deleteForm.action = action;
    if (promptEl) {
      promptEl.textContent = name
        ? `Are you sure you want to delete “${name}”?`
        : 'Are you sure you want to delete this item?';
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.zIndex = '9999';
    cancelBtn && cancelBtn.focus();
  }

  function hideModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function bindHandlers() {
    // Open modal from any Delete button
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.js-open-delete');
      if (!btn) return;
      e.preventDefault();
      const action = btn.dataset.action;
      const name   = btn.dataset.name || '';
      if (!action) return;
      showModalWith(action, name);
    });

    overlay && overlay.addEventListener('click', hideModal);
    cancelBtn && cancelBtn.addEventListener('click', hideModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideModal(); });
    deleteForm && deleteForm.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindHandlers);
  } else {
    bindHandlers();
  }
  document.addEventListener('turbo:load', bindHandlers);
  document.addEventListener('livewire:load', bindHandlers);
})();
</script>

</x-userlayout>