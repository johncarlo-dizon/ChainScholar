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
            <h1 class="text-center text-6xl font-extrabold tracking-wider text-gray-700 ms-3" style="font-size: 40px; font-family: 'Times New Roman', Times, serif;">CHAINSCHOLAR</h1>
            </div>
            <div class="mt-5 flex flex-col items-center">
                <div class="w-full flex-1 mt-2">
               

          

                    <div class="mx-auto max-w-xs">


                      <main class="container">
    {{ $slot }}
  </main>

<div class="flex-1 bg-gray-100 text-center hidden lg:flex relative overflow-hidden">
    <div class="absolute inset-0 flex transition-transform duration-[3000ms] ease-in-out animate-carousel">
        <div class="w-full flex-shrink-0 bg-contain bg-center bg-no-repeat bg-cover"
             style="background-image: url('{{ asset('storage/images/car2.png') }}')">
        </div>
        <div class="w-full flex-shrink-0 bg-contain bg-center bg-no-repeat bg-cover"
             style="background-image: url('{{ asset('storage/images/car4.png') }}')">
        </div>
        <div class="w-full flex-shrink-0 bg-contain bg-center bg-no-repeat bg-cover"
             style="background-image: url('{{ asset('storage/images/car3.png') }}')">
        </div>
    </div>
</div>

<style>
@keyframes carousel {
    0%, 10% {
        transform: translateX(0%);
    }
    20%, 30% {
        transform: translateX(-100%);
    }
    40%, 50% {
        transform: translateX(-200%);
    }
    60%, 100% {
        transform: translateX(0%);
    }
}

.animate-carousel {
    animation: carousel 12s infinite; /* slower for 3 images */
}
</style>

                  


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