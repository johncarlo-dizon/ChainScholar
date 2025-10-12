<x-userlayout>

<x-header.bar
  title="User Profile"
  subtitle="Manage your personal information and account settings"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

{{-- Main Section --}}
<div class="max-w-8xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 mt-10">
    {{-- Profile Info + Avatar Upload --}}
    <div class="bg-white p-6 rounded-xl shadow-lg md:col-span-7 self-start">
    <h3 class="text-xl font-semibold mb-6">Profile Information</h3>
    
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Avatar Section --}}
        <div class="flex flex-col items-center lg:items-start lg:w-1/3">
            <div class="relative mb-3">
              <img id="avatar-preview"
     class="w-28 h-28 rounded-full object-cover object-center border-2 border-gray-200 aspect-square"
     src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/default.png') }}"
     alt="Profile Avatar"
     style="width:112px; height:112px; aspect-ratio:1 / 1; border-radius:50%; object-fit:cover; object-position:center;">

 
            </div>
            
            <div class="text-center lg:text-left">
                <p class="text-sm text-gray-500 mb-1">Profile Picture</p>
                <p class="text-xs text-gray-400">JPG, PNG or GIF. Max 5MB.</p>
            </div>
        </div>

        {{-- User Details Section --}}
        <div class="flex-1 space-y-4">
            {{-- Username Section --}}
            <div class="space-y-2">
                <label class="block text-sm mb-1">USERNAME</label>
                <form id="username-form" action="{{ route('profile.update.username') }}" method="POST">
                    @csrf
                    <div class="flex items-center gap-2">
                        <div class="flex-1 relative">
                            <input type="text" 
                                   name="username" 
                                   id="username-input" 
                                   value="{{ old('username', $user->name) }}"
                                   class="w-full px-3 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                   placeholder="Enter your username" 
                                   required
                                   disabled>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button type="button" 
                                        id="username-edit-btn" 
                                        class="text-gray-400 hover:text-indigo-600 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" 
                                id="username-save-btn" 
                                class="hidden bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition text-sm">
                            Save
                        </button>
                        <button type="button" 
                                id="username-cancel-btn" 
                                class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition text-sm">
                            Cancel
                        </button>
                    </div>
                    @error('username')
                        <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            {{-- Email Section --}}
            <div class="space-y-2">
                <label class="block text-sm mb-1">EMAIL ADDRESS</label>
                <div class="flex items-center gap-2">
                    <input type="email" 
                           value="{{ $user->email }}" 
                           class="flex-1 px-3 py-2 text-gray-500 border border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed"
                           disabled>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                        Verified
                    </span>
                </div>
                <p class="text-xs text-gray-500">Email address cannot be changed</p>
            </div>

            {{-- Avatar Upload Section --}}
            <div class="space-y-2 pt-3 border-t border-gray-200">
                <label class="block text-sm mb-1">UPDATE PROFILE PICTURE</label>
                <form id="avatar-form" action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                       <div class="flex-1">
  <label
    for="avatar-input"
    class="flex items-center justify-center gap-2 w-full border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white/80
           text-gray-500 cursor-pointer transition hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50
           focus-within:ring-2 focus-within:ring-blue-500"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
    </svg>
    <span id="avatar-label" class="text-sm font-medium">Click to upload avatar (PNG, JPG)</span>
  </label>

  <input 
    type="file" 
    id="avatar-input" 
    name="avatar" 
    class="hidden"
    accept="image/*"
    onchange="updateAvatarLabel(this)"
  >

  <p id="avatar-error" class="mt-1 text-sm text-red-600 hidden"></p>
</div>

                        <button type="submit" 
                                id="avatar-submit" 
                                class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition text-sm whitespace-nowrap">
                            Upload Avatar
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Select a new image to update your profile picture</p>
                </form>
            </div>
        </div>
    </div>
</div>

    {{-- Change Password Form --}}
    <div class="bg-white p-6 rounded-xl shadow-lg md:col-span-5 self-start">
        <h3 class="text-xl font-semibold mb-4">Change Password</h3>
        <form id="password-form" action="{{ route('profile.update.password') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">OLD PASSWORD</label>
                <input type="password" name="current_password" placeholder="Enter current password"
                       class="w-full px-4 py-2 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm mb-1">NEW PASSWORD</label>
                <input type="password" name="new_password" placeholder="Enter new password"
                       class="w-full px-4 py-2 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                @error('new_password')
                    <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm mb-1">CONFIRM PASSWORD</label>
                <input type="password" name="new_password_confirmation" placeholder="Confirm new password"
                       class="w-full px-4 py-2 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="justify-end flex">
                <button type="submit" class="w-40 bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Confirmation Modal (ChainScholar pattern) ===== --}}
<div id="confirmation-modal" class="fixed inset-0 hidden z-50">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/40" data-close-modal></div>

  <!-- Centered container -->
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-lg border border-gray-200">
      <!-- Header -->
      <div class="px-5 pt-2 border-b border-gray-200 flex items-center justify-between">
        <h3 id="modal-title" class="text-base font-semibold text-gray-900">Confirm Changes</h3>
        <button type="button" class="text-gray-500 hover:text-gray-700" data-close-modal>✕</button>
      </div>

      <!-- Body -->
      <div class="p-5 space-y-4">
        <p id="modal-message" class="text-sm text-gray-700">
          Are you sure you want to make these changes?
        </p>

        <div class="flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50" data-close-modal>
            Cancel
          </button>
          <button id="modal-confirm" type="button" class="px-3 py-1.5 text-xs rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
            Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function updateAvatarLabel(input) {
  const label = document.getElementById('avatar-label');
  if (input.files && input.files[0]) {
    label.textContent = input.files[0].name;
    label.classList.add('text-gray-800');
  } else {
    label.textContent = 'Click to upload avatar (PNG, JPG)';
    label.classList.remove('text-gray-800');
  }
}
</script>


<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Username edit functionality
const usernameInput = document.getElementById('username-input');
const usernameEditBtn = document.getElementById('username-edit-btn');
const usernameSaveBtn = document.getElementById('username-save-btn');
const usernameCancelBtn = document.getElementById('username-cancel-btn');
const usernameForm = document.getElementById('username-form');

// Initially disable the input
usernameInput.disabled = true;

usernameEditBtn.addEventListener('click', function() {
    // Enable editing
    usernameInput.disabled = false;
    usernameInput.focus();
    usernameInput.classList.remove('bg-gray-50');
    usernameInput.classList.add('bg-white');
    
    // Show save and cancel buttons, hide edit button
    usernameEditBtn.classList.add('hidden');
    usernameSaveBtn.classList.remove('hidden');
    usernameCancelBtn.classList.remove('hidden');
});

// Cancel editing
usernameCancelBtn.addEventListener('click', function() {
    // Reset to original value
    usernameInput.value = "{{ $user->name }}";
    usernameInput.disabled = true;
    usernameInput.classList.remove('bg-white');
    usernameInput.classList.add('bg-gray-50');
    
    // Show edit button, hide save and cancel buttons
    usernameSaveBtn.classList.add('hidden');
    usernameCancelBtn.classList.add('hidden');
    usernameEditBtn.classList.remove('hidden');
});

// Save username with confirmation
usernameForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (usernameInput.value.trim() && usernameInput.value !== "{{ $user->name }}") {
        showConfirmationModal(
            'Update Username',
            'Are you sure you want to update your username to "' + usernameInput.value + '"?',
            () => {
                this.submit();
            }
        );
    }
});

// Image preview functionality
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('avatar-preview');
    const errorElement = document.getElementById('avatar-error');
    
    // Reset error state
    errorElement.classList.add('hidden');
    
    if (file) {
        // Validate file type
        if (!file.type.match('image.*')) {
            errorElement.textContent = 'Please select a valid image file (JPG, PNG, GIF).';
            errorElement.classList.remove('hidden');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            errorElement.textContent = 'Image size must be less than 5MB.';
            errorElement.classList.remove('hidden');
            return;
        }
        
        // Create preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
 

// Avatar form submission with confirmation
document.getElementById('avatar-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('avatar-input');
    if (!fileInput.files.length) {
        Swal.fire({
            icon: 'error',
            title: 'No file selected',
            text: 'Please select an image to upload.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }
    
    showConfirmationModal(
        'Update Avatar',
        'Are you sure you want to change your profile picture?',
        () => {
            // Show loading state
            const submitBtn = document.getElementById('avatar-submit');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Uploading...';
            submitBtn.disabled = true;
            
            // Submit form
            this.submit();
        }
    );
});

// Password form submission with confirmation
document.getElementById('password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    showConfirmationModal(
        'Change Password',
        'Are you sure you want to change your password?',
        () => {
            this.submit();
        }
    );
});
// ===== Confirmation Modal Logic (same pattern as awaiting_admin) =====
function showConfirmationModal(title, message, confirmCallback) {
  const modal = document.getElementById('confirmation-modal');
  const titleEl = document.getElementById('modal-title');
  const messageEl = document.getElementById('modal-message');
  const confirmBtn = document.getElementById('modal-confirm');

  // Ensure modal is directly under <body> like the reference
  if (modal && modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }

  // Set content
  titleEl.textContent = title || 'Confirm';
  messageEl.textContent = message || '';

  // Show (reference uses .hidden/.flex toggling on the root)
  modal.classList.remove('hidden');

  // lock scroll (same as reference pages)
  document.body.classList.add('overflow-hidden');

  const closeModal = () => {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    confirmBtn.removeEventListener('click', onConfirm);
    document.removeEventListener('keydown', onEsc);
    // remove all close listeners added below
    modal.querySelectorAll('[data-close-modal]').forEach(btn => btn.removeEventListener('click', closeModal));
  };

  const onConfirm = () => {
    closeModal();
    if (typeof confirmCallback === 'function') confirmCallback();
  };

  const onEsc = (e) => { if (e.key === 'Escape') closeModal(); };

  // Attach listeners
  confirmBtn.addEventListener('click', onConfirm);
  modal.querySelectorAll('[data-close-modal]').forEach(btn => btn.addEventListener('click', closeModal));
  document.addEventListener('keydown', onEsc);
}


// Success notifications
@if (session('success'))
    Swal.fire({
        icon: 'success',
        title: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        position: 'top-end'
    });
@endif
</script>

</x-userlayout>