  <!-- verify.blade.php -->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4 text-white">Verify Title</h2>
    </div>

    <div class="container mx-auto px-4 py-8">
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

            <div class="mb-4 flex flex-col space-y-2">
                <div class="text-sm text-gray-600 flex">
                    <div>Internal Scan: &nbsp;</div>
                    <div id="similarity-result" class="text-sm text-gray-600">Waiting for title input...</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="similarity-bar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <span id="similarity-percent" class="text-xs text-gray-500">0%</span>

                <div class="text-sm text-gray-600 flex">
                    <div>Web Scan: &nbsp;</div>
                    <div id="external-similarity-result" class="text-sm text-gray-600">Waiting for title input...</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="external-similarity-bar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <span id="external-similarity-percent" class="text-xs text-gray-500">0%</span>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Proceed to Document
                </button>
            </div>
        </form>
    </div>

    <script>
        const currentDocumentId = '';
        const debounce = (func, delay) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), delay);
            };
        };

        const updateBars = (target, percent, approved) => {
            target.bar.style.width = percent + '%';
            target.percent.innerText = percent + '%';
            target.result.innerText = `Similarity: ${percent}% — ${approved ? 'Title is acceptable.' : 'Title might be rejected.'}`;
            target.result.classList.toggle('text-green-600', approved);
            target.result.classList.toggle('text-red-600', !approved);
        };

        const checkSimilarity = debounce(title => {
            if (title.length < 5) return;

            fetch("{{ route('documents.check-similarity') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title })
            })
            .then(res => res.json())
            .then(data => updateBars({
                bar: document.getElementById("similarity-bar"),
                percent: document.getElementById("similarity-percent"),
                result: document.getElementById("similarity-result")
            }, data.max_similarity, data.approved));

            fetch("{{ route('documents.check-web') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title })
            })
            .then(res => res.json())
            .then(data => updateBars({
                bar: document.getElementById("external-similarity-bar"),
                percent: document.getElementById("external-similarity-percent"),
                result: document.getElementById("external-similarity-result")
            }, data.max_similarity, data.approved));
        }, 600);

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('title');
            input.addEventListener('input', e => checkSimilarity(e.target.value));
        });
    </script>
</x-userlayout>
