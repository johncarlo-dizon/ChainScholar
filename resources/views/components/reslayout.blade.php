<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    @vite('resources/css/app.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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