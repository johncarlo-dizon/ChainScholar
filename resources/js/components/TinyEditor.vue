<template>
  <div>
    <editor
      v-if="!disabled"
      :init="initOptions"
      v-model="modelValue"
    />
    <div v-else v-html="modelValue" class="disabled-editor"></div>
  </div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue'

export default {
  components: { Editor },
  props: {
    modelValue: String,
    disabled: {
      type: Boolean,
      default: false
    },
    apiKey: {
      type: String,
      default: 'q4ojn7q4jqhfv1h6bdfxwb8ywgs7t4kpw698i7lxkq026qjx' // Get from https://www.tiny.cloud/
    },
    height: {
      type: Number,
      default: 500
    }
  },
  emits: ['update:modelValue'],
  computed: {
    initOptions() {
      return {
        height: this.height,
        menubar: true,
        plugins: [
          'advlist autolink lists link image charmap print preview anchor',
          'searchreplace visualblocks code fullscreen',
          'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline | \
                 alignleft aligncenter alignright alignjustify | \
                 bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px }',
        images_upload_handler: this.uploadImage
      }
    }
  },
  methods: {
    uploadImage(blobInfo, progress) {
      return new Promise((resolve, reject) => {
        const formData = new FormData()
        formData.append('file', blobInfo.blob(), blobInfo.filename())

        axios.post('/api/upload-image', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (e) => {
            progress(e.loaded / e.total * 100)
          }
        })
        .then(response => {
          resolve(response.data.location)
        })
        .catch(error => {
          reject('Image upload failed')
          console.error(error)
        })
      })
    }
  }
}
</script>

<style>
.disabled-editor {
  border: 1px solid #ccc;
  padding: 10px;
  min-height: 200px;
}
</style>