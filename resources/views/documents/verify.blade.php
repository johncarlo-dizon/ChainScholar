<x-userlayout>
  <div class="bg-blue-600 rounded-lg shadow p-4 sm:p-6">
    <h2 class="text-2xl sm:text-3xl font-semibold text-white">Verify Title</h2>
  </div>

  <div class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 bg-white mt-5 sm:mt-7 shadow rounded-xl">
    <form method="POST" action="{{ route('titles.verify.submit') }}">
      @csrf

      <div class="mb-5 sm:mb-6">
        <label for="title" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Document Title</label>
        <input 
          type="text" 
          name="title" 
          id="title"
          class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-base sm:text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
          placeholder="Enter title to verify"
          required
        >
      </div>

      <!-- Buttons Row -->
      <div class="mb-4 sm:mb-3 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        <!-- Verify -->
        <button type="button"
                onclick="startVerification(event)"
                id="verify-btn"
                class="w-full sm:w-auto px-4 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 active:scale-[.99]">
          Verify Title
        </button>

        <!-- NEW: AI Feedback Toggle (disabled/hidden until relevant) -->
        <button type="button"
                id="ai-toggle-btn"
                class="w-full sm:w-auto px-4 py-2.5 shadow text-blue-700 rounded-lg hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed hidden"
                aria-controls="ai-feedback"
                aria-expanded="false"
                onclick="toggleAIPanel()">
          <span id="ai-toggle-label">Show AI Feedback</span>
        </button>

        <span id="reject-hint" class="text-sm text-rose-600 hidden sm:ml-2">Title Rejected</span>
      </div>
<!-- AI Feedback (compact 3-column layout) -->
<div id="ai-feedback" class="mt-5 hidden">
  <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 sm:p-5">
    <div class="flex items-center gap-2 mb-4">
      <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-white text-xs">AI</span>
      <h3 id="ai-heading" class="font-semibold text-rose-700 text-base sm:text-lg">Why it was rejected</h3>
    </div>

    <!-- 3-column grid to reduce empty space -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Reasons -->
      <div>
        <h4 class="font-semibold text-rose-700 text-base mb-2">Reasons</h4>
        <ul id="ai-reasons" class="list-disc list-inside text-rose-700 text-sm space-y-1">
          <!-- filled by JS -->
        </ul>
      </div>

      <!-- Improvements -->
      <div>
        <h4 class="font-semibold text-emerald-700 text-base mb-2">How to improve</h4>
        <ul id="ai-suggestions" class="list-disc list-inside text-emerald-700 text-sm space-y-1">
          <!-- filled by JS -->
        </ul>
      </div>

      <!-- Sample Titles -->
      <div>
        <h4 class="font-semibold text-slate-800 text-base mb-2">Sample improved titles</h4>
        <ul id="ai-samples" class="list-disc list-inside text-slate-700 text-sm space-y-1">
          <!-- filled by JS -->
        </ul>
      </div>

    </div>
  </div>
</div>


      <!-- Similarity Result Bars -->
      <div class="flex flex-col xl:flex-row items-stretch gap-3 sm:gap-4 mt-3 sm:mt-5 mb-2 min-w-0">
        <!-- Internal -->
        <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700">Internal Similar Titles:</span>
            <span id="similarity-result" class="truncate">Waiting for verification...</span>
          </div>
          <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
          </div>
          <span id="similarity-percent" class="text-[11px] sm:text-xs text-gray-500 whitespace-nowrap">0%</span>
        </div>

        <!-- Web -->
        <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700">Web Similar Titles:</span>
            <span id="external-similarity-result" class="truncate">Waiting for verification...</span>
          </div>
          <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
          </div>
          <span id="external-similarity-percent" class="text-[11px] sm:text-xs text-gray-500 whitespace-nowrap">0%</span>
        </div>
      </div>

      <!-- Similar Titles Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="rounded-md border border-gray-200">
          <ul id="internal-similar-titles" 
              class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-3 sm:p-4 min-h-[160px] max-h-[45vh] lg:max-h-[28rem] overflow-y-auto">
            <li class="italic text-gray-400">No similar internal titles found.</li>
          </ul>
        </div>
        <div class="rounded-md border border-gray-200">
          <ul id="web-similar-titles" 
              class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-3 sm:p-4 min-h-[160px] max-h-[45vh] lg:max-h-[28rem] overflow-y-auto">
            <li class="italic text-gray-400">No similar web titles found.</li>
          </ul>
        </div>
      </div>

      {{-- Adviser chooser (hidden until internal+web pass) --}}
      <div id="adviser-box" class="mt-6 hidden">
        <label for="adviser_id" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Choose Adviser</label>
        @if(($advisers ?? collect())->count())
          <select id="adviser_id" name="adviser_id"
                  class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                  disabled>
            <option value="">— Select an adviser —</option>
            @foreach($advisers as $adv)
              <option value="{{ $adv->id }}">
                {{ $adv->name }}
                @if($adv->department) — {{ $adv->department }} @endif
                @if($adv->specialization) ({{ $adv->specialization }}) @endif
              </option>
            @endforeach
          </select>
          <p class="text-xs text-gray-500 mt-1">
            The adviser will receive your request and can accept/decline.
          </p>
        @else
          <div class="p-3 rounded-md bg-amber-50 border border-amber-200 text-sm text-amber-800">
            No advisers available yet. Please contact the administrator.
          </div>
        @endif
      </div>

      <div id="authors-box" class="mt-4 hidden">
        <label for="authors" class="block mb-2 font-bold text-blue-600 text-sm sm:text-base">Authors</label>
        <input 
          type="text" 
          name="authors" 
          id="authors"
          class="w-full border border-gray-300 rounded-md px-3 sm:px-4 py-2.5 sm:py-3 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
          placeholder="Enter author name(s), e.g., Last, First; Last, First"
          disabled
        >
        <p class="text-xs text-gray-500 mt-1">
          Separate multiple authors with commas or semicolons.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row sm:justify-end gap-3 sm:gap-0 mt-5">
        <button id="proceed-btn" type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>
          Proceed to Document
        </button>
      </div>
    </form>
  </div>

  <!-- Loading Overlay -->
  <div id="loading-overlay" class="fixed inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-50 hidden">
    <div class="text-center px-6">
      <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      <p class="text-blue-700 font-semibold text-base sm:text-lg">Scanning title for similarity...</p>
    </div>
  </div>

  {{-- ===== JS ===== --}}
  <script>
/* ---------- Helpers ---------- */
function escapeHtml(str){
  return (str || '').replace(/[&<>"']/g, m => (
    {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]
  ));
}
function toggleRejectHint(on){
  const hint = document.getElementById('reject-hint');
  if (on) hint.classList.remove('hidden'); else hint.classList.add('hidden');
}
function updateBars(target, percent, approved, labelWhenWaiting = null) {
  target.bar.style.width = percent + '%';
  target.percent.innerText = percent + '%';
  target.result.innerText = labelWhenWaiting ?? `Similarity: ${percent}% — ${approved ? 'Approved.' : 'Rejected.'}`;
  target.result.classList.toggle('text-green-600', approved);
  target.result.classList.toggle('text-red-600', !approved);
}
function showLoading(message = 'Scanning title for similarity...') {
  document.getElementById('loading-overlay').classList.remove('hidden');
  document.querySelector('#loading-overlay p').textContent = message;
}
function hideLoading() {
  document.getElementById('loading-overlay').classList.add('hidden');
}
function showApprovalFields(show){
  const advBox = document.getElementById('adviser-box');
  const advSel = document.getElementById('adviser_id');
  const authBox = document.getElementById('authors-box');
  const authInp = document.getElementById('authors');
  if (show) {
    advBox?.classList.remove('hidden'); if (advSel) { advSel.disabled = false; advSel.setAttribute('required','required'); }
    authBox?.classList.remove('hidden'); if (authInp) { authInp.disabled = false; authInp.setAttribute('required','required'); }
  } else {
    advBox?.classList.add('hidden'); if (advSel) { advSel.disabled = true; advSel.removeAttribute('required'); advSel.value = ''; }
    authBox?.classList.add('hidden'); if (authInp) { authInp.disabled = true; authInp.removeAttribute('required'); authInp.value = ''; }
  }
}
function updateProceedButton(){
  const btn  = document.getElementById('proceed-btn');
  const adv  = document.getElementById('adviser_id');
  const auth = document.getElementById('authors');
  const passed = (window.passedInternal && window.passedExternal);
  showApprovalFields(passed);
  const adviserOk = adv ? (adv.value && adv.value !== '') : true;
  const authorsOk = auth ? (auth.value && auth.value.trim().length > 0) : true;
  btn.disabled = !(passed && adviserOk && authorsOk);
}
document.addEventListener('input', (e) => {
  if (e.target && (e.target.id === 'adviser_id' || e.target.id === 'authors')) {
    updateProceedButton();
  }
});
async function sleep(ms){ return new Promise(r => setTimeout(r, ms)); }

/* ---------- AI Panel: NEW helpers ---------- */
const AI_STORE_KEY = 'aiPanelOpen';

function setAIHeading(rejected = true){
  const h = document.getElementById('ai-heading');
  h.textContent = rejected ? 'Why it was rejected' : 'AI Feedback';
}

function enableAIToggle(showButton){
  const btn = document.getElementById('ai-toggle-btn');
  if (showButton) {
    btn.classList.remove('hidden');
    btn.disabled = false;
  } else {
    btn.classList.add('hidden');
    btn.disabled = true;
  }
}

function setAIToggleLabel(open){
  const lbl = document.getElementById('ai-toggle-label');
  const btn = document.getElementById('ai-toggle-btn');
  lbl.textContent = open ? 'Hide AI Feedback' : 'Show AI Feedback';
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function openAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  wrap.classList.remove('hidden');
  setAIToggleLabel(true);
  try { localStorage.setItem(AI_STORE_KEY, '1'); } catch(e){}
}

function closeAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  wrap.classList.add('hidden');
  setAIToggleLabel(false);
  try { localStorage.setItem(AI_STORE_KEY, '0'); } catch(e){}
}

function toggleAIPanel(){
  const wrap = document.getElementById('ai-feedback');
  const isHidden = wrap.classList.contains('hidden');
  if (isHidden) openAIPanel(); else closeAIPanel();
}

function fillList(ul, items, emptyMsg='—') {
  ul.innerHTML = '';
  if (!items || !items.length) {
    const li = document.createElement('li');
    li.className = 'text-gray-500 italic';
    li.textContent = emptyMsg;
    ul.appendChild(li);
    return;
  }
  items.forEach(t => {
    const li = document.createElement('li');
    li.textContent = t;
    ul.appendChild(li);
  });
}

async function fetchAIFeedback(payload) {
  const res = await fetch("{{ route('titles.ai-feedback') }}", {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: JSON.stringify(payload)
  });
  return res.json();
}

function renderAIFeedback(reasons=[], suggestions=[], samples=[]){
  const ulR  = document.getElementById('ai-reasons');
  const ulS  = document.getElementById('ai-suggestions');
  const ulSm = document.getElementById('ai-samples');
  fillList(ulR, reasons, 'No reasons generated.');
  fillList(ulS, suggestions, 'No suggestions generated.');
  fillList(ulSm, samples, 'No sample titles generated.');
}

/* ---------- Internal state ---------- */
window.passedInternal = false;
window.passedExternal = false;
window.__lastInternalData = null;
window.__lastWebData = null;
let isRunning = false;

/* ---------- Web similarity with retry ---------- */
async function fetchWebSimilarityWithRetries(title, maxTries = 2) {
  let last = null;
  for (let attempt = 1; attempt <= maxTries; attempt++) {
    const res = await fetch("{{ route('documents.check-web') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ title, attempt })
    });
    last = await res.json();

    const hasResult =
      Array.isArray(last.results) &&
      last.results.length > 0 &&
      (Number(last.max_similarity) > 0 || last.results.some(r => Number(r.similarity) > 0));

    if (hasResult) return { data: last, attempts: attempt };

    showLoading(`No results yet. Retrying (${attempt}/${maxTries})…`);
    await sleep(400 * attempt);
  }
  return { data: last ?? { max_similarity: 0, approved: true, results: [] }, attempts: maxTries };
}

/* ---------- Main ---------- */
async function startVerification(event){
  if (isRunning) return;
  isRunning = true;

  // Reset state
  window.passedInternal = false;
  window.passedExternal = false;
  updateProceedButton();
  toggleRejectHint(false);

  // Hide AI stuff until needed
  closeAIPanel();
  enableAIToggle(false);
  setAIHeading(true); // default

  const title = document.getElementById('title').value.trim();
  if (title.length < 5){
    alert("Please enter a more descriptive title.");
    isRunning = false;
    return;
  }

  const verifyBtn = event?.target?.closest('button') || document.getElementById('verify-btn');
  if (verifyBtn){
    verifyBtn.disabled = true;
    verifyBtn.classList.add('opacity-60','cursor-not-allowed');
  }
  showLoading();

  /* ----- INTERNAL ----- */
  let internalPercent = 0;
  try {
    const res = await fetch("{{ route('documents.check-similarity') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ title, exclude_title_id: null, limit: 10 })
    });
    if (!res.ok) throw new Error('Internal similarity failed: ' + res.status);
    const internalData = await res.json();
    window.__lastInternalData = internalData;

    internalPercent = Math.round(Number(internalData.max_similarity || 0));
    updateBars({
      bar: document.getElementById("similarity-bar"),
      percent: document.getElementById("similarity-percent"),
      result: document.getElementById("similarity-result")
    }, internalPercent, internalPercent < 30);

    const internalList = document.getElementById("internal-similar-titles");
    internalList.innerHTML = "";
    if (Array.isArray(internalData.results) && internalData.results.length) {
      internalData.results.forEach((item, idx) => {
        const li = document.createElement('li');
        const safeTitle = escapeHtml(item.title || '');
        const byline = (item.authors && item.authors.length)
          ? escapeHtml(item.authors.slice(0, 5).join(', ')) + (item.authors.length > 5 ? ' et al.' : '')
          : 'Unknown author(s)';
        const year = item.year ? ` (${item.year})` : '';
        const sim  = (typeof item.similarity === 'number') ? `${item.similarity}%` : '—';
        li.className = 'p-2 bg-white rounded-md border hover:border-blue-400 hover:shadow-sm transition';
        li.innerHTML = `
          <div class="text-sm leading-snug">
            <div class="font-medium">${idx===0 ? '🔥 ' : ''}${safeTitle}</div>
            <div class="text-xs text-gray-600 mt-0.5">by ${byline}${year}</div>
            <div class="text-xs text-gray-600 mt-1">
              <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 border text-gray-700">
                Similarity: ${sim}
              </span>
            </div>
          </div>
        `;
        internalList.appendChild(li);
      });
    } else {
      internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
    }
    window.passedInternal = internalPercent < 30;
  } catch (e) {
    console.error(e);
    updateBars({
      bar: document.getElementById("similarity-bar"),
      percent: document.getElementById("similarity-percent"),
      result: document.getElementById("similarity-result")
    }, 0, true);
    document.getElementById("internal-similar-titles").innerHTML = `<li class="italic text-gray-400">Internal check unavailable.</li>`;
    window.passedInternal = true; // don’t block on internal failure
  }

  /* ----- WEB ----- */
  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, 0, false, 'Waiting for web results…');

  const { data } = await fetchWebSimilarityWithRetries(title, 2);
  hideLoading();

  window.__lastWebData = data;
  const webPercent = Math.round(Number(data.max_similarity || 0));

  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, webPercent, Boolean(data.approved));

  const webList = document.getElementById("web-similar-titles");
  webList.innerHTML = "";
  if (Array.isArray(data.results) && data.results.length){
    data.results.forEach((item, idx) => {
      const li = document.createElement('li');
      const safeTitle = escapeHtml(item.title || '');
      const byline = (item.authors && item.authors.length)
          ? escapeHtml(item.authors.slice(0, 5).join(', ')) + (item.authors.length > 5 ? ' et al.' : '')
          : 'Unknown author(s)';
      const year = item.year ? ` (${item.year})` : '';
      const src  = item.source ? ` · <span class="text-[11px] text-gray-500">${escapeHtml(item.source)}</span>` : '';
      const sim  = (typeof item.similarity === 'number') ? `${item.similarity}%` : '—';
      const link = item.link ? `<a href="${item.link}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Link</a>` : '<span class="text-gray-400">No link</span>';
      li.className = 'p-2 bg-white rounded-md border hover:border-green-400 hover:shadow-sm transition';
      li.innerHTML = `
        <div class="text-sm leading-snug">
          <div class="font-medium">${idx===0 ? '🔥 ' : ''}${safeTitle}</div>
          <div class="text-xs text-gray-600 mt-0.5">by ${byline}${year}${src}</div>
          <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
            <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 border text-gray-700">Similarity: ${sim}</span>
            ${link}
          </div>
        </div>
      `;
      webList.appendChild(li);
    });
  } else {
    webList.innerHTML = `<li class="italic text-gray-400">No similar web titles found.</li>`;
  }

  window.passedExternal = Boolean(data.approved);
  updateProceedButton();

  const finalPass = (window.passedInternal && window.passedExternal);
  toggleRejectHint(!finalPass);

  // === NEW: AI feedback flow ===
  // We only generate feedback when rejected, but we always let the user decide to show/hide via the toggle.
  if (!finalPass) {
    try {
      showLoading('Generating AI feedback…');

      const internalExamples = Array.isArray(window.__lastInternalData?.results)
        ? window.__lastInternalData.results.slice(0,3).map(r => ({
            title: r.title, similarity: r.similarity, year: r.year || null
          }))
        : [];
      const webExamples = Array.isArray(window.__lastWebData?.results)
        ? window.__lastWebData.results.slice(0,3).map(r => ({
            title: r.title, similarity: r.similarity, source: r.source || null, year: r.year || null
          }))
        : [];

      const aiPayload = {
        title: title,
        internal_percent: Math.round(Number(window.__lastInternalData?.max_similarity || 0)),
        web_percent: Math.round(Number(window.__lastWebData?.max_similarity || 0)),
        internal_examples: internalExamples,
        web_examples: webExamples,
        rules: ['reject_if_percent>=30']
      };

      const aiRes = await fetchAIFeedback(aiPayload);
      hideLoading();

      // Render + prepare toggle
      if (aiRes?.ok) {
        renderAIFeedback(aiRes.reasons, aiRes.suggestions, aiRes.improved_samples);
      } else {
        renderAIFeedback(
          ['AI feedback unavailable.'],
          ['Refine scope, add population, specify method.'],
          []
        );
      }

      // Heading should explicitly say "was rejected"
      setAIHeading(true);
      enableAIToggle(true);

      // Respect last user choice (open/close)
      let wantOpen = false;
      try { wantOpen = localStorage.getItem(AI_STORE_KEY) === '1'; } catch(e){}
      if (wantOpen) openAIPanel(); else closeAIPanel();

    } catch (err) {
      console.error(err);
      hideLoading();
      renderAIFeedback(
        ['Error generating AI feedback.'],
        ['Shorten the title and add a unique angle.'],
        []
      );
      setAIHeading(true);
      enableAIToggle(true);
      closeAIPanel();
    }
  } else {
    // Passed — keep AI button hidden & panel closed
    enableAIToggle(false);
    closeAIPanel();
  }

  if (verifyBtn){
    verifyBtn.disabled = false;
    verifyBtn.classList.remove('opacity-60','cursor-not-allowed');
  }
  isRunning = false;
}
  </script>
</x-userlayout>
