<x-userlayout>


 
<x-header.bar
  title="Users Management"
  subtitle="Manage all user accounts and permissions"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>


   


    <div class="bg-white rounded-lg shadow p-6 mt-3">
     
     {{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
  <div class="flex flex-col md:flex-row md:items-end gap-3">
    <div class="w-full md:w-1/4">
      <label class="block text-sm font-medium text-gray-700 mb-1" for="q">Search</label>
      <input
        type="text"
        id="q"
        name="q"
        value="{{ $filters['q'] ?? '' }}"
        placeholder="Search by name, email, or ID…"
        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-400"
      >
    </div>

    <div class="w-full md:w-1/4">
      <label class="block text-sm font-medium text-gray-700 mb-1" for="role">Role</label>
      <select
        id="role"
        name="role"
        class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-400"
      >
        @php $selectedRole = $filters['role'] ?? ''; @endphp
        <option value="">All</option>
        @foreach($roles as $r)
          <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>
            {{ $r }}
          </option>
        @endforeach
      </select>
    </div>

       <div class="w-full md:w-1/4">
      <label class="block text-sm font-medium text-gray-700 mb-1" for="status">Status</label>
      @php $selectedStatus = $filters['status'] ?? ''; @endphp
      <select
        id="status"
        name="status"
        class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-blue-400"
      >
        <option value="" {{ $selectedStatus === '' ? 'selected' : '' }}>All</option>
        <option value="enabled"  {{ $selectedStatus === 'enabled'  ? 'selected' : '' }}>Enabled</option>
        <option value="disabled" {{ $selectedStatus === 'disabled' ? 'selected' : '' }}>Disabled</option>
      </select>
    </div>

    <div class="flex gap-2">
      <button
        type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
      >Apply</button>
    </div>

</form>

       <div class="flex justify-end">
      <a href="{{ route('admin.users.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Add New User
      </a>
      </div>
  </div>



        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
        {{ $user->role }}
    </span>
</td>

<td class="px-6 py-4 whitespace-nowrap">
    @if($user->is_active)
        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
            Enabled
        </span>
    @else
        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
            Disabled
        </span>
    @endif
</td>

<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">

   <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>

<button
    type="button"
    class="mr-3 js-toggle-status"
    data-action="{{ route('admin.users.toggle', $user) }}"
    data-name="{{ $user->name }}"
    data-current="{{ $user->is_active ? 'enabled' : 'disabled' }}"
>
    @if($user->is_active)
        <span class="text-yellow-600 hover:text-yellow-800">Disable</span>
    @else
        <span class="text-green-600 hover:text-green-800">Enable</span>
    @endif
</button>


<button
    type="button"
    class="text-red-600 hover:text-red-900 js-open-delete"
    data-action="{{ route('admin.users.destroy', $user) }}"
    data-name="{{ $user->name }}"
>
    Delete
</button>




  
</td>

                    </tr>
                    @endforeach
                    @if ($users->isEmpty())
  <tr>
    <td colspan="6" class="px-6 py-6 text-center text-gray-500">

      No users found. Try adjusting your search or role filter.
    </td>
  </tr>
@endif

                </tbody>
            </table>
 

        </div>
{{ $users->appends(request()->query())->links() }}
<!-- Global Delete Modal (single instance) -->



    </div>


<div id="delete-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div id="modal-overlay" class="absolute inset-0 backdrop-blur-sm bg-black/10"></div>
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
    <form id="toggle-form" method="POST" class="hidden">
  @csrf
  @method('PATCH')
</form>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  const modal         = document.getElementById('delete-modal');
  const overlay       = document.getElementById('modal-overlay');
  const cancelBtn     = document.getElementById('cancel-btn');
  const deleteForm    = document.getElementById('delete-form');
  const promptEl      = document.getElementById('delete-prompt');

  function ensureInBody() {
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  }

  function showModalWith(action, name) {
    if (!modal || !deleteForm) {
      console.error('[DeleteModal] Missing modal or form.');
      return;
    }
    ensureInBody();

    if (!action) {
      console.error('[DeleteModal] No data-action on clicked button.');
      return;
    }

    deleteForm.action = action;

    if (promptEl) {
      promptEl.textContent = name
        ? `Are you sure you want to delete “${name}”?`
        : 'Are you sure you want to delete this user?';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.zIndex = '9999';
    cancelBtn && cancelBtn.focus();
  }

  function hideModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function bindHandlers() {
    // Delegate clicks for all current/future delete buttons
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.js-open-delete');
      if (btn) {
        e.preventDefault();
        const action = btn.dataset.action;
        const name   = btn.dataset.name || '';
        console.log('[DeleteModal] Open for:', action, name);
        showModalWith(action, name);
      }
    });

    // Overlay click closes
    overlay && overlay.addEventListener('click', hideModal);
    // Cancel button closes
    cancelBtn && cancelBtn.addEventListener('click', hideModal);
    // ESC closes
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') hideModal();
    });
    // Prevent Enter in form accidentally submitting early
    deleteForm && deleteForm.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') e.preventDefault();
    });
  }

  // Bind on normal load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindHandlers);
  } else {
    bindHandlers();
  }

  // Re-bind on Turbo/Livewire navigation if present
  document.addEventListener('turbo:load', bindHandlers);
  document.addEventListener('livewire:load', bindHandlers);
})();
</script>


<script>
(function () {
  // Confirm Enable/Disable via SweetAlert2
  document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.js-toggle-status');
    if (!btn) return;

    e.preventDefault();

    const action  = btn.getAttribute('data-action');
    const name    = btn.getAttribute('data-name') || 'this user';
    const current = (btn.getAttribute('data-current') || '').toLowerCase(); // 'enabled' | 'disabled'
    const nextOp  = current === 'enabled' ? 'Disable' : 'Enable';

    // SweetAlert2 confirm
    const resp = await Swal.fire({
      title: `${nextOp} account?`,
      html: `<div class="text-left">
               <p>Are you sure you want to <b>${nextOp.toLowerCase()}</b> the account of <b>${name}</b>?</p>
             </div>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: nextOp,
      cancelButtonText: 'Cancel',
      confirmButtonColor: current === 'enabled' ? '#d97706' : '#16a34a', // amber for disable, green for enable
      cancelButtonColor: '#6b7280',
      background: '#ffffff',
      color: '#111827',
    });

    if (!resp.isConfirmed) return;

    // Submit hidden PATCH form
    const form = document.getElementById('toggle-form');
    if (!form) return console.error('[Toggle] Missing #toggle-form');

    form.setAttribute('action', action);
    form.submit();
  });
})();
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