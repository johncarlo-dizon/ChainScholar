<x-userlayout>

 

        <div
  class="relative overflow-hidden rounded-2xl text-white shadow-lg mb-4"
  style="background: linear-gradient(to bottom right, #1E293B, #334155, #0F172A); padding: 1.5rem 2rem;"
>
  <div class="flex items-start justify-between gap-6">
    <div>
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Update user {{$user->name}}</h2>
    </div>
   
  </div>
</div>


 <div class="flex justify-center">
    <div class="bg-white rounded-lg shadow p-6 mt-6 w-11/12">
       
        
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-gray-500 text-sm font-bold mb-2">Username</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="shadow appearance-none border  border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter username">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-500 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="shadow appearance-none border  border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter email">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-500 text-sm font-bold mb-2">Password (leave blank to keep current)</label>
                <input type="password" name="password" id="password"
                    class="shadow appearance-none border  border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter new password (optional)">
            </div>
            
            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-500 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="shadow appearance-none border  border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Confirm new password">
            </div>
            
          <div class="mb-4">
  <label for="role" class="block text-gray-500 text-sm font-bold mb-2">Role</label>
  <select name="role" id="role" required
      class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      <option value="" disabled>Select role</option>
      <option value="STUDENT" {{ old('role', $user->role) === 'STUDENT' ? 'selected' : '' }}>Student</option>
      <option value="ADVISER" {{ old('role', $user->role) === 'ADVISER' ? 'selected' : '' }}>Adviser</option>
      <option value="ADMIN"   {{ old('role', $user->role) === 'ADMIN'   ? 'selected' : '' }}>Admin</option>
  </select>
</div>


            
            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update User
                </button>
            </div>
        </form>
    </div>
    </div>

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
