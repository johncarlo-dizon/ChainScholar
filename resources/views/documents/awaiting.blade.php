<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-4">
        <h2 class="text-2xl font-semibold text-white">Awaiting Titles</h2>
        <p class="text-blue-100 text-sm">Manage adviser approvals and admin approvals in one view.</p>
    </div>

    @php
        $pageItems = $titles instanceof \Illuminate\Pagination\AbstractPaginator
            ? $titles->getCollection()
            : collect($titles);

        $awaitingAdviser = $pageItems->where('status', 'awaiting_adviser')->values();
        $awaitingAdmin   = $pageItems->where('status', 'awaiting_admin')->values();
    @endphp

    <div class="container mx-auto px-4 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- LEFT COLUMN: Waiting for Adviser --}}
            <section class="space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Waiting for Adviser</h3>
                    <span class="text-xs text-gray-500">{{ $awaitingAdviser->count() }} item(s)</span>
                </div>

                @if($awaitingAdviser->isEmpty())
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-sm text-gray-600 text-center">
                        No titles are currently awaiting an adviser.
                    </div>
                @else
                    @foreach($awaitingAdviser as $t)
                        @php
                            $studentPending = $t->adviserRequests->firstWhere('requested_by', 'student');
                            $incomingFromAdvisers = $t->adviserRequests->where('requested_by', 'adviser')->values();
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-700 text-sm md:text-base truncate">{{ $t->title }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200">awaiting_adviser</span>
                                        @if($studentPending)
                                            <span class="px-2 py-0.5 rounded-full text-[11px] bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
                                                Pending with <b>{{ $studentPending->adviser->name }}</b>
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] bg-gray-50 text-gray-600 ring-1 ring-gray-200">No student request</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Change / Withdraw (compact) --}}
                                <div class="w-48">
                                    <label for="adviser_id_{{ $t->id }}" class="sr-only">Choose adviser</label>
                                    <select id="adviser_id_{{ $t->id }}"
                                            class="w-full border-gray-300 rounded-md text-sm px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <option value="">— Select adviser —</option>
                                        @foreach($advisers as $a)
                                            <option value="{{ $a->id }}" @selected(optional($studentPending)->adviser_id === $a->id)>
                                                {{ $a->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 flex gap-1">
                                        <form id="changeForm-{{ $t->id }}" method="POST" action="{{ route('titles.adviser.change', $t) }}">
                                            @csrf
                                            <input type="hidden" name="adviser_id" id="adviser_id_hidden_{{ $t->id }}">
                                            <button type="submit" class="px-2.5 py-1 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-700">Change</button>
                                        </form>
                                        <form id="withdrawForm-{{ $t->id }}" method="POST" action="{{ route('titles.adviser.cancel', $t) }}">
                                            @csrf
                                            <button type="button"
                                                    class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs hover:bg-gray-200"
                                                    data-confirm
                                                    data-title="Withdraw Request"
                                                    data-message="Withdraw your current adviser request for “{{ $t->title }}”?"
                                                    data-form="withdrawForm-{{ $t->id }}">
                                                Withdraw
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Incoming adviser requests (compact list) --}}
                            <div class="mt-3">
                                @if($incomingFromAdvisers->isEmpty())
                                    <div class="text-xs text-gray-500">No adviser-initiated requests yet.</div>
                                @else
                                    <ul class="space-y-2">
                                        @foreach($incomingFromAdvisers as $req)
                                            <li class="border border-gray-200 rounded-md p-2">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="truncate">
                                                        <span class="text-sm font-medium text-gray-900">{{ $req->adviser->name }}</span>
                                                        <span class="text-xs text-gray-500">
                                                            @if($req->adviser->department) • {{ $req->adviser->department }} @endif
                                                        </span>
                                                    </div>
                                                    <div class="flex gap-1">
                                                        <form id="acceptForm-{{ $t->id }}-{{ $req->id }}" method="POST" action="{{ route('titles.incoming.accept', [$t, $req]) }}">
                                                            @csrf
                                                            <button type="button"
                                                                    class="px-2.5 py-1 rounded-md bg-green-600 text-white text-xs hover:bg-green-700"
                                                                    data-confirm
                                                                    data-title="Accept Adviser"
                                                                    data-message="Accept {{ $req->adviser->name }} as adviser for “{{ $t->title }}”? This will close other pending requests."
                                                                    data-form="acceptForm-{{ $t->id }}-{{ $req->id }}">
                                                                Accept
                                                            </button>
                                                        </form>
                                                        <form id="declineForm-{{ $t->id }}-{{ $req->id }}" method="POST" action="{{ route('titles.incoming.decline', [$t, $req]) }}">
                                                            @csrf
                                                            <button type="button"
                                                                    class="px-2.5 py-1 rounded-md bg-red-600 text-white text-xs hover:bg-red-700"
                                                                    data-confirm
                                                                    data-title="Decline Adviser"
                                                                    data-message="Decline {{ $req->adviser->name }}’s request for “{{ $t->title }}”?"
                                                                    data-form="declineForm-{{ $t->id }}-{{ $req->id }}">
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

                        {{-- Sync select → hidden input --}}
                        <script>
                            (function(){
                                const s = document.getElementById('adviser_id_{{ $t->id }}');
                                const h = document.getElementById('adviser_id_hidden_{{ $t->id }}');
                                if (s && h) { h.value = s.value; s.addEventListener('change',()=>h.value=s.value); }
                            })();
                        </script>
                    @endforeach
                @endif
            </section>

            {{-- RIGHT COLUMN: Waiting for Admin --}}
            <section class="space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Waiting for Admin</h3>
                    <span class="text-xs text-gray-500">{{ $awaitingAdmin->count() }} item(s)</span>
                </div>

                @if($awaitingAdmin->isEmpty())
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-sm text-gray-600 text-center">
                        No titles are currently awaiting admin approval.
                    </div>
                @else
                    @foreach($awaitingAdmin as $t)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-700 text-sm md:text-base truncate">{{ $t->title }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-800 ring-1 ring-blue-200">awaiting_admin</span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] bg-gray-50 text-gray-700 ring-1 ring-gray-200">Editing locked</span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600">
                                    Adviser accepted — pending admin approval. You’ll be notified once approved.
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </section>
        </div>

        <div class="mt-3">
            {{ $titles->links() }}
        </div>
    </div>

    {{-- Confirm Modal (reuse yours as-is) --}}
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

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="confirmCancelBtn"
                        class="px-3 py-1.5 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm">
                    Cancel
                </button>
                <button type="button" id="confirmOkBtn"
                        class="px-3 py-1.5 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
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
                modal.classList.remove('hidden'); modal.classList.add('flex');
            }
            function closeModal() {
                modal.classList.add('hidden'); modal.classList.remove('flex');
                titleEl.textContent = 'Confirm'; msgEl.textContent = 'Are you sure?'; targetFormId = null;
            }
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-confirm]'); if (!btn) return;
                openModal({
                    title: btn.getAttribute('data-title'),
                    message: btn.getAttribute('data-message'),
                    formId: btn.getAttribute('data-form')
                });
            });
            overlay.addEventListener('click', closeModal);
            cancel.addEventListener('click', closeModal);
            okBtn.addEventListener('click', () => {
                if (targetFormId) { const f = document.getElementById(targetFormId); if (f) f.submit(); }
                closeModal();
            });
        })();
    </script>
</x-userlayout>
