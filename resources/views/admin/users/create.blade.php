<x-userlayout> 

 <div class="bg-blue-600 rounded-lg shadow p-6">
           <h2 class="text-2xl text-white font-semibold mb-4">Create New User</h2>
            <p class="text-gray-600"> </p>
        </div> 



 <div class="flex justify-center">

    <div class="bg-white rounded-lg shadow p-6 mt-6 w-11/12 ">
      
        
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-600 text-sm   font-bold mb-2">Username</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter username">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-600 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter email">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-600 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" required
                    class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter password">
            </div>
            
            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-600 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Confirm password">
            </div>
            
            <div class="mb-4">
                <label for="position" class="block text-gray-600 text-sm font-bold mb-2">Position</label>
                <select name="position" id="position" required
                    class="shadow appearance-none border border-white rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="" disabled selected>Select position</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            @if ($errors->any())
                <ul class="px-4 py-2 bg-red-100 rounded-lg mt-5 text-center">
                    @foreach ($errors->all() as $error)
                        <li class="my-2 text-red-500">{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create User
                </button>
            </div>
        </form>
    </div>
 </div>




</x-userlayout>
