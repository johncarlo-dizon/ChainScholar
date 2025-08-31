<x-userlayout>
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold text-white">All Research Papers</h2>
                <p class="text-white/90 mt-1 text-sm">Manage all uploaded research papers</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <!-- Search and Filters -->
        <div class="mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search papers...">
                </div>

                <!-- User Filter -->
                <div>
                    <label for="user" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user" id="user"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department" id="department"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Program Filter -->
                <div>
                    <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select name="program" id="program"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                {{ $program }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="year" id="year"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('research-papers.admin-index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Papers Table -->
     <!-- Papers Table -->
<div class="overflow-x-auto border border-gray-200 rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yr</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($papers as $paper)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ Str::limit($paper->title, 50) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ Str::limit($paper->authors, 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> 
                                       {{ Str::limit($paper->program, 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $paper->year }}
                    </td>
           
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3 flex items-center">
    <!-- View PDF -->
    <a href="{{ Storage::url($paper->file_path) }}" target="_blank"
       class="text-indigo-600 hover:text-indigo-900" title="View PDF">
        <i data-feather="eye" class="w-5 h-5"></i>
    </a>

    <!-- Details -->
    <button type="button"
            class="text-sky-600 hover:text-sky-800 btn-details"
            title="Details"
            data-title="{{ e($paper->title) }}"
            data-authors="{{ e($paper->authors) }}"
            data-department="{{ e($paper->department) }}"
            data-program="{{ e($paper->program) }}"
            data-year="{{ e($paper->year) }}"
            data-user-name="{{ e(optional($paper->user)->name) }}"
            data-user-email="{{ e(optional($paper->user)->email) }}"
            data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
            data-file-url="{{ Storage::url($paper->file_path) }}"
            data-abstract="{{ e($paper->abstract ?? '') }}">
        <i data-feather="info" class="w-5 h-5"></i>
    </button>

    <!-- Delete -->
    <form action="{{ route('research-papers.destroy', $paper->id) }}" method="POST" class="inline-block"
          onsubmit="return confirm('Are you sure you want to delete this research paper?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
            <i data-feather="trash-2" class="w-5 h-5"></i>
        </button>
    </form>
</td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No research papers found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


        <!-- Pagination -->
        <div class="mt-6">
            {{ $papers->links() }}
        </div>
    </div>

 

    <!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 backdrop-blur-sm bg-transparent" data-close-modal></div>

    <div class="relative mx-auto my-8 w-full max-w-2xl bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Research Paper Details</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-modal>&times;</button>
        </div>

        <div class="mt-4 space-y-4 text-sm">
            <div>
                <p class="text-gray-500">Title</p>
                <p id="m-title" class="font-medium text-gray-900"></p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500">Authors</p>
                    <p id="m-authors" class="text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500">Department</p>
                    <p id="m-department" class="text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500">Program</p>
                    <p id="m-program" class="text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500">Year</p>
                    <p id="m-year" class="text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500">Uploaded</p>
                    <p id="m-uploaded" class="text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500">User</p>
                    <p id="m-user" class="text-gray-900"></p>
                </div>
            </div>

            <div id="m-abstract-wrap" class="hidden">
                <p class="text-gray-500">Abstract</p>
                <p id="m-abstract" class="text-gray-900 whitespace-pre-line"></p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a id="m-file-url" href="#" target="_blank"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                Open PDF
            </a>
            <button type="button" class="rounded-lg border px-4 py-2 hover:bg-gray-50" data-close-modal>Close</button>
        </div>
    </div>
</div>


<script>
(function () {
    const modal = document.getElementById('detailsModal');
    const closeEls = modal.querySelectorAll('[data-close-modal]');

    const setText = (selector, value, fallback = '—') => {
        const el = document.querySelector(selector);
        if (!el) return;
        el.textContent = (value && String(value).trim() !== '') ? value : fallback;
    };

    const openModalFromBtn = (btn) => {
        setText('#m-title', btn.dataset.title);
        setText('#m-authors', btn.dataset.authors);
        setText('#m-department', btn.dataset.department);
        setText('#m-program', btn.dataset.program);
        setText('#m-year', btn.dataset.year);
        setText('#m-uploaded', btn.dataset.uploaded);
        setText('#m-user', [btn.dataset.userName, btn.dataset.userEmail].filter(Boolean).join(' ') || '—');

        const abstract = btn.dataset.abstract || '';
        const abstractWrap = document.getElementById('m-abstract-wrap');
        const abstractEl = document.getElementById('m-abstract');
        if (abstract.trim()) {
            abstractEl.textContent = abstract;
            abstractWrap.classList.remove('hidden');
        } else {
            abstractEl.textContent = '';
            abstractWrap.classList.add('hidden');
        }

        const fileUrl = btn.dataset.fileUrl || '#';
        const link = document.getElementById('m-file-url');
        link.setAttribute('href', fileUrl);

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // Delegate clicks for any .btn-details (works with pagination too)
    document.addEventListener('click', (e) => {
        const detailsBtn = e.target.closest('.btn-details');
        if (detailsBtn) {
            e.preventDefault();
            openModalFromBtn(detailsBtn);
        }

        if (e.target.hasAttribute('data-close-modal') || e.target === modal.querySelector('.absolute.inset-0')) {
            closeModal();
        }
    });

    // ESC to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // Also close when clicking the dark overlay
    modal.querySelector('.absolute.inset-0').addEventListener('click', closeModal);
})();
</script>


</x-userlayout>