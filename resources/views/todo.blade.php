FIX THE MODAL DELETE FOR THE PDF STUDENT INDEX - PDF ADMIN INDEX
NOTIFY ADMIN - USER ---- USER -- ADMIN






fix the manage part also the delete modal part
   <!-- Admin Actions -->
                   @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="flex gap-3 mt-4 hidden">
                            <a href="{{ route('announcements.edit', $announcement) }}"
                               class="bg-yellow-500 text-white px-4 py-1.5 rounded-lg shadow hover:bg-yellow-600 transition">
                                Edit
                            </a>

                            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-600 text-white px-4 py-1.5 rounded-lg shadow hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>



future fix policy for the sidebars ---------- important note