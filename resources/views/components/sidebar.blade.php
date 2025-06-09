@props(['highlight' => false])


    <!-- Sidebar -->
  <div class="w-64 bg-white shadow-lg flex flex-col">
        <!-- Logo/Brand -->
    <div class="p-4 flex items-center space-x-3">
    <!-- User Avatar (can be replaced with actual user image) -->
    <div class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center">
        <!-- First letter of username -->
        <span class="text-gray-600 font-semibold">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </span>
    </div>
    
    <!-- User Info -->
    <div>
        <p class="text-7sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
        <p class="text-xs text-gray-500">Student</p> <!-- Can be dynamic role -->
    </div>
</div>

        <!-- Top Icons Section -->
        <hr class="text-center mx-auto text-gray-200" width="90%">

        <div class="p-4">
        
            <ul class="space-y-2">
        @auth 
            @if(auth()->user()->position === 'admin')
            <li>
            <a href="{{ route('admin.home') }}" 
                class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.home') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                Dashboard
            </a>
            </li>
            <li>
               <a href="{{ route('admin.users.index') }}" 
                class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.users.index','admin.users.create','admin.users.store','admin.users.edit','admin.users.update','admin.users.destroy') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                Users
            </a>
            @endif





            @if(auth()->user()->position === 'users')
                <li>
                <a href="{{ route('show.dashboard') }}" 
                    class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('show.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                    <i data-feather="home" class="w-4 h-4 mr-3"></i>
                    Dashboard
                </a>
                </li>
                      <li>
                    <a href="{{route('documents.index')}}" class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('documents.index','documents.show') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="file-text" class="mr-3 w-4 h-4 "></i>
                        Files
                    </a>
                </li>
                <li>
                    <a href="{{route('documents.create')}}"  class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('documents.create','documents.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="book-open" class="mr-3 w-4 h-4 "></i>
                        Create
                    </a>
                </li>
                <li>
                    <a href="{{route('show.verify')}}" class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('show.verify') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="check" class="mr-3 w-4 h-4 "></i>
                        Verify
                    </a>
                </li>
                @endif
        @endauth
            </ul>
        </div>

        <hr class="text-center mx-auto text-gray-200" width="90%">

        <!-- Navigation Links -->
        <nav class="flex-grow p-4">
            <ul class="space-y-2">
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-700 text-sm hover:bg-indigo-50  rounded-lg transition">
                        <i data-feather="settings" class="mr-3 w-4 h-4"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-700 text-sm hover:bg-indigo-50  rounded-lg transition">
                        <i data-feather="bell" class="w-4 h-4 mr-3"></i>
                        Notification
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-gray-700 text-sm hover:bg-indigo-50  rounded-lg transition">
                        <i data-feather="user" class="w-4 h-4 mr-3"></i>
                        Profile
                    </a>
                </li>
            
            </ul>
        </nav>

        <!-- Bottom Logout Button -->
        
        <hr class="text-center mx-auto text-gray-200" width="90%">
        <div class="p-4 mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center  text-sm w-full p-2 text-gray-700 hover:bg-red-50 rounded-lg transition">
                    <i data-feather="log-out" class="w-4 h-4 mr-3"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>