<x-userlayout>
    <!-- Page Header -->
    <div class="bg-blue-600 rounded-lg shadow p-6 mb-6">
        <h1 class="text-3xl font-semibold text-white">Edit Announcement</h1>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" 
                       value="{{ old('title', $announcement->title) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                       required>
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Body -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                <textarea name="body" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                          required>{{ old('body', $announcement->body) }}</textarea>
                @error('body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Tier -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tier</label>
                <select name="tier"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                        required>
                    <option value="URGENT"    {{ old('tier', $announcement->tier)==='URGENT' ? 'selected' : '' }}>URGENT (red)</option>
                    <option value="IMPORTANT" {{ old('tier', $announcement->tier)==='IMPORTANT' ? 'selected' : '' }}>IMPORTANT (amber)</option>
                    <option value="GENERAL"   {{ old('tier', $announcement->tier)==='GENERAL' ? 'selected' : '' }}>GENERAL (blue)</option>
                </select>
                @error('tier') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Audience -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
                <select name="audience"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                        required>
                    <option value="ALL"     {{ old('audience', $announcement->audience)==='ALL' ? 'selected' : '' }}>Everyone</option>
                    <option value="STUDENT" {{ old('audience', $announcement->audience)==='STUDENT' ? 'selected' : '' }}>Students</option>
                    <option value="ADVISER" {{ old('audience', $announcement->audience)==='ADVISER' ? 'selected' : '' }}>Advisers</option>
                    <option value="ADMIN"   {{ old('audience', $announcement->audience)==='ADMIN' ? 'selected' : '' }}>Admins</option>
                </select>
                @error('audience') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Event Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date (optional)</label>
                <input type="date" name="event_date" 
                       value="{{ old('event_date', optional($announcement->event_date)->format('Y-m-d')) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5">
                @error('event_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                    Update Announcement
                </button>
            </div>
        </form>
    </div>
</x-userlayout>
