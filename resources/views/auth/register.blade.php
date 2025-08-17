<x-layout>

  <form action="{{route('register')}}" method="POST">
  @csrf
 
 

  <label for="name">Name:</label>
  <input 
 class="w-full px-8 py-3 mb-2  rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
    placeholder="Name"
    type="text"
    name="name"
      value="{{old('name')}}"
    required
  >

  <label for="email">Email:</label>
  <input 
   class="w-full px-8 py-3 mb-2  rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
    placeholder="Email"
    type="email"
    name="email"
      value="{{old('email')}}"
    required
  >

<label for="role">Role:</label>
<select
  id="role"
  name="role"
  required
  class="w-full px-8 py-3 mb-2 rounded-lg font-medium bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
>
  <option value="STUDENT" {{ old('role','STUDENT') === 'STUDENT' ? 'selected' : '' }}>Student</option>
  <option value="ADVISER" {{ old('role') === 'ADVISER' ? 'selected' : '' }}>Adviser</option>

  {{-- Optional: only allow creating ADMIN if a logged-in admin is creating users from this form --}}
  @auth
    @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
      <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>Admin</option>
    @endif
  @endauth
</select>


  <label for="password">Password:</label>
  <input 
   class="w-full px-8 py-3 mb-2 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
    placeholder="Password"
    type="password"
    name="password"
    required
  >

  <label for="password_confirmation">Confirm Password:</label>
  <input 
   class="w-full px-8 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
    placeholder="Confirm Password"
    type="password"
    name="password_confirmation"
    required
  >

  <button type="submit"   class="mt-5 tracking-wide font-semibold  w-full py-4 rounded-lg btn-auth transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none" style="color: white;">Sign up</button>


                   

        <div class="mt-4 text-center text-sm text-gray-400">
            <span>    Already have an account? </span>
            <a href="{{ route('show.login') }}" class="ml-1 font-medium text-blue-500 hover:text-blue-700">
              Sign in
            </a>
        </div>


    <!-- validation errors -->
      @if ($errors->any())
      <ul class="px-4 py-2 bg-red-100 rounded-lg mt-5 text-center">
        @foreach ($errors->all() as $error)
          <li class="my-2 text-red-500">{{ $error }}</li>
        @endforeach
      </ul>
    @endif
</form>
 
                    </div>
                </div>
            </div>
        </div>
        
</x-layout>