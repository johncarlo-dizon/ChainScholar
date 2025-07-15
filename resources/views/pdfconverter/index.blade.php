<!-- resources/views/pdfconverter/index.blade.php -->
<x-userlayout>



    
        @if(session('success'))
            <div id="success-alert" class="mb-6 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div id="error-alert" class="mb-6 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {!! session('error') !!}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

          <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">Research Paper Submission</h2>
    </div>
    
  

        <div class="bg-white shadow rounded-lg border border-gray-200 container mx-auto  px-4 py-8 md:p-8 mb-8 mt-4">

            
           <form action="{{ route('research-papers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
        @csrf

        <!-- File Upload -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Upload PDF</label>
            <div class="mt-1 flex items-center">
                <label for="pdfFile" class="cursor-pointer">
                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Choose file
                    </span>
                    <input id="pdfFile" name="fileToUpload" type="file" class="sr-only" accept=".pdf" required>
                </label>
                <span id="file-name" class="ml-4 text-sm text-gray-600">No file selected</span>
            </div>
            <p id="filename-warning" class="text-sm text-red-600"></p>
        </div>

        <!-- Title and Year -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="pdfTitle" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="pdfTitle" name="title" placeholder="Research paper title" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>
            <div>
                <label for="yearInput" class="block text-sm font-medium text-gray-700">Year</label>
                <input type="number" id="yearInput" name="year" placeholder="2023" min="1900" max="2099" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>
        </div>

        <!-- Authors -->
        <div>
            <label for="authorInput" class="block text-sm font-medium text-gray-700">Author(s)</label>
            <input type="text" id="authorInput" name="authors" placeholder="John Doe, Jane Smith" required 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            <p class="mt-1 text-xs text-gray-500">Separate multiple authors with commas</p>
        </div>

        <!-- Department and Program -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="departmentSelect" class="block text-sm font-medium text-gray-700">Department</label>
                <select id="departmentSelect" name="department" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-white">
                    <option value="" disabled selected>Select department</option>
                    <option value="Senior High School">Senior High School</option>
                    <option value="School of Computing, Information Technology and Engineering">School of Computing, IT and Engineering</option>
                    <option value="School of Arts, Sciences, and Education">School of Arts, Sciences, and Education</option>
                    <option value="School of Criminal Justice">School of Criminal Justice</option>
                    <option value="School of Tourism and Hospitality Management">School of Tourism and Hospitality</option>
                    <option value="School of Business and Accountancy">School of Business and Accountancy</option>
                </select>
            </div>
            <div>
                <label for="categorySelect" class="block text-sm font-medium text-gray-700">Program</label>
                <select id="categorySelect" name="program" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-white">
                    <option value="" disabled selected>Select department first</option>
                </select>
            </div>
        </div>

        <!-- Abstract -->
        <div>
            <label for="abstract" class="block text-sm font-medium text-gray-700">Abstract</label>
            <textarea id="abstract" name="abstract" rows="4" placeholder="Enter your research abstract..." required 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"></textarea>
        </div>

        <!-- Extracted Text -->
        <div>
            <label for="pdfText" class="block text-sm font-medium text-gray-700">Extracted Text</label>
            <textarea id="pdfText" name="ocrPdf" rows="8" placeholder="PDF content will appear here..." readonly 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-gray-50"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" id="submitBtn" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Submit Research
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </form>
        </div>
 

 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.min.js"></script>
    <script>
        // Department and Program dropdown logic
        const programs = {
            "Senior High School": ["Accountancy, Business, and Management", "Science, Technology, Engineering, and Mathematics", "Humanities and Social Sciences", "General Academic Strand", "Technical-Vocational-Livelihood - Home Economics", "Technical-Vocational-Livelihood - Information and Communications Technology"],
            "School of Computing, Information Technology and Engineering": ["Bachelor of Science in Civil Engineering", "Bachelor of Science in Computer Engineering", "Bachelor of Science in Computer Science", "Bachelor of Science in Information Technology", "Bachelor of Library and Information Science"],
            "School of Arts, Sciences, and Education": ["Bachelor of Elementary Education", "Bachelor of Science in Development Communication", "Bachelor of Science in Psychology", "Bachelor of Secondary Education major in English", "Bachelor of Secondary Education major in Filipino", "Bachelor of Secondary Education major in Mathematics", "Bachelor of Secondary Education major in Science"],
            "School of Criminal Justice": ["Bachelor of Science in Criminology"],
            "School of Tourism and Hospitality Management": ["Bachelor of Science in Hospitality Management", "Bachelor of Science in Tourism Management"],
            "School of Business and Accountancy": ["Bachelor of Science in Accountancy", "Bachelor of Science in Accounting Information System", "Bachelor of Science in Business Administration major in Financial Management", "Bachelor of Science in Business Administration major in Marketing Management"]
        };

        const departmentSelect = document.getElementById('departmentSelect');
        const categorySelect = document.getElementById('categorySelect');

        departmentSelect.addEventListener('change', function () {
            const selectedDept = this.value;
            const options = programs[selectedDept] || [];
            categorySelect.innerHTML = '<option selected disabled value="">Choose...</option>';
            options.forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                categorySelect.appendChild(option);
            });
        });

        // PDF handling
        const pdfFileInput = document.getElementById('pdfFile');
        const pdfTextArea = document.getElementById('pdfText');
        const submitBtn = document.getElementById('submitBtn');
        const filenameWarning = document.getElementById('filename-warning');

        if (typeof pdfjsLib !== 'undefined') {
            pdfFileInput.addEventListener('change', async (event) => {
                filenameWarning.textContent = '';
                submitBtn.disabled = false;
                pdfTextArea.value = '';

                const file = event.target.files[0];
                if (!file) return;

                const fileName = file.name;

                try {
                    const response = await fetch(`{{ route('research-papers.check-filename') }}?filename=${encodeURIComponent(fileName)}`);
                    const data = await response.json();

                    if (data.exists) {
                        filenameWarning.textContent = `You already have a file named "${fileName}". Please rename your file.`;
                        submitBtn.disabled = true;
                        pdfFileInput.value = '';
                        return;
                    }
                } catch (error) {
                    console.error('Error checking filename:', error);
                }

                if (file.type !== 'application/pdf') {
                    filenameWarning.textContent = 'Invalid file type. Only PDF is allowed.';
                    submitBtn.disabled = true;
                    return;
                }

                try {
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                    let fullText = '';
                    
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const textContent = await page.getTextContent();
                        const pageText = textContent.items.map(item => item.str).join(' ');
                        fullText += pageText + '\n\n';
                    }
                    
                    pdfTextArea.value = fullText;
                } catch (error) {
                    console.error('PDF text extraction error:', error);
                    filenameWarning.textContent = 'Could not read text from this PDF.';
                }
            });
        } else {
            console.error("pdf.js is not loaded.");
        }
    </script>
 
</x-userlayout>