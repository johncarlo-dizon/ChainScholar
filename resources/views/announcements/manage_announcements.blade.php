<x-userlayout>
    <!-- Page Header -->
   
    <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Manage Announcements</h2>
    </div>
   
  </div>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal"
     class="fixed inset-0 z-[9999] hidden"
     role="dialog" aria-modal="true" aria-labelledby="del-modal-title" aria-describedby="del-modal-desc">
  <!-- Backdrop -->
  <div id="deleteModalBackdrop" class="fixed inset-0 bg-black/40 transition-opacity"></div>

  <!-- Panel -->
  <div class="fixed inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
      <div class="px-6 pt-6 pb-4">
        <h3 id="del-modal-title" class="text-lg font-semibold text-gray-900">Delete announcement?</h3>
        <p id="del-modal-desc" class="mt-2 text-sm text-gray-600">
          This will permanently delete: <span id="delModalTitle" class="font-medium text-gray-900"></span>
        </p>
      </div>

      <div class="px-6 pb-6 flex items-center justify-end gap-3">
        <button type="button"
                id="btnCancelDelete"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
          Cancel
        </button>

        <form id="deleteAnnForm" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit"
                  id="btnConfirmDelete"
                  class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
            Yes, delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  // In case the layout or Livewire/Turbo replaces content, delegate from document:
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-ann');
    if (!btn) return;

    // Open modal
    const modal     = document.getElementById('deleteModal');
    const backdrop  = document.getElementById('deleteModalBackdrop');
    const titleSpan = document.getElementById('delModalTitle');
    const form      = document.getElementById('deleteAnnForm');

    form.setAttribute('action', btn.dataset.action);
    titleSpan.textContent = `“${btn.dataset.title || 'this announcement'}”`;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('btnConfirmDelete').focus(), 0);
  });

  // Close handlers
  function closeModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', function (e) {
    if (e.target.id === 'btnCancelDelete' || e.target.id === 'deleteModalBackdrop') {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !document.getElementById('deleteModal').classList.contains('hidden')) {
      closeModal();
    }
  });

  // Optional: guard against double submit
  document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'deleteAnnForm') {
      const confirmBtn = document.getElementById('btnConfirmDelete');
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Deleting...';
    }
  });
})();
</script>

    
</x-userlayout>
