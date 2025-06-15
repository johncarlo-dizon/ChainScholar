<x-userlayout>

 

    <div class="bg-blue-600 rounded-lg shadow p-6 max-w-8xl container">
        <h2 class="text-3xl font-semibold mb-4 text-white">User Profile</h2>
           <p class="text-gray-100">Welcome to ChainScholar.</p>
    </div>



    {{-- Main Section --}}
   <div class="max-w-8xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 mt-10  ">
        {{-- Profile Info + Avatar Upload --}}
        <div class="bg-white p-6 rounded-xl shadow-lg md:col-span-7 self-start">
            <h3 class="text-xl font-semibold mb-4">Profile</h3>
            <div class="flex flex-col items-center">
                <img class="w-30 h-30 rounded-full object-cover mb-2 border-2 border-gray-300"
                     src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/default.png') }}"
                     alt="Avatar">

        <form id="username-form" action="{{ route('profile.update.username') }}" method="POST" class="w-full space-y-4">
        @csrf
        <input type="text" name="username" value="{{ old('username', $user->name) }}"
            class="w-full text-center text-gray-700 text-xl px-4 py-1 font-semibold rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="Enter your username"  required onchange="document.getElementById('username-form').submit();">
                        @error('username')
                                    <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
                                @enderror
        </form>

                <p class="text-gray-600 text-sm my-1 mb-5">{{ old('username', $user->email) }}</p>
                {{-- Update Username --}}
              
                     <!-- Update Username Form -->




        <div class="w-full border-t border-gray-200 my-4"></div>


                <form action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data" class="w-full">
                    @csrf
                    <input type="file" name="avatar" class="file:mr-4 file:py-2 bg-gray-50 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-500 hover:file:bg-gray-200 w-full text-sm text-gray-700 rounded-lg mb-4" required>
                    <div class="justify-end flex">
                    <button type="submit" class="w-40 a-end bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition">Change Avatar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Form --}}
        <div class="bg-white p-6 rounded-xl shadow-lg md:col-span-5 self-start">
            <h3 class="text-xl font-semibold mb-4">Change Password</h3>
    

 

            

            <form action="{{ route('profile.update.password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">OLD PASSWORD</label>
                    <input type="password" name="current_password" placeholder="Enter current password"
                           class="w-full px-4 py-2 bg-gray-100   rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                             @error('current_password')
                                <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
                            @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">NEW PASSWORD</label>
                    <input type="password" name="new_password" placeholder="Enter new password"
                           class="w-full px-4 py-2 bg-gray-100  rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                             @error('new_password')
                <p class="mt-1 text-sm text-red-600 bg-red-100 p-2 rounded">{{ $message }}</p>
            @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">CONFIRM PASSWORD</label>
                    <input type="password" name="new_password_confirmation" placeholder="Confirm new password"
                           class="w-full px-4 py-2 bg-gray-100  rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

              <div class="justify-end flex">
                    <button type="submit" class="w-40 bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition">Update</button>
               </div>
            </form>
        </div>
    </div>
<script>
// Add event listener to form submission
document.getElementById('avatar-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    await updateAvatar(this);
});

async function updateAvatar(form) {
    // Show loading indicator if you have one
    const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    });
    
    const result = await response.json();
    
    if (result.success) {
        // Update avatar image on page
        document.getElementById('avatar-img').src = result.avatar_url;
        
        // Show success message (better to use a toast/notification system)
        alert(result.message);
    } else {
        // Handle error
        alert('Error: ' + (result.message || 'Failed to update avatar'));
    }
}
</script>


<script>
let timeout = null;
document.querySelector('input[name="username"]').addEventListener('input', function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        document.getElementById('username-form').submit();
    }, 1000); // wait 1 second after user stops typing
});
</script>


<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end'
        });
    </script>
@endif

</x-userlayout>