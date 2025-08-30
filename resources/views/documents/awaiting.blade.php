<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-1 text-white">Awaiting Adviser</h2>
        <p class="text-blue-100">These titles passed verification and are waiting for an adviser. You can accept incoming requests or change your chosen adviser.</p>
    </div>

    <div class="container mx-auto px-4 py-6 space-y-4">
        @if($titles->isEmpty())
            <div class="bg-white rounded-xl shadow p-8 text-center text-gray-600">
                <div class="mx-auto mb-2 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                No titles are currently awaiting an adviser.
            </div>
        @endif

        @foreach($titles as $t)
            @php
                $studentPending = $t->adviserRequests->firstWhere('requested_by', 'student');
                $incomingFromAdvisers = $t->adviserRequests->where('requested_by', 'adviser')->values();
            @endphp

            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 hover:shadow-md transition">
                <!-- Header -->
                <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold tracking-tight text-gray-900">
                                {{ $t->title }}
                            </h3>
                            <div class="mt-1 text-sm text-gray-500 flex flex-wrap gap-x-2 gap-y-1">
                                <span>Submitted: {{ optional($t->submitted_at)->format('M d, Y h:ia') ?? '—' }}</span>
                                @if($t->verified_at)
                                    <span class="hidden md:inline">•</span>
                                    <span>Verified: {{ $t->verified_at->format('M d, Y h:ia') }}</span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <!-- Status chip -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                    Status: {{ $t->status }}
                                </span>

                                <!-- Current student request chip -->
                                @if($studentPending)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        Pending with <span class="font-medium ml-1">{{ $studentPending->adviser->name }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-50 text-gray-600 ring-1 ring-gray-200">
                                        No student-initiated request
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Change adviser -->
                        <div class="md:w-96">
                            <label for="adviser_id_{{ $t->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                Choose a different adviser
                            </label>
                            <div class="flex gap-2">
                                <select id="adviser_id_{{ $t->id }}"
                                        class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <option value="">— Select an adviser —</option>
                                    @foreach($advisers as $a)
                                        <option value="{{ $a->id }}"
                                            @selected(optional($studentPending)->adviser_id === $a->id)>
                                            {{ $a->name }}
                                            @if($a->department) — {{ $a->department }} @endif
                                            @if($a->specialization) ({{ $a->specialization }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <form id="changeForm-{{ $t->id }}" method="POST" action="{{ route('titles.adviser.change', $t) }}">
                                    @csrf
                                    <input type="hidden" name="adviser_id" id="adviser_id_hidden_{{ $t->id }}">
                                    <button type="submit" class="whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                                       
                                        Change
                                    </button>
                                </form>
                                <form id="withdrawForm-{{ $t->id }}" method="POST" action="{{ route('titles.adviser.cancel', $t) }}">
                                    @csrf
                                    <button type="button"
                                            class="whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200"
                                            data-confirm
                                            data-title="Withdraw Request"
                                            data-message="Withdraw your current adviser request for “{{ $t->title }}”?"
                                            data-form="withdrawForm-{{ $t->id }}">
                                      
                                        Withdraw
                                    </button>
                                </form>
                            </div>
                            @error('adviser_id')
                                <div class="text-sm text-rose-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        
                    </div>
                </div>

                <!-- Body: Incoming adviser requests -->
                <div class="px-5 py-4">
                    <div class="font-medium text-gray-800 mb-2">Requests from advisers</div>

                    @if($incomingFromAdvisers->isEmpty())
                        <div class="text-sm text-gray-500 italic">No adviser-initiated requests yet.</div>
                    @else
                        <ul class="space-y-3">
                            @foreach($incomingFromAdvisers as $req)
                                <li class="rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <!-- Avatar initial -->
                                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold">
                                                {{ strtoupper(substr($req->adviser->name,0,1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $req->adviser->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @if($req->adviser->department) {{ $req->adviser->department }} @endif
                                                    @if($req->adviser->specialization) — {{ $req->adviser->specialization }} @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <form id="acceptForm-{{ $t->id }}-{{ $req->id }}" method="POST" action="{{ route('titles.incoming.accept', [$t, $req]) }}">
                                                @csrf
                                                <button type="button"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700"
                                                        data-confirm
                                                        data-title="Accept Adviser"
                                                        data-message="Accept {{ $req->adviser->name }} as adviser for “{{ $t->title }}”? This will close other pending requests."
                                                        data-form="acceptForm-{{ $t->id }}-{{ $req->id }}">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                                        <path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Accept
                                                </button>
                                            </form>

                                            <form id="declineForm-{{ $t->id }}-{{ $req->id }}" method="POST" action="{{ route('titles.incoming.decline', [$t, $req]) }}">
                                                @csrf
                                                <button type="button"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700"
                                                        data-confirm
                                                        data-title="Decline Adviser"
                                                        data-message="Decline {{ $req->adviser->name }}’s request for “{{ $t->title }}”?"
                                                        data-form="declineForm-{{ $t->id }}-{{ $req->id }}">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                    </svg>
                                                    Decline
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <script>
                // Keep hidden input synced with visible select (per card)
                (function(){
                    const select = document.getElementById('adviser_id_{{ $t->id }}');
                    const hidden = document.getElementById('adviser_id_hidden_{{ $t->id }}');
                    if (select && hidden) {
                        hidden.value = select.value;
                        select.addEventListener('change', () => hidden.value = select.value);
                    }
                })();
            </script>
        @endforeach

        <div>
            {{ $titles->links() }}
        </div>
    </div>

    {{-- Reusable Confirm Modal --}}
    <div id="confirmModal" class="fixed inset-0 hidden items-center justify-center z-50">
        <div id="confirmOverlay" class="absolute inset-0 backdrop-blur-sm bg-black/20"></div>

        <div class="relative bg-white rounded-xl shadow-xl p-6 max-w-lg w-full mx-4 z-10">
            <div class="flex items-start gap-3">
                <div class="shrink-0 w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="confirmTitle" class="text-lg font-semibold text-gray-900">Confirm</h3>
                    <p id="confirmMessage" class="mt-1 text-sm text-gray-600">Are you sure?</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="confirmCancelBtn"
                        class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="button" id="confirmOkBtn"
                        class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    {{-- Feather (optional if not globally loaded) --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        // Modal logic
        (function () {
            const modal   = document.getElementById('confirmModal');
            const overlay = document.getElementById('confirmOverlay');
            const titleEl = document.getElementById('confirmTitle');
            const msgEl   = document.getElementById('confirmMessage');
            const okBtn   = document.getElementById('confirmOkBtn');
            const cancel  = document.getElementById('confirmCancelBtn');
            let targetFormId = null;

            function openModal({ title, message, formId }) {
                titleEl.textContent = title || 'Confirm';
                msgEl.textContent   = message || 'Are you sure?';
                targetFormId        = formId || null;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                if (window.feather) feather.replace();
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                titleEl.textContent = 'Confirm';
                msgEl.textContent   = 'Are you sure?';
                targetFormId        = null;
            }

            // Attach to any button with data-confirm
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-confirm]');
                if (!btn) return;

                const title   = btn.getAttribute('data-title') || 'Confirm';
                const message = btn.getAttribute('data-message') || 'Are you sure?';
                const formId  = btn.getAttribute('data-form');

                if (!formId) return; // nothing to submit
                openModal({ title, message, formId });
            });

            overlay.addEventListener('click', closeModal);
            cancel.addEventListener('click', closeModal);
            okBtn.addEventListener('click', () => {
                if (targetFormId) {
                    const form = document.getElementById(targetFormId);
                    if (form) form.submit();
                }
                closeModal();
            });
        })();
    </script>
</x-userlayout>
