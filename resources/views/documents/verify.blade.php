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
            <div class="mb-4 flex justify-start">
                <button type="button" onclick="startVerification()" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                    Verify Title
                </button>
            </div>

            <!-- ✅ Similarity Result Bars -->
            <div class="mb-4 flex flex-col space-y-2">
                <!-- Internal Similarity -->
                <div class="text-sm text-gray-600 flex">
                    <div>Internal Scan: &nbsp;</div>
                    <div id="similarity-result" class="text-sm text-gray-600">Waiting for verification...</div>
                </div>   
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <span id="similarity-percent" class="text-xs text-gray-500">0%</span>

                <!-- Web Similarity -->
                <div class="text-sm text-gray-600 flex pt-4">
                    <div>Web Scan: &nbsp;</div>
                    <div id="external-similarity-result" class="text-sm text-gray-600">Waiting for verification...</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <span id="external-similarity-percent" class="text-xs text-gray-500">0%</span>
            </div>

             <!-- Similar Titles Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Internal List -->
                <div>
                    <h4 class="font-semibold text-blue-600 mb-2">📘 Internal Similar Titles</h4>
                    <ul id="internal-similar-titles" class="list-inside list-disc space-y-1 text-sm text-gray-700 bg-gray-50 p-4 rounded-md border border-blue-100 min-h-[100px]">
                        <li class="italic text-gray-400">No similar internal titles found.</li>
                    </ul>
                </div>

                <!-- Web List -->
                <div>
                    <h4 class="font-semibold text-green-600 mb-2">🌐 Web Similar Titles</h4>
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

    async function startVerification() {
        if (isRunning) return; // prevent double-click spam
        isRunning = true;
        passedInternal = false;
        passedExternal = false;
        updateProceedButton();

        const title = document.getElementById('title').value.trim();
        if (title.length < 5) {
            alert("Please enter a more descriptive title.");
            isRunning = false;
            return;
        }

        // Lock button UI
        const verifyBtn = event?.target?.closest('button') || document.querySelector('button[onclick="startVerification()"]');
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.classList.add('opacity-60','cursor-not-allowed');
        }

        showLoading();

        /* ------- Internal Similarity (Cosine) ------- */
        const similarities = existingTitles.map(existing => ({
            title: existing,
            score: cosineSimilarity(title, existing)
        }));

        similarities.sort((a, b) => b.score - a.score);

        const topMatches = similarities.slice(0, 5);
        const internalPercent = Math.round(((topMatches[0]?.score) || 0) * 100);

        updateBars({
            bar: document.getElementById("similarity-bar"),
            percent: document.getElementById("similarity-percent"),
            result: document.getElementById("similarity-result")
        }, internalPercent, internalPercent < 30);

        // Internal list UI
        const internalList = document.getElementById("internal-similar-titles");
        internalList.innerHTML = "";
        let hasValidInternal = false;

        topMatches.forEach((m, i) => {
            const pct = Math.round(m.score * 100);
            if (pct > 0) {
                const li = document.createElement("li");
                li.innerHTML = `${i === 0 ? '🔥 ' : ''}${m.title} — ${pct}%`;
                internalList.appendChild(li);
                hasValidInternal = true;
            }
        });

        if (!hasValidInternal) {
            internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
        }

        passedInternal = internalPercent < 30; // gate

        /* ------- Web Similarity with auto-retry ------- */
        updateBars({
            bar: document.getElementById("external-similarity-bar"),
            percent: document.getElementById("external-similarity-percent"),
            result: document.getElementById("external-similarity-result")
        }, 0, false, 'Waiting for web results…');

        const { data, attempts } = await fetchWebSimilarityWithRetries(title, 5);

        // Update web bars/lists
        hideLoading();

        const webPercent = Math.round(Number(data.max_similarity || 0));
        updateBars({
            bar: document.getElementById("external-similarity-bar"),
            percent: document.getElementById("external-similarity-percent"),
            result: document.getElementById("external-similarity-result")
        }, webPercent, Boolean(data.approved));

        const webList = document.getElementById("web-similar-titles");
        webList.innerHTML = "";
        if (Array.isArray(data.results) && data.results.length > 0) {
            data.results.forEach((item, index) => {
                const li = document.createElement("li");
                li.innerHTML = `${index === 0 ? '🔥 ' : ''}${item.title} — ${item.similarity}%`;
                webList.appendChild(li);
            });
            if (attempts > 1) {
                // Subtle hint that we retried
                const hint = document.createElement('div');
                hint.className = 'text-xs text-gray-500 mt-2';
                hint.textContent = `Found after ${attempts} attempt(s).`;
                webList.parentElement.appendChild(hint);
            }
        } else {
            webList.innerHTML = `<li class="italic text-gray-400">No similar web titles found after ${attempts} attempt(s).</li>`;
        }

        passedExternal = Boolean(data.approved);
        updateProceedButton();

        // Unlock button UI
        if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.classList.remove('opacity-60','cursor-not-allowed');
        }
        isRunning = false;
    }
</script>

</x-userlayout>
 