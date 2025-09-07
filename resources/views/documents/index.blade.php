<!--documents.index-->
<x-userlayout>
    <div class="bg-blue-600 rounded-lg shadow p-6">
        <h2 class="text-3xl font-semibold mb-4 text-white">📝 My Draft Titles</h2>
        <p class="text-sm text-blue-100">These are titles you're still working on.</p>
    </div>

    <div class="container mx-auto px-4 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($titles->isEmpty())
                <div class="text-center py-12">
                    <h4 class="text-xl font-semibold mb-2">No draft titles found</h4>
                    <p class="text-gray-600 mb-4">Start by creating or verifying your first title.</p>
                    <a href="{{ route('titles.verify') }}"
                       class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Verify & Create Title
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($titles as $title)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $title->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $title->updated_at->format('M d, Y H:i') }}</td>
                                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> 
                                        <a href="{{ route('open.chapters', $title->id) }}"
                                          class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Open
                                        </a>

                                 <button type="button"
    onclick="showTitleModal({{ $title->id }})"
    class="text-red-600 hover:text-red-900">
    Delete
</button>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

 
<!-- Delete Title Modal -->
<div id="delete-title-modal"
     class="fixed inset-0 hidden items-center justify-center z-50"
     data-url-template="{{ route('titles.destroy', ['id' => '__ID__']) }}">
  <div id="delete-title-overlay" class="absolute inset-0 backdrop-blur-sm bg-transparent"></div>
  <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4 z-10">
      <p class="text-lg font-semibold mb-5 text-gray-900">
          Are you sure you want to delete this title and all its chapters?
      </p>
      <div class="flex justify-end space-x-3">
          <button id="delete-title-cancel"
                  class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
              Cancel
          </button>
          <form id="delete-title-form" method="POST">
              @csrf
              @method('DELETE')
              <button type="submit"
                      class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                  Delete
              </button>
          </form>
      </div>
  </div>
</div>

<script>
  const modalTitle      = document.getElementById('delete-title-modal');
  const overlayTitle    = document.getElementById('delete-title-overlay');
  const cancelBtnTitle  = document.getElementById('delete-title-cancel');
  const deleteFormTitle = document.getElementById('delete-title-form');

  function showTitleModal(titleId) {
    const template = modalTitle.dataset.urlTemplate; // e.g. "/titles/__ID__"
    deleteFormTitle.action = template.replace('__ID__', titleId);

    modalTitle.classList.remove('hidden');
    modalTitle.classList.add('flex');
  }

  function hideTitleModal() {
    modalTitle.classList.add('hidden');
    modalTitle.classList.remove('flex');
  }

  cancelBtnTitle.addEventListener('click', hideTitleModal);
  overlayTitle.addEventListener('click', hideTitleModal);
</script>


</x-userlayout>
