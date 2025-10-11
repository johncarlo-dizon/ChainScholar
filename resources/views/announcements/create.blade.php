<x-userlayout>
    <!-- Page Header -->
  
   <x-header.bar
  title="Create Announcement"
  subtitle="Post new announcements and updates for users"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

@can('isAdmin')
  <div class="flex justify-end mb-4 gap-2">
    <a
      href="{{ route('announcements.manage') }}"
     class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 transition"
    >
      Manage
    </a>
    
  <a href="{{ route('announcements.index') }}"
     class="inline-flex items-center bg-white text-slate-800 px-4 py-2 rounded-lg shadow border border-gray-200 hover:bg-gray-100 transition">
      View 
  </a>
  </div>
@endcan



    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('announcements.store') }}" class="space-y-5">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                       required>
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Body -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                <textarea name="body" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                          required>{{ old('body') }}</textarea>
                @error('body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Tier -->
      <!-- Tier -->
<div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Tier</label>
  <select name="tier"
          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
          required>
    <option value="" disabled {{ old('tier') ? '' : 'selected' }}>— Select Tier —</option>
    <option value="URGENT"    {{ old('tier')==='URGENT' ? 'selected' : '' }}>URGENT</option>
    <option value="IMPORTANT" {{ old('tier')==='IMPORTANT' ? 'selected' : '' }}>IMPORTANT</option>
    <option value="GENERAL"   {{ old('tier')==='GENERAL' ? 'selected' : '' }}>GENERAL</option>
  </select>
  @error('tier') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<!-- Audience -->
<div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
  <select name="audience"
          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
          required>
    <option value="" disabled {{ old('audience') ? '' : 'selected' }}>— Select Audience —</option>
    <option value="ALL"     {{ old('audience')==='ALL' ? 'selected' : '' }}>Everyone</option>
    <option value="STUDENT" {{ old('audience')==='STUDENT' ? 'selected' : '' }}>Students</option>
    <option value="ADVISER" {{ old('audience')==='ADVISER' ? 'selected' : '' }}>Advisers</option>
    <option value="ADMIN"   {{ old('audience')==='ADMIN' ? 'selected' : '' }}>Admins</option>
  </select>
  @error('audience') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
</div>


            <!-- Event Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date (optional)</label>
                <input type="date" name="event_date" value="{{ old('event_date') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5">
                @error('event_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">
                    Post Announcement
                </button>
            </div>
        </form>
    </div>
</x-userlayout>
