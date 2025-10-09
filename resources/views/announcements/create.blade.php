<x-userlayout>
    <!-- Page Header -->
  
    <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Create Announcement</h2>
    </div>
   
  </div>
</div>

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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tier</label>
                <select name="tier"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                        required>
                    <option value="URGENT"    {{ old('tier')==='URGENT' ? 'selected' : '' }}>URGENT (red)</option>
                    <option value="IMPORTANT" {{ old('tier')==='IMPORTANT' ? 'selected' : '' }}>IMPORTANT (amber)</option>
                    <option value="GENERAL"   {{ old('tier','GENERAL')==='GENERAL' ? 'selected' : '' }}>GENERAL (blue)</option>
                </select>
                @error('tier') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Audience -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
                <select name="audience"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                        required>
                    <option value="ALL"     {{ old('audience','ALL')==='ALL' ? 'selected' : '' }}>Everyone</option>
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
