<x-layout>
 





        <h1 class="text-2xl font-bold text-gray-500 mb-4 text-center mt-2">Verify Your Email Address</h1>
        
        <p class="mb-6 text-sm text-center">
            Before proceeding, please check your email for a verification link.
            If you did not receive the email,
        </p>
        
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="mt-5 tracking-wide font-semibold  text-white-1000 w-full py-4 rounded-lg  btn-auth transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none" style="color: white;">
                Resend Verification Email
            </button>
        </form>
             <div class="text-center text-sm mt-5">


               <form action="{{route('logout')}}" method="POST" class="m-0">
            @csrf
            Already have an account?   <button class="ml-1 font-medium text-blue-500 hover:text-blue-700">Sign In</button>
            </form>

              </div>
        @if (session('message'))
            <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif
                       
                    </div>
                </div>
            </div>
        </div>
        
</x-layout>