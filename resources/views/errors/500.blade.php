<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>500 – Server Error · ChainScholar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .oops{font-weight:900;letter-spacing:1px;background:
      radial-gradient(2px 2px at 20% 30%, #fff 98%, transparent 100%),
      radial-gradient(2px 2px at 80% 20%, #fff 98%, transparent 100%),
      radial-gradient(1.5px 1.5px at 60% 70%, #fff 98%, transparent 100%),
      conic-gradient(from 180deg at 50% 50%, #7c3aed, #2563eb, #0ea5e9, #9333ea, #7c3aed);
      -webkit-background-clip:text;background-clip:text;color:transparent;}
    .brand{font-weight:900;letter-spacing:.5px;}
    .brand .ai{color:#D4AF37;}
  </style>
</head>
<body class="min-h-screen bg-white flex items-center justify-center p-6">
  <main class="w-full max-w-2xl text-center">
    <div class="oops text-[96px] md:text-[140px] leading-none mb-3">Oops!</div>
    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">500 — Something went wrong</h1>
    <p class="mt-3 text-slate-600 text-sm md:text-base">
      Our servers hit a snag. Please try again in a moment.
    </p>

    @php $homeUrl = auth()->check() ? route('dashboard') : url('/'); @endphp
    <div class="mt-7 flex items-center justify-center gap-3">
      <a href="{{ $homeUrl }}" class="rounded-full bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 shadow">Go to Homepage</a>
    </div>

    <div class="mt-8 brand text-3xl md:text-4xl tracking-tight text-slate-900">
      Ch<span class="ai">AI</span>nScholar
    </div>
  </main>
</body>
</html>
