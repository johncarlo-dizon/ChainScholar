@props([
  'title' => 'Dashboard',
  'subtitle' => null,
  'unreadCount' => 0,
  'notifications' => collect(),
  'user' => Auth::user(),
])

<div class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
     style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;">
  <div class="flex items-center justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">{{ $title }}</h2>
      @if($subtitle)
        <p class="text-sm text-white/70">{{ $subtitle }}</p>
      @endif
    </div>

    <div class="flex items-center gap-4">
      <x-header.notification-bell
        :unread-count="$unreadCount"
        :notifications="$notifications" />
      <x-header.user-pill :user="$user" />
    </div>
  </div>
</div>
