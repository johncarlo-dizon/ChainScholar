<x-userlayout>
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold text-white">My Research Papers</h2>
                <p class="text-white/90 mt-1 text-sm">View all your uploaded research papers</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <!-- Search and Filters -->
        <div class="mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search papers...">
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
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('research-papers.student-index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Papers Table -->
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Authors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($papers as $paper)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($paper->title, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ Str::limit($paper->authors, 30) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $paper->program }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $paper->year }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                                <!-- View PDF -->
                                <a href="{{ Storage::url($paper->file_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900" title="View PDF">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </a>

                                <!-- Details -->
                                <button type="button" class="text-sky-600 hover:text-sky-800 btn-details"
                                    title="Details"
                                    data-title="{{ e($paper->title) }}"
                                    data-authors="{{ e($paper->authors) }}"
                                    data-department="{{ e($paper->department) }}"
                                    data-program="{{ e($paper->program) }}"
                                    data-year="{{ e($paper->year) }}"
                                    data-uploaded="{{ $paper->created_at->format('M d, Y') }}"
                                    data-file-url="{{ Storage::url($paper->file_path) }}"
                                    data-abstract="{{ e($paper->abstract ?? '') }}">
                                    <i data-lucide="info" class="w-5 h-5"></i>
                                </button>

                                <!-- Delete -->
                                <form action="{{ route('research-papers.destroy', $paper->id) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this research paper?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
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

    <!-- Modal (same design as admin) -->
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

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        const modal = document.getElementById("detailsModal");
        const closeBtns = modal.querySelectorAll("[data-close-modal]");

        document.querySelectorAll(".btn-details").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("m-title").textContent = btn.dataset.title;
                document.getElementById("m-authors").textContent = btn.dataset.authors;
                document.getElementById("m-department").textContent = btn.dataset.department;
                document.getElementById("m-program").textContent = btn.dataset.program;
                document.getElementById("m-year").textContent = btn.dataset.year;
                document.getElementById("m-uploaded").textContent = btn.dataset.uploaded;
                document.getElementById("m-file-url").href = btn.dataset.fileUrl;

                if (btn.dataset.abstract) {
                    document.getElementById("m-abstract").textContent = btn.dataset.abstract;
                    document.getElementById("m-abstract-wrap").classList.remove("hidden");
                } else {
                    document.getElementById("m-abstract-wrap").classList.add("hidden");
                }

                modal.classList.remove("hidden");
            });
        });

        closeBtns.forEach(btn => btn.addEventListener("click", () => modal.classList.add("hidden")));
    </script>
</x-userlayout>
