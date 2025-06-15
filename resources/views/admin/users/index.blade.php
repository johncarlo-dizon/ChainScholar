<x-userlayout>


 
        <div class="bg-blue-600 rounded-lg shadow p-6 mb-4">
            <h2 class="text-2xl text-white font-semibold mb-3">User Management</h2>
      
        </div>

      <div class="flex justify-end">
      <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add New User
      </a>
      </div>


    <div class="bg-white rounded-lg shadow p-6 mt-3">
     
     

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $user->position }}
                            </span>
                        </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> 
    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>

    <!-- Delete button triggers modal -->
    <div x-data="{ open: false }" class="inline-block">
        <button
            type="button"
            class="text-red-600 hover:text-red-900" 
            onclick="showModal({{ $user->id }})"
        >
            
            Delete
        </button>
    </div>

    <!-- Modal -->
    <div id="delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
        <!-- The blur overlay -->
        <div id="modal-overlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
            <p class="text-lg font-semibold mb-5 text-gray-900">Are you sure you want to delete this user?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-btn"
                    class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

  
</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
 

        </div>
        {{ $users->links() }}

    </div>


    
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
        const modal = document.getElementById('delete-modal');
        const overlay = document.getElementById('modal-overlay');
        const cancelBtn = document.getElementById('cancel-btn');
        const deleteForm = document.getElementById('delete-form');

        // Show modal and set form action dynamically
        function showModal(userId) {
            deleteForm.action = `/admin/users/${userId}`; // Match your route
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Hide modal helper
        function hideModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Cancel button closes modal
        cancelBtn.addEventListener('click', hideModal);

        // Clicking outside modal closes modal
        overlay.addEventListener('click', hideModal);
    </script>
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