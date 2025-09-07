<x-userlayout>
 
        <!-- Page Header -->
        <div class="bg-blue-600 rounded-lg shadow p-6 mb-6">
            <h1 class="text-3xl font-semibold text-white">Manage Announcements</h1>
        </div>

        <!-- Create Button -->
        <div class="mb-6 flex justify-end">
            <a href="{{ route('announcements.create') }}"
               class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                + Create New Announcement
            </a>
        </div>

        <!-- Announcements Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="p-3 text-left">Title</th>
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
                                <td class="p-3 text-gray-600 truncate max-w-xs">
                                    {{ Str::limit($announcement->body, 50) }}
                                </td>
                                <td class="p-3 text-gray-700">
                                    {{ $announcement->created_at->format('M d, Y h:i A') }}
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
                                <td colspan="5" class="p-6 text-center text-gray-500">No announcements found.</td>
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
 


 <!-- Delete Confirmation Modal (Announcements) -->
<div id="annDeleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Blur overlay -->
    <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-ann-delete></div>

    <!-- Modal card -->
    <div class="relative mx-auto my-8 w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
        </div>

        <div class="mt-4 space-y-3">
            <p class="text-sm text-gray-600">You’re about to delete this announcement:</p>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p id="ann-del-title" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                This action is irreversible. The announcement will be permanently removed.
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-ann-delete>
                Cancel
            </button>

            <!-- Hidden form submitted by JS -->
            <form id="annDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button id="annConfirmDeleteBtn" type="submit"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 disabled:opacity-50">
                    <svg id="annConfirmDeleteSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                        <path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" opacity=".75"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    // Elements
    const modal      = document.getElementById('annDeleteModal');
    const form       = document.getElementById('annDeleteForm');
    const titleEl    = document.getElementById('ann-del-title');
    const btnConfirm = document.getElementById('annConfirmDeleteBtn');
    const spinner    = document.getElementById('annConfirmDeleteSpinner');

    const openModal = (action, title) => {
        form.setAttribute('action', action);
        titleEl.textContent = title || 'Untitled announcement';
        modal.classList.remove('hidden');
        document.documentElement.classList.add('overflow-hidden'); // lock scroll
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.documentElement.classList.remove('overflow-hidden');
        btnConfirm.disabled = false;
        spinner.classList.add('hidden');
    };

    // Delegate: open on any .btn-delete-ann
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete-ann');
        if (btn) {
            e.preventDefault();
            openModal(btn.dataset.action, btn.dataset.title);
            return;
        }
        if (e.target.hasAttribute('data-close-ann-delete')) {
            closeModal();
        }
    });

    // ESC to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // Submit UX: prevent double submit
    form.addEventListener('submit', () => {
        btnConfirm.disabled = true;
        spinner.classList.remove('hidden');
    });
})();
</script>

</x-userlayout>
