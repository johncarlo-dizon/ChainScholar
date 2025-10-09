{{-- resources/views/adviser/profile.blade.php --}}
<x-userlayout>
     


    <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Adviser Profile</h2>
    </div>
   
  </div>
</div>

    <div class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 bg-white mt-5 sm:mt-7 shadow rounded-xl">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3">
                <div class="font-semibold mb-1">Please fix the following:</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="adviserProfileForm" method="POST" action="{{ route('adviser.profile.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- BASIC INFO --}}
            <section class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department / Unit</label>
                        <input type="text" name="department" value="{{ old('department', $profile->department ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" autocomplete="organization">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Field of Expertise</label>
                        <input type="text" name="field_of_expertise" value="{{ old('field_of_expertise', $profile->field_of_expertise ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
                    </div>
                </div>
            </section>

            {{-- EDUCATION --}}
            <section class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Educational Background (Highest)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Degree</label>
                        <input type="text" name="highest_degree" value="{{ old('highest_degree', $profile->highest_degree ?? '') }}"
                               placeholder="e.g., MS, PhD"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">School</label>
                        <input type="text" name="degree_school" value="{{ old('degree_school', $profile->degree_school ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <input type="number" name="degree_year" value="{{ old('degree_year', $profile->degree_year ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" min="1900" max="{{ date('Y')+1 }}">
                    </div>
                </div>
            </section>

            {{-- EXPERIENCE --}}
            <section class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Advisory Experience</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Years of Advising</label>
                        <input type="number" name="advisory_years" value="{{ old('advisory_years', $profile->advisory_years ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" min="0" max="200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Projects Handled</label>
                        <input type="number" name="projects_handled" value="{{ old('projects_handled', $profile->projects_handled ?? '') }}"
                               class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400" min="0" max="5000">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes / Short Bio</label>
                    <textarea name="notes" rows="4"
                              class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400">{{ old('notes', $profile->notes ?? '') }}</textarea>
                </div>
            </section>

            {{-- ACHIEVEMENTS REPEATER (vanilla JS) --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Achievements / Awards (major recognitions only)</h3>
                    <div class="flex items-center gap-2">
                        <span id="achievementsCount" class="text-xs text-gray-500">Total: 0</span>
                        <button type="button" id="addAchievementBtn" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + Add Achievement
                        </button>
                    </div>
                </div>

                <div id="achievementsEmpty" class="text-sm text-gray-500 border border-dashed border-gray-300 rounded-md p-3 hidden">
                    No achievements added yet. Click “Add Achievement”.
                </div>

                <div id="achievementsList" class="space-y-3">
                    {{-- Server-render existing achievements as rows so they’re visible even if JS fails --}}
                    @php
                        $existingAch = old('achievements', ($profile?->achievements ?? collect())->map(function($a){
                            return [
                                'title' => $a->title,
                                'issuer'=> $a->issuer,
                                'year'  => $a->year,
                                'description' => $a->description
                            ];
                        })->toArray());
                    @endphp

                    @forelse($existingAch as $i => $a)
                        <div class="border  border-gray-300 rounded-lg p-3 achievement" data-index="{{ $i }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600">Title *</label>
                                    <input type="text" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                           name="achievements[{{ $i }}][title]" value="{{ $a['title'] ?? '' }}" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Issuer</label>
                                    <input type="text" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                           name="achievements[{{ $i }}][issuer]" value="{{ $a['issuer'] ?? '' }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Year</label>
                                    <input type="number" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                           name="achievements[{{ $i }}][year]" value="{{ $a['year'] ?? '' }}" min="1900" max="{{ date('Y')+1 }}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-600">Description</label>
                                <textarea class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2" rows="2"
                                          name="achievements[{{ $i }}][description]">{{ $a['description'] ?? '' }}</textarea>
                            </div>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="text-[11px] text-gray-400">#{{ $i + 1 }}</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-xs text-gray-600 hover:text-gray-800 move-up">↑</button>
                                    <button type="button" class="text-xs text-gray-600 hover:text-gray-800 move-down">↓</button>
                                    <button type="button" class="text-red-600 text-sm remove">Remove</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- no rows; JS will show the "empty" panel --}}
                    @endforelse
                </div>

                {{-- Template for new rows --}}
                <template id="achievementRowTpl">
                    <div class="border  border-gray-300 rounded-lg p-3 achievement" data-index="__INDEX__">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600">Title *</label>
                                <input type="text" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                       name="achievements[__INDEX__][title]" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Issuer</label>
                                <input type="text" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                       name="achievements[__INDEX__][issuer]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Year</label>
                                <input type="number" class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2"
                                       name="achievements[__INDEX__][year]" min="1900" max="{{ date('Y')+1 }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600">Description</label>
                            <textarea class="mt-1 w-full border  border-gray-300 rounded-md px-3 py-2" rows="2"
                                      name="achievements[__INDEX__][description]"></textarea>
                        </div>
                        <div class="mt-3 flex justify-between items-center">
                            <span class="text-[11px] text-gray-400">#<span class="row-number"></span></span>
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-xs text-gray-600 hover:text-gray-800 move-up">↑</button>
                                <button type="button" class="text-xs text-gray-600 hover:text-gray-800 move-down">↓</button>
                                <button type="button" class="text-red-600 text-sm remove">Remove</button>
                            </div>
                        </div>
                    </div>
                </template>
            </section>

            {{-- RESEARCH INTERESTS TAGS (vanilla JS) --}}
            <section class="space-y-2">
                <h3 class="text-lg font-semibold text-gray-800">Research Interests / Specialization</h3>
                <p class="text-sm text-gray-500">Add interests as tags. Press <kbd>Enter</kbd> to add (comma also supported).</p>

                <div class="border  border-gray-300 rounded-lg p-3">
                    <div id="tagsWrap" class="flex flex-wrap gap-2 mb-2">
                        @php
                            $existingTags = old('research_interests', ($profile?->researchInterests ?? collect())->pluck('name')->values()->toArray());
                        @endphp
                        @foreach ($existingTags as $i => $tag)
                            <span class="tag chip inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full text-xs" data-value="{{ $tag }}">
                                <span>{{ $tag }}</span>
                                <button type="button" class="ml-1 remove-tag" aria-label="Remove">✕</button>
                                <input type="hidden" name="research_interests[]" value="{{ $tag }}">
                            </span>
                        @endforeach
                    </div>

                    <input id="tagInput" type="text" placeholder="Type an interest and press Enter"
                           class="w-full border  border-gray-300 rounded-md px-3 py-2">

                    @if(($suggestions ?? collect())->isNotEmpty())
                        <div class="mt-2">
                            <label class="block text-xs text-gray-500 mb-1">Suggestions</label>
                            <div id="tagSuggestions" class="flex flex-wrap gap-2">
                                @foreach(($suggestions ?? collect()) as $s)
                                    <button type="button"
                                            class="suggestion text-xs px-2 py-1 rounded-full border  border-gray-300 hover:bg-gray-50"
                                            data-value="{{ $s }}">{{ $s }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg shadow hover:bg-blue-700">
                    Save Profile
                </button>
            </div>
        </form>
    </div>

    {{-- Vanilla JS for repeater + tags --}}
    <script>
        (function(){
            // ===== Achievements Repeater =====
            const list = document.getElementById('achievementsList');
            const tpl = document.getElementById('achievementRowTpl');
            const btnAdd = document.getElementById('addAchievementBtn');
            const emptyState = document.getElementById('achievementsEmpty');
            const countLabel = document.getElementById('achievementsCount');

            function updateUI() {
                const rows = list.querySelectorAll('.achievement');
                // Toggle empty state
                emptyState.classList.toggle('hidden', rows.length > 0);
                // Update count and numbers + data-index + input names
                countLabel.textContent = 'Total: ' + rows.length;
                rows.forEach((row, i) => {
                    row.dataset.index = i;
                    // update visible number
                    const num = row.querySelector('.row-number');
                    if (num) num.textContent = (i + 1);
                    // update all name attributes inside
                    row.querySelectorAll('input[name], textarea[name]').forEach((input) => {
                        input.name = input.name
                            .replace(/achievements\[\d+\]/g, `achievements[${i}]`)
                            .replace(/__INDEX__/g, i);
                    });
                });
            }

            function addRow(focusTitle = true) {
                const html = tpl.innerHTML.replace(/__INDEX__/g, list.children.length);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const node = wrapper.firstElementChild;
                list.appendChild(node);
                updateUI();
                if (focusTitle) {
                    const title = node.querySelector('input[name$="[title]"]');
                    if (title) title.focus();
                }
            }

            function moveRow(row, dir) {
                const rows = Array.from(list.querySelectorAll('.achievement'));
                const i = rows.indexOf(row);
                const target = i + dir;
                if (target < 0 || target >= rows.length) return;
                if (dir < 0) {
                    list.insertBefore(row, rows[target]);
                } else {
                    list.insertBefore(rows[target], row);
                }
                updateUI();
            }

            function removeRow(row) {
                row.remove();
                updateUI();
            }

            // Delegate row buttons
            list.addEventListener('click', (e) => {
                const row = e.target.closest('.achievement');
                if (!row) return;

                if (e.target.classList.contains('remove')) {
                    removeRow(row);
                }
                if (e.target.classList.contains('move-up')) {
                    moveRow(row, -1);
                }
                if (e.target.classList.contains('move-down')) {
                    moveRow(row, +1);
                }
            });

            // Add button
            btnAdd?.addEventListener('click', () => addRow(true));

            // Initialize empty state/count on load
            updateUI();

            // ===== Tags (Interests) =====
            const tagWrap = document.getElementById('tagsWrap');
            const tagInput = document.getElementById('tagInput');
            const suggWrap = document.getElementById('tagSuggestions');

            function hasTag(val) {
                val = (val || '').trim();
                if (!val) return false;
                return !!tagWrap.querySelector(`.tag[data-value="${CSS.escape(val)}"]`);
            }

            function addTag(val) {
                val = (val || '').trim();
                if (!val || hasTag(val)) return;
                const span = document.createElement('span');
                span.className = 'tag chip inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full text-xs';
                span.dataset.value = val;
                span.innerHTML = `
                    <span>${val}</span>
                    <button type="button" class="ml-1 remove-tag" aria-label="Remove">✕</button>
                    <input type="hidden" name="research_interests[]" value="${val}">
                `;
                tagWrap.appendChild(span);
            }

            function removeTag(el) {
                const chip = el.closest('.tag');
                if (chip) chip.remove();
            }

            tagWrap.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-tag')) {
                    removeTag(e.target);
                }
            });

            tagInput?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const raw = tagInput.value;
                    tagInput.value = '';
                    raw.split(',').map(s => s.trim()).filter(Boolean).forEach(addTag);
                }
            });

            tagInput?.addEventListener('blur', () => {
                const raw = tagInput.value;
                tagInput.value = '';
                raw.split(',').map(s => s.trim()).filter(Boolean).forEach(addTag);
            });

            suggWrap?.addEventListener('click', (e) => {
                if (e.target.classList.contains('suggestion')) {
                    const v = e.target.getAttribute('data-value');
                    addTag(v);
                }
            });
        })();
    </script>
</x-userlayout>
