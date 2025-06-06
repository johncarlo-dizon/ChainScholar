<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chain Scholar</title>
  @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
 
<!-- source:https://codepen.io/owaiswiz/pen/jOPvEPB -->
<div class="min-h-screen bg-gray-100 text-gray-900 flex justify-center">
    <div class="max-w-screen-xl m-0 sm:m-10 bg-white shadow sm:rounded-lg flex justify-center flex-1">
        <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
            <div class="mt-10 flex flex-row items-center">
            <img src="{{ asset('storage/images/logo.png') }}" style="width: 100;" alt="">
            <h1 class="text-center text-6xl font-extrabold tracking-wider text-gray-500 ms-3" style="font-size: 40px; font-family: 'Times New Roman', Times, serif;">CHAINSCHOLAR</h1>
            </div>
            <div class="mt-5 flex flex-col items-center">
                <div class="w-full flex-1 mt-2">
               

          

                    <div class="mx-auto max-w-xs">


                      <main class="container">
    {{ $slot }}
  </main>

  
                  


    </div>
</div>


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