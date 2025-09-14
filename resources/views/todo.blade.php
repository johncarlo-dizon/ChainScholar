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









modal design


 No alert(), must use modal UI.
 Blurred overlay, rounded-xl card, same design language as my details modal.
 Title, description, and warning message inside.
 Dynamic form action (set via data-action) and optional item title (set via data-title).
 Hidden form with @csrf and @method('DELETE').
 Cancel button + Delete button.
 Delete button shows a spinner and disables itself on submit.
 Script should be delegated (works across pagination), handle open/close, lock scroll, ESC key, and overlay click.”








The only things left are optional polish (not required for correctness):

Make min_percent consistent between pre-export and post-export (5 vs 20).

Drop unused code that still builds/cleans source_excerpt behind the scenes.

Standardize your log channels if you want cleaner debugging.