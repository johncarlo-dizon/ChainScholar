<x-userlayout> 
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">Verify Title</h2>
    </div>

    <div class="container mx-auto px-4 py-8 bg-white mt-7 shadow rounded-xl">



        <form method="POST" action="{{ route('titles.verify.submit') }}">
            @csrf

            <div class="mb-6">
                <label for="title" class="block mb-2 font-bold text-blue-600">Document Title</label>
                <input 
                    type="text" 
                    name="title" 
                    id="title"
                    class="w-full border border-gray-300 rounded-md px-4 py-3 text-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
                    placeholder="Enter title to verify"
                    required
                >
            </div>





            <!-- ✅ Verify Button -->
<div class="mb-3 flex items-center gap-3">
  <button type="button"
          onclick="startVerification(event)"
          id="verify-btn"
          class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
    Verify Title
  </button>

  <!-- Inline AI loader (only for Gemini; hidden by default) -->
  <div id="ai-inline-loader" class="hidden w-40 h-2 rounded bg-gray-200 overflow-hidden">
    <div id="ai-inline-loader-bar" class="h-2 w-0 bg-purple-600 transition-all duration-300"></div>
  </div>

  <!-- Small text hint on rejection -->
  <span id="reject-hint" class="text-sm text-rose-600 hidden">
      Title Rejected
  </span>
</div>



<div id="ai-suggestions" class="mt-4 hidden">
  <h4 class="font-semibold text-purple-700 mb-2">🤖 AI Suggested Titles</h4>
  <ul id="ai-suggestions-list" class="space-y-2 bg-purple-50 p-4 rounded-md border border-purple-200"></ul>
   
</div>

 

            <!-- ✅ Similarity Result Bars -->
         <div class="flex items-center gap-6 mt-5 mb-2">
    <!-- Internal Similarity -->
    <div class="flex items-center gap-3 flex-1">
        <!-- Label and result -->
        <div class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700 text-1xl">Internal Similar Titles:</span>
            <span id="similarity-result" class="text-sm text-gray-600">Waiting for verification...</span>
        </div>

        <!-- Progress bar -->
        <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>

        <!-- Percentage -->
        <span id="similarity-percent" class="text-xs text-gray-500 whitespace-nowrap">0%</span>
    </div>

    <!-- Web Similarity -->
    <div class="flex items-center gap-3 flex-1">
        <!-- Label and result -->
        <div class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
            <span class="font-bold text-blue-700 text-1xl">Web Similar Titles:</span>
            <span id="external-similarity-result" class="text-sm text-gray-600">Waiting for verification...</span>
        </div>

        <!-- Progress bar -->
        <div class="flex-1 bg-gray-200 rounded-full h-3">
            <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>

        <!-- Percentage -->
        <span id="external-similarity-percent" class="text-xs text-gray-500 whitespace-nowrap">0%</span>
    </div>
</div>


             <!-- Similar Titles Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Internal List -->
                <div>
                    <ul id="internal-similar-titles" class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-4 rounded-md border border-blue-100 min-h-[100px]">
                        <li class="italic text-gray-400">No similar internal titles found.</li>
                    </ul>
                </div>

                <!-- Web List -->
                <div>
                     <ul id="web-similar-titles" class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-4 rounded-md border border-green-100 min-h-[100px]">
                        <li class="italic text-gray-400">No similar web titles found.</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button id="proceed-btn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>
                    Proceed to Document
                </button>
            </div>
        </form>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 hidden">
        <div class="text-center">
            <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <p class="text-blue-700 font-semibold text-lg">Scanning title for similarity...</p>
        </div>
    </div>
 


<script>
let passedInternal = false;
let passedExternal = false;
let isRunning = false;

// NEW:
let wasRejected = false;   // true if internal≥30 or web≥30
let autoAITriggered = false;

const existingTitles = @json(\App\Models\Title::pluck('title')->toArray());



 
    function updateBars(target, percent, approved, labelWhenWaiting = null) {
        target.bar.style.width = percent + '%';
        target.percent.innerText = percent + '%';
        target.result.innerText = labelWhenWaiting ?? `Similarity: ${percent}% — ${approved ? 'Title is acceptable.' : 'Title might be rejected.'}`;
        target.result.classList.toggle('text-green-600', approved);
        target.result.classList.toggle('text-red-600', !approved);
    }

    function tokenize(text) {
        const stopWords = new Set([
            'a','an','and','are','as','at','be','by','for','from','has','he','in','is','it','its','of','on','that','the','to','was','were','will','with','she','they','we','you','your','but','or','if','then','so','because','about','this','what','which','who','whom','where','when','how','can','could','would','should','may','might','must','do','does','did','done','not','no','yes','also','such','their','there','here','into','out','up','down','over','under','again','each','other','any','all','more','most','some','few','than','very','just','now','only','like','even','ever','many','one','two','three','first','second','third','new','old','same','use','used','using','based','among','between','via','per','toward','towards','across','through','within','without','study','research','paper','report','project','capstone','case','review','investigation','analysis','approach','effect','impact','model','method','methods','design','development','evaluation','implementation','system','application','framework','prototype','solution','tool','tools','technology','technologies','process','processes','exploration','assessment','insight'
        ]);
        return (text.toLowerCase().match(/\w+/g) || []).filter(word => !stopWords.has(word));
    }

    function termFreqMap(tokens) {
        const freqMap = {};
        tokens.forEach(token => freqMap[token] = (freqMap[token] || 0) + 1);
        return freqMap;
    }

    function dotProduct(mapA, mapB) {
        let product = 0;
        for (const key in mapA) if (mapB[key]) product += mapA[key] * mapB[key];
        return product;
    }

    function magnitude(freqMap) {
        return Math.sqrt(Object.values(freqMap).reduce((sum, val) => sum + val * val, 0));
    }

    function cosineSimilarity(textA, textB) {
        const tokensA = tokenize(textA);
        const tokensB = tokenize(textB);
        const freqA = termFreqMap(tokensA);
        const freqB = termFreqMap(tokensB);
        const dot = dotProduct(freqA, freqB);
        const mag = magnitude(freqA) * magnitude(freqB);
        return mag === 0 ? 0 : dot / mag;
    }

    function showLoading(message = 'Scanning title for similarity...') {
        document.getElementById('loading-overlay').classList.remove('hidden');
        document.querySelector('#loading-overlay p').textContent = message;
    }

    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }

    function updateProceedButton() {
        const btn = document.getElementById('proceed-btn');
        btn.disabled = !(passedInternal && passedExternal);
    }

    async function sleep(ms){ return new Promise(r => setTimeout(r, ms)); }

    async function fetchWebSimilarityWithRetries(title, maxTries = 5) {
        let last = null;

        for (let attempt = 1; attempt <= maxTries; attempt++) {
            // Tell server which attempt this is (so it can decide whether to bypass cache)
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

            if (hasResult) {
                return { data: last, attempts: attempt };
            }

            // Update overlay to indicate retry
            showLoading(`No results yet. Retrying (${attempt}/${maxTries})…`);
            // Exponential backoff (0.4s, 0.8s, 1.2s, 1.6s, 2.0s)
            await sleep(400 * attempt);
        }

        return { data: last ?? { max_similarity: 0, approved: true, results: [] }, attempts: maxTries };
    }

   

async function startVerification(event){
  if (isRunning) return;
  isRunning = true;
  passedInternal = false;
  passedExternal = false;
  wasRejected = false;
  autoAITriggered = false;
  updateProceedButton();
  toggleRejectHint(false);

  document.getElementById('ai-suggestions').classList.add('hidden');

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

  // ----- INTERNAL (cosine vs existing) -----
  const sims = existingTitles.map(t => ({ t, s: cosineSimilarity(title, t) }))
                             .sort((a,b)=> b.s - a.s)
                             .slice(0,5);
  const internalPercent = Math.round(((sims[0]?.s)||0) * 100);

  updateBars({
    bar: document.getElementById("similarity-bar"),
    percent: document.getElementById("similarity-percent"),
    result: document.getElementById("similarity-result")
  }, internalPercent, internalPercent < 30);

  const internalList = document.getElementById("internal-similar-titles");
  internalList.innerHTML = "";
  let anyInternal = false;
  sims.forEach((m,i)=>{
    const pct = Math.round(m.s*100);
    if (pct>0){
      const li = document.createElement('li');
      li.innerHTML = `${i===0?'🔥 ':''}${m.t} — ${pct}%`;
      internalList.appendChild(li);
      anyInternal = true;
    }
  });
  if (!anyInternal){
    internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
  }
  passedInternal = internalPercent < 30;

  // ----- WEB (Semantic Scholar via your endpoint + retries) -----
  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, 0, false, 'Waiting for web results…');

  const { data, attempts } = await fetchWebSimilarityWithRetries(title, 5);
  hideLoading();

  const webPercent = Math.round(Number(data.max_similarity || 0));
  updateBars({
    bar: document.getElementById("external-similarity-bar"),
    percent: document.getElementById("external-similarity-percent"),
    result: document.getElementById("external-similarity-result")
  }, webPercent, Boolean(data.approved));

  const webList = document.getElementById("web-similar-titles");
  webList.innerHTML = "";
  if (Array.isArray(data.results) && data.results.length){
    data.results.forEach((item, idx)=>{
      const li = document.createElement('li');
      li.innerHTML = `${idx===0?'🔥 ':''}${item.title} — ${item.similarity}%`;
      webList.appendChild(li);
    });
    if (attempts>1){
      const hint = document.createElement('div');
      hint.className = 'text-xs text-gray-500 mt-2';
      hint.textContent = `Found after ${attempts} attempt(s).`;
      webList.parentElement.appendChild(hint);
    }
  }else{
    webList.innerHTML = `<li class="italic text-gray-400">No similar web titles found after ${attempts} attempt(s).</li>`;
  }

  passedExternal = Boolean(data.approved);
  updateProceedButton();

  // ----- Decide reject/approve & auto-trigger AI if rejected -----
 
wasRejected = !(passedInternal && passedExternal); // rejected if either ≥30
if (wasRejected){
  toggleRejectHint(true);
  // auto‑trigger Gemini once
  suggestWithGemini(true);
}else{
  toggleRejectHint(false);
}


  if (verifyBtn){
    verifyBtn.disabled = false;
    verifyBtn.classList.remove('opacity-60','cursor-not-allowed');
  }
  isRunning = false;
}
</script>




<script>
const SUGGEST_URL = "{{ route('titles.suggest') }}";
const CSRF_TOKEN  = "{{ csrf_token() }}";

async function suggestWithGemini(auto=false){
  const titleEl = document.getElementById('title');
  const draft = (titleEl.value || '').trim();

  if (!wasRejected && !auto){
    // safety: only when rejected
    return;
  }
  if (draft.length < 5){
    return;
  }
  if (auto && autoAITriggered) return;
  autoAITriggered = true;

  // Optional context if you add fields later
  const context = {
    domain:      document.querySelector('#domain')?.value || null,
    problem:     document.querySelector('#problem')?.value || null,
    population:  document.querySelector('#population')?.value || null,
    location:    document.querySelector('#location')?.value || null,
    method:      document.querySelector('#method')?.value || null
  };

  const payload = { draft_title: draft, existing_titles: existingTitles };
  if (Object.values(context).some(v => v)) payload.context = context;

  try{
    aiLoader(true);

    const res = await fetch(SUGGEST_URL, {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept':'application/json' },
      body: JSON.stringify(payload)
    });

    aiLoader(false);

    if (!res.ok){
      console.error(await res.text());
      // Keep UI quiet; just don’t render anything
      return;
    }

    const data = await res.json();
    const wrap = document.getElementById('ai-suggestions');
    const list = document.getElementById('ai-suggestions-list');
    list.innerHTML = '';

    const items = Array.isArray(data.suggestions) ? data.suggestions : [];
    if (!items.length){
      list.innerHTML = '<li class="text-sm italic text-gray-500">No suggestions returned. Try revising and verify again.</li>';
      wrap.classList.remove('hidden');
      return;
    }

    items.forEach((s)=>{
      const li = document.createElement('li');
      li.className = 'p-2 bg-white rounded-md border hover:border-purple-400 hover:shadow-sm transition cursor-pointer';
      li.innerHTML = `
        <div class="text-sm text-gray-800 leading-snug">
          <div class="font-medium">${escapeHtml(s.title)}</div>
          ${s.why ? `<div class="text-xs text-gray-600 mt-1">${escapeHtml(s.why)}</div>` : ''}
        </div>
      `;
     li.addEventListener('click', ()=>{
    document.getElementById('title').value = s.title;
    li.classList.add('ring-2','ring-purple-400');
    setTimeout(()=> li.classList.remove('ring-2','ring-purple-400'), 700);
  });

      list.appendChild(li);
    });

    wrap.classList.remove('hidden');

  }catch(err){
    aiLoader(false);
    console.error(err);
  }
}

function escapeHtml(str){
  return (str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>



<script>
function aiLoader(show){
  const shell = document.getElementById('ai-inline-loader');
  const bar   = document.getElementById('ai-inline-loader-bar');
  if (show){
    shell.classList.remove('hidden');
    bar.style.width = '0%';
    // simple looping fill animation
    let w = 0;
    bar._timer && clearInterval(bar._timer);
    bar._timer = setInterval(()=>{
      w = (w + 12) % 112; // wrap after 100
      bar.style.width = Math.min(w,100) + '%';
    }, 180);
  }else{
    shell.classList.add('hidden');
    const t = bar._timer;
    if (t) clearInterval(t);
    bar._timer = null;
    bar.style.width = '0%';
  }
}

function toggleRejectHint(on){
  const hint = document.getElementById('reject-hint');
  if (on) hint.classList.remove('hidden'); else hint.classList.add('hidden');
}
</script>



</x-userlayout>
 