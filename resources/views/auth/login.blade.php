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
     autocomplete="username"
    required
  >

  <label for="password">Password:</label>
  <input 
         class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
         placeholder="Password"
    type="password"
    name="password"
       autocomplete="current-password"
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
  class="mt-5 tracking-wide font-semibold w-full py-4 rounded-lg btn-auth transition-all duration-200 flex items-center justify-center gap-2 focus:shadow-outline focus:outline-none disabled:opacity-70 disabled:cursor-not-allowed"
  style="color: white;"
>
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



  
<script>
(function () {
  const form    = document.getElementById('loginForm');
  const btn     = document.getElementById('submitBtn');
  const spinner = document.getElementById('btnSpinner');
  const label   = document.getElementById('btnLabel');
  const live    = document.getElementById('ariaLoginStatus');

  if (!form || !btn || !spinner || !label) return;

  let submitted = false;

  form.addEventListener('submit', function (e) {
    if (submitted) { e.preventDefault(); return; }
    submitted = true;

    btn.disabled = true;
    spinner.classList.remove('hidden');
    label.textContent = 'Signing in…';
    form.setAttribute('aria-busy', 'true');
    if (live) live.textContent = 'Signing you in, please wait.';

    // keep values but prevent edits
    form.querySelectorAll('input:not([type="hidden"]):not([name="_token"]), textarea')
      .forEach(el => el.setAttribute('readonly', 'readonly'));
    form.querySelectorAll('select, button')
      .forEach(el => { if (el !== btn) el.setAttribute('disabled', 'disabled'); });
  });

  // If HTML5 validation blocks submission, reset light state
  form.addEventListener('invalid', () => {
    submitted = false;
    btn.disabled = false;
    spinner.classList.add('hidden');
    label.textContent = 'Sign In';
    form.removeAttribute('aria-busy');
    if (live) live.textContent = '';
  }, true);

  // Back/forward cache: restore UI if user returns to this page
  window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
      submitted = false;
      btn.disabled = false;
      spinner.classList.add('hidden');
      label.textContent = 'Sign In';
      form.removeAttribute('aria-busy');
      if (live) live.textContent = '';
      form.querySelectorAll('input, select, textarea').forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
      });
    }
  });
})();
</script>




</x-layout>


       