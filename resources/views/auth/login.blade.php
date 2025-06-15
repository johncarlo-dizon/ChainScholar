<x-layout>
  <form action="{{route('login')}}" method="POST">
  @csrf


  <label for="email">Email:</label>
  <input 
         class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
         placeholder="Email"
    type="email"
    name="email"
    value="{{old('email')}}"
    required
  >

  <label for="password">Password:</label>
  <input 
         class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
         placeholder="Password"
    type="password"
    name="password"
    
    required
  >

  <button type="submit"  class="mt-5 tracking-wide font-semibold   text-white-1000 w-full py-4 rounded-lg btn-auth transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none" style="color: white;">    
                                Sign In
                           </button>

         


                            <div class="d-flex text-center text-sm text-indigo-400 mt-5">
                <a href="{{route('password.request')}}">Forgot password</a>         |     <a href="{{route('show.register')}}" >Sign up</a> 
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

                        <p class="mt-6 text-xs text-gray-600 text-center">
                            I agree to abide by ChainScholar
                            <a href="#" class="border-b border-gray-500 border-dotted">
                                Terms of Service
                            </a>
                            and its
                            <a href="#" class="border-b border-gray-500 border-dotted">
                                Privacy Policy
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
</x-layout>


       