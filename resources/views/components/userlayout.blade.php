<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    @vite('resources/css/app.css')
   
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/96x96.png" sizes="96x96">
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



</body>
</html>

