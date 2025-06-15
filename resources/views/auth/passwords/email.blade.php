<x-layout>
    <div class="container">
    <h1 class="text-2xl font-bold text-gray-500 mb-4 text-start mt-2">Forgot Password</h1>
        
  
      @if (session('status'))
                <div class="text-green-500 mb-2 text-sm">
                {{ session('status') }}
            </div>
        @endif
 
 


        <form method="POST" action="{{ route('password.email') }}">
            @csrf
       
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Email" class="w-full px-8 py-3 mb-2  rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white" required>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
   
            <button type="submit" class="mt-5 tracking-wide font-semibold btn-auth  w-full py-4 rounded-lg  transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none" style="color: white;">Send Reset Link</button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-400">
            <span>No longer needed?</span>
            <a href="{{ route('show.login') }}" class="ml-1 font-medium text-blue-500 hover:text-blue-700">
                Go back to login
            </a>
        </div>
     
    </div>
  
                    </div>
                </div>
            </div>
        </div>
        
</x-layout>