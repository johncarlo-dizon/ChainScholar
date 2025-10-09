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

            <button type="submit"  class="mt-5 tracking-wide font-semibold w-full py-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 focus:shadow-outline focus:outline-none disabled:opacity-70 disabled:cursor-not-allowed text-white shadow-lg"
  style="background: linear-gradient(to right, #1E293B, #334155, #0F172A);"
    onmouseover="this.style.background='linear-gradient(to right, #334155, #475569, #1E293B)'"
  onmouseout="this.style.background='linear-gradient(to right, #1E293B, #334155, #0F172A)'"
  >Reset Password</button>
        </form>
 </div>
   
                    </div>
                </div>
            </div>
        </div>
        

</x-layout>