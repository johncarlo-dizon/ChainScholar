<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="icon" href="{{ asset('storage/images/chainlogo.png') }}" type="image/png">
    @vite('resources/css/app.css')
   
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


       <!-- <link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/96x96.png" sizes="96x96"> -->

		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/120x120.png" sizes="120x120">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/152x152.png" sizes="152x152">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/167x167.png" sizes="167x167">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/180x180.png" sizes="180x180">
</head>
<body class="bg-gray-50 flex h-screen">


  <x-sidebar>
  </x-sidebar>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-8">
                     <main class="container">
            {{ $slot }}
     
    </main>
   
    </div>

    <!-- Initialize Feather Icons -->
    <script>
        feather.replace();
    </script>



<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('status'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '{{ session("status") }}',
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end'
        });
    </script>
@endif

@if (session('registered'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '{{ session("registered") }}',
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end'
        });
    </script>
@endif


 
<!-- Global Logout Confirmation Modal -->
<div id="globalLogoutModal"
     class="hidden fixed inset-0"
     style="z-index: 99999;">

  <!-- Overlay -->
<div id="globalLogoutOverlay"
     class="absolute inset-0 bg-black/40 backdrop-blur-sm pointer-events-auto"></div>


  <!-- Dialog -->
<div
  class="absolute inset-0 flex items-center justify-center p-4 sm:p-6"
  aria-modal="true"
  role="dialog"
  aria-labelledby="logoutModalTitle"
  aria-describedby="logoutModalDesc"
>


 <div
  class="bg-white rounded-2xl shadow-2xl p-6"
  style="width:100%;max-width:28rem"
>

      <h2 id="logoutModalTitle" class="text-lg font-semibold text-gray-800 mb-2">Confirm Logout</h2>
      <p id="logoutModalDesc" class="text-sm text-gray-600 mb-6">Are you sure you want to log out of your account?</p>

      <div class="flex justify-end gap-3">
        <button
          type="button"
          id="logoutCancelBtn"
          class="px-4 py-2 rounded-lg text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition"
        >Cancel</button>

        <form id="globalLogoutForm" action="{{ route('logout') }}" method="POST">
          @csrf
          <button
            type="submit"
            class="px-4 py-2 rounded-lg text-xs font-medium text-white transition"
            style="background: linear-gradient(to right, #1E293B, #334155, #0F172A);"
            onmouseover="this.style.background='linear-gradient(to right, #334155, #475569, #1E293B)'"
            onmouseout="this.style.background='linear-gradient(to right, #1E293B, #334155, #0F172A)'"
          >Logout</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Global show/hide helpers (available to any component)
(function () {
  const modal   = document.getElementById('globalLogoutModal');
  const overlay = document.getElementById('globalLogoutOverlay');
  const cancel  = document.getElementById('logoutCancelBtn');

  function show() {
    modal?.classList.remove('hidden');
    document.documentElement.classList.add('overflow-hidden');
    document.body.classList.add('overflow-hidden');
  }
  function hide() {
    modal?.classList.add('hidden');
    document.documentElement.classList.remove('overflow-hidden');
    document.body.classList.remove('overflow-hidden');
  }

  window.showLogoutModal = show;
  window.hideLogoutModal = hide;

  overlay?.addEventListener('click', hide);
  cancel?.addEventListener('click', hide);
  document.addEventListener('keydown', (e) => {
    if (!modal || modal.classList.contains('hidden')) return;
    if (e.key === 'Escape') hide();
  });
})();
</script>
@stack('scripts')
</body>
</html>

 
