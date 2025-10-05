<x-layout>
 <form id="loginForm" action="{{route('login')}}" method="POST" autocomplete="off">

  @csrf


  <label for="email">Email:</label>
  <input 
         class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
         placeholder="Email"
    type="email"
    name="email"
    value="{{old('email')}}"
    required
  >

  <label for="password">Password:</label>
  <input 
         class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
         placeholder="Password"
    type="password"
    name="password"
    
    required
  >

 

@if (!empty($verifyIntent))
  <div role="alert" class="my-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900 text-xs">
    <strong>Heads up:</strong> please sign in to complete your email verification.
  </div>
@endif


 <button
  id="submitBtn"
  type="submit"
  class="mt-5 tracking-wide font-semibold text-white-1000 w-full py-4 rounded-lg btn-auth transition-all duration-300 ease-in-out flex items-center justify-center gap-2 focus:shadow-outline focus:outline-none"
  style="color: white;"
>
  <!-- spinner (hidden by default) -->
  <svg id="btnSpinner" class="hidden animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 008 12H4z"></path>
  </svg>
  <span id="btnLabel">Sign In</span>
</button>


         <p id="ariaLoginStatus" class="sr-only" aria-live="polite"></p>



                            <div class="d-flex text-center text-sm text-indigo-400 mt-5">
                <a href="{{route('password.request')}}">Forgot password</a>         |     <a href="{{route('show.register')}}" >Sign up</a> 
                            </div>

                              <!-- validation errors -->
                                @if ($errors->any())
                                <ul class="px-4 py-2 bg-red-100 rounded-lg mt-5 text-center">
                                  @foreach ($errors->all() as $error)
                                    <li class="my-2 text-red-500">{{ $error }}</li>
                                  @endforeach
                                </ul>
                              @endif
                            
                          </form>

                        <p class="mt-6 text-xs text-gray-600 text-center">
                            I agree to abide by ChainScholar
                            <a href="#" class="border-b border-gray-500 border-dotted">
                                Terms of Service
                            </a>
                            and its
                            <a href="#" class="border-b border-gray-500 border-dotted">
                                Privacy Policy
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>



        <style>
  @keyframes cs-progress {
    0%   { transform: translateX(-100%); }
    50%  { transform: translateX(0%); }
    100% { transform: translateX(100%); }
  }
  @media (prefers-reduced-motion: reduce) {
    #pageLoader *, #pageProgress * { animation: none !important; transition: none !important; }
  }
</style>

        <script>
(function () {
  const form    = document.getElementById('loginForm');
  const btn     = document.getElementById('submitBtn');
  const spinner = document.getElementById('btnSpinner');
  const label   = document.getElementById('btnLabel');
  const live    = document.getElementById('ariaLoginStatus');

  if (!form || !btn || !spinner || !label) return;

  // --- Top indeterminate progress bar ---
  const progress = document.createElement('div');
  progress.id = 'pageProgress';
  progress.className = 'fixed left-0 top-0 h-1 w-full z-[60] hidden overflow-hidden';
  progress.innerHTML = `
    <div class="h-full bg-gradient-to-r from-indigo-500 via-blue-500 to-indigo-500"
         style="width:40%; animation: cs-progress 1.15s ease-in-out infinite;"></div>`;
  document.body.appendChild(progress);

  // --- Center overlay card (subtle, branded, accessible) ---
  const overlay = document.createElement('div');
  overlay.id = 'pageLoader';
  overlay.className = 'fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[55] hidden';
  overlay.innerHTML = `
    <div class="bg-white/95 rounded-2xl shadow-2xl px-6 py-5 w-[320px] text-center">
      <div class="mx-auto mb-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
        <svg class="h-5 w-5 text-indigo-600 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 008 12H4z"></path>
        </svg>
      </div>
      <div class="font-semibold text-gray-800">Signing you in…</div>
      <div class="mt-1 text-xs text-gray-500">Verifying credentials. Please wait.</div>
    </div>`;
  document.body.appendChild(overlay);

  let submitted = false;
  let overlayTimer = null;

  form.addEventListener('submit', function (e) {
    if (submitted) { e.preventDefault(); return; }
    submitted = true;

    // Button state
    btn.setAttribute('disabled', 'disabled');
    btn.classList.add('cursor-not-allowed', 'opacity-80');
    spinner.classList.remove('hidden');
    label.textContent = 'Signing in…';

    // Keep inputs submittable but prevent edits (don’t touch hidden/_token)
    Array.from(form.querySelectorAll('input:not([type="hidden"]):not([name="_token"]), textarea'))
      .forEach(el => el.setAttribute('readonly', 'readonly'));
    Array.from(form.querySelectorAll('select, button'))
      .forEach(el => el.setAttribute('disabled', 'disabled'));
    Array.from(form.querySelectorAll('input, select, textarea'))
      .forEach(el => el.classList.add('opacity-60'));

    // Accessibility
    form.setAttribute('aria-busy', 'true');
    if (live) live.textContent = 'Signing you in, please wait.';

    // Avoid flashing overlay on ultra-fast redirects: show after a short delay
    overlayTimer = setTimeout(() => {
      progress.classList.remove('hidden');
      overlay.classList.remove('hidden');
    }, 180);
  });

  // If the browser restores the page from the back-forward cache, reset UI
  window.addEventListener('pageshow', (evt) => {
    if (evt.persisted) {
      clearTimeout(overlayTimer);
      submitted = false;
      btn.disabled = false;
      spinner.classList.add('hidden');
      label.textContent = 'Sign In';
      progress.classList.add('hidden');
      overlay.classList.add('hidden');
      form.removeAttribute('aria-busy');
      if (live) live.textContent = '';
      Array.from(form.querySelectorAll('input, select, textarea')).forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
        el.classList.remove('opacity-60');
      });
    }
  });

  // If client-side HTML5 validation fails, cancel loading UI immediately
  form.addEventListener('invalid', () => {
    clearTimeout(overlayTimer);
    submitted = false;
    btn.disabled = false;
    spinner.classList.add('hidden');
    label.textContent = 'Sign In';
    progress.classList.add('hidden');
    overlay.classList.add('hidden');
    form.removeAttribute('aria-busy');
    if (live) live.textContent = '';
  }, true);

  // Guard against Enter-key spam after first submit
  form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && submitted) e.preventDefault();
  });
})();
</script>


</x-layout>


       