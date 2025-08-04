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

        const existingTitles = @json(\App\Models\Title::pluck('title')->toArray());

        function updateBars(target, percent, approved) {
            target.bar.style.width = percent + '%';
            target.percent.innerText = percent + '%';
            target.result.innerText = `Similarity: ${percent}% — ${approved ? 'Title is acceptable.' : 'Title might be rejected.'}`;
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
            for (const key in mapA) {
                if (mapB[key]) product += mapA[key] * mapB[key];
            }
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

        function startVerification() {
            const title = document.getElementById('title').value.trim();
            if (title.length < 5) {
                alert("Please enter a more descriptive title.");
                return;
            }

            showLoading();

            // Internal Similarity (Cosine)
            const similarities = existingTitles.map(existing => ({
                title: existing,
                score: cosineSimilarity(title, existing)
            }));

            const sorted = similarities.sort((a, b) => b.score - a.score);
            const topMatches = sorted.slice(0, 5);
            const maxSim = topMatches[0]?.score || 0;
            const percent = Math.round(maxSim * 100);

            updateBars({
                bar: document.getElementById("similarity-bar"),
                percent: document.getElementById("similarity-percent"),
                result: document.getElementById("similarity-result")
            }, percent, percent < 30);

            // Internal List
            const internalList = document.getElementById("internal-similar-titles");
            internalList.innerHTML = "";
            if (topMatches.length > 0) {
      let hasValidMatch = false;
topMatches.forEach((match, index) => {
    const percent = Math.round(match.score * 100);
    if (percent > 0) {
        const li = document.createElement("li");
        li.innerHTML = index === 0 
            ? `${match.title} — ${percent}%` 
            : `${match.title} — ${percent}%`;
        internalList.appendChild(li);
        hasValidMatch = true;
    }
});

if (!hasValidMatch) {
    internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
}




            } else {
                internalList.innerHTML = `<li class="italic text-gray-400">No similar internal titles found.</li>`;
            }

            // Web Similarity
            fetch("{{ route('documents.check-web') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title })
            })
            .then(res => res.json())
            .then(data => {
                hideLoading();
                updateBars({
                    bar: document.getElementById("external-similarity-bar"),
                    percent: document.getElementById("external-similarity-percent"),
                    result: document.getElementById("external-similarity-result")
                }, data.max_similarity, data.approved);

                const webList = document.getElementById("web-similar-titles");
                webList.innerHTML = "";
                if (data.results && data.results.length > 0) {
                            data.results.forEach((item, index) => {
                    const li = document.createElement("li");
                    const label = index === 0 ? "" : "";
                    li.innerHTML = `${label}${item.title} — ${item.similarity}%`;
                    webList.appendChild(li);
                });

                } else {
                    webList.innerHTML = `<li class="italic text-gray-400">No similar web titles found.</li>`;
                }

                passedInternal = percent < 30;
                passedExternal = data.approved;
                updateProceedButton();
            });
        }

        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        function updateProceedButton() {
            const btn = document.getElementById('proceed-btn');
            btn.disabled = !(passedInternal && passedExternal);
        }
    </script>
</x-userlayout>
 