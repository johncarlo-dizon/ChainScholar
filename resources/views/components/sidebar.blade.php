@props(['highlight' => false])


    <!-- Sidebar -->
  <div class="w-64 bg-white shadow-lg flex flex-col">
        <!-- Logo/Brand -->
    <div class="p-4 flex items-center space-x-3">
    <!-- User Avatar (can be replaced with actual user image) -->
 
    <img class="w-20 h-20 rounded-full shadow-sm object-cover flex items-center justify-center"
                     src="{{  Auth::user()->avatar ? asset('storage/avatars/' .  Auth::user()->avatar) : asset('storage/avatars/default.png') }}"
                     alt="Avatar">

    
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
            <a href="{{ route('admin.index') }}" 
                class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                <i data-feather="home" class="w-4 h-4 mr-3"></i>
                Dashboard
            </a>
            </li>
            <li>
               <a href="{{ route('admin.users.index') }}" 
                class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('admin.users.index','admin.users.create','admin.users.store','admin.users.edit','admin.users.update','admin.users.destroy') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                <i data-feather="users" class="w-4 h-4 mr-3"></i>
                Users
            </a>
             <li>
                    <a href="{{route('templates.index')}}" class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="file-text" class="mr-3 w-4 h-4 "></i>
                        Templates
                    </a>
                </li>
            @endif





            @if(auth()->user()->position === 'user')
                <li>
                <a href="{{ route('show.dashboard') }}" 
                    class="flex items-center p-2 rounded-lg transition text-sm hidden {{ request()->routeIs('show.dashboard') ? ' bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                    <i data-feather="home" class="w-4 h-4 mr-3"></i>
                    Dashboard
                </a>
                      </li>
                <li>
                    <a href="{{route('titles.verify')}}"  class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('documents.create','templates.use','titles.verify','titles.chapters') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="edit-3" class="mr-3 w-4 h-4 "></i>
                        Create
                    </a>
                </li>
           
                      <li>
                    <a href="{{route('titles.index')}}" class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('titles.index','documents.show','documents.edit','open.chapters','templates.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="folder" class="mr-3 w-4 h-4 "></i>
                        Titles
                    </a>
                </li>

                       <li>
                    <a href="{{route('submitted_documents.index')}}" class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('submitted_documents.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="folder" class="mr-3 w-4 h-4 "></i>
                        Submitted Documents
                    </a>
                </li>

                <li>
                <a href="{{ route('research-papers.create') }}" 
                class="flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('research-papers.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                    <i data-feather="file-text" class="mr-3 w-4 h-4"></i>
                    Research Papers
                </a>
            </li>
             

                <li>
                    <a href="{{route('templates.index')}}" class="hidden flex items-center p-2 rounded-lg transition text-sm {{ request()->routeIs('templates.index','templates.create','templates.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="file-text" class="mr-3 w-4 h-4 "></i>
                        Templates
                    </a>
                </li>

                <li>
                    <a href="{{route('show.verify')}}" class="hidden flex items-center p-2 rounded-lg transition text-sm  {{ request()->routeIs('show.verify') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
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
                  <!-- Notification Toggle Button -->
<a id="toggleNotification"
   class="flex items-center gap-2 p-2 text-gray-700 text-sm hover:bg-indigo-50 rounded-lg transition cursor-pointer relative"
>
    <div class="relative">
        <i data-feather="bell" class="w-5 h-5 transition-all" id="notificationIcon"></i>

        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-400 rounded-full leading-none shadow">
                {{ $unreadCount }}
            </span>
        @endif
    </div>
    <span>Notification</span>
</a>




                </li>
           


                 <li>
                    <a href="{{route('profile.show')}}" class=" flex items-center p-2 rounded-lg transition text-sm  {{ request()->routeIs('profile.show') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-indigo-50' }}">
                        <i data-feather="user" class="mr-3 w-4 h-4 "></i>
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


<!-- Notification Panel -->
<div id="notificationPanel" class="hidden absolute top-4 left-68 w-96 bg-white shadow-lg rounded-xl p-6 z-2000 h-200">
    <div class="space-y-3 max-h-180 overflow-y-auto">
        <div class="font-semibold text-lg my-0">Updates</div>
        <div class="text-sm mb-2 py-2 text-gray-500">Click notification to mark as read.</div>


        @forelse($notifications as $notif)
            <div class="bg-white border rounded-lg p-3 hover:bg-indigo-50 transition cursor-pointer"
                 onclick="markAsRead({{ $notif->id }}, this)">
                <h4 class="font-semibold text-sm {{ $notif->is_read ? 'text-gray-600' : 'text-indigo-600' }}">
                    {{ $notif->title }}
                </h4>
                <p class="text-xs text-gray-600">{{ $notif->message }}</p>
                <span class="text-[10px] text-gray-400">
                    {{ $notif->created_at->diffForHumans() }}
                </span>
            </div>
        @empty
            <div class="text-center space-y-2 text-gray-500">
                <div class="flex justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01M19 13a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold">Nothing to see here (yet)!</h3>
                <p class="text-xs">You have no notifications at the moment.</p>
            </div>
        @endforelse
    </div>
</div>




<script>
    const toggleBtn = document.getElementById('toggleNotification');
    const panel = document.getElementById('notificationPanel');
    const icon = document.getElementById('notificationIcon');

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isHidden = panel.classList.contains('hidden');
        panel.classList.toggle('hidden');

        // Toggle active styles
        if (isHidden) {
            toggleBtn.classList.add('bg-indigo-100', 'text-indigo-600');
            icon.classList.add('text-indigo-600');
        } else {
            toggleBtn.classList.remove('bg-indigo-100', 'text-indigo-600');
            icon.classList.remove('text-indigo-600');
        }
    });

    document.addEventListener('click', function (event) {
        if (!panel.contains(event.target) && !toggleBtn.contains(event.target)) {
            panel.classList.add('hidden');
            toggleBtn.classList.remove('bg-indigo-100', 'text-indigo-600');
            icon.classList.remove('text-indigo-600');
        }
    });

    
</script>

<script>
    function markAsRead(id, element) {
        fetch(`/notifications/read/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(res => {
            if (res.ok) {
                element.querySelector('h4').classList.remove('text-indigo-600');
                element.querySelector('h4').classList.add('text-gray-600');
                location.reload(); // Optional: reload to update unread badge
            }
        });
    }
</script>


