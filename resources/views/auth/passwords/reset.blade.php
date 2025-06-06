<x-layout>
    <div class="container">
        <h1 class="text-2xl font-bold text-gray-500 mb-4 text-start mt-2">Reset Password</h1>
        
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email"  class="w-full px-8 py-3 mb-2  rounded-lg font-medium   readonly border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white" value="{{ $email }}" required readonly>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" placeholder="New Password" name="password" id="password"  class="w-full px-8 py-3 mb-2  rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white" required>
           
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" placeholder="Confirm Password" name="password_confirmation" id="password_confirmation"  class="w-full px-8 py-3 mb-2  rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white" required>
            </div>

                 @error('password')
                    <span class="text-danger text-sm text-red-500">{{ $message }}</span>
                @enderror

            <button type="submit" class="mt-5 tracking-wide font-semibold bg-indigo-500 w-full py-4 rounded-lg hover:bg-indigo-400 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none" style="color: white;">Reset Password</button>
        </form>
 </div>
   
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-1 bg-indigo-100 text-center hidden lg:flex">
            <div class="m-12 xl:m-30 w-full bg-contain bg-center bg-no-repeat"
               style="background-image: url('{{ asset('storage/images/forgot.png') }}')">
            </div>
        </div>

</x-layout>