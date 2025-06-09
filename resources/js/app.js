import './bootstrap';
import { createApp } from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

// Create Vue application
const app = createApp({
    data() {
        return {
            editor: ClassicEditor,
            editorConfig: {
              toolbar: [
                'heading', '|', 
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                'alignment', '|',
                'numberedList', 'bulletedList', '|',
                'outdent', 'indent', '|',
                'link', 'imageUpload', 'blockQuote', 'insertTable', '|',
                'undo', 'redo'
            ],
                simpleUpload: {
                    uploadUrl: '/upload-image',
                    withCredentials: true,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }
            }
        };
    }
});

// Use CKEditor plugin
app.use(CKEditor);

// Mount the application
app.mount('#app');