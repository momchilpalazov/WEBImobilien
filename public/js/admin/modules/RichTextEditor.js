export class RichTextEditor {
    constructor(options = {}) {
        this.selector = options.selector || 'textarea[id^="description_"]';
        this.uploadUrl = options.uploadUrl || '/admin/upload-image';
        this.language = options.language || 'en';
        
        this.init();
    }

    init() {
        tinymce.init({
            selector: this.selector,
            height: 400,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 14px; }',
            branding: false,
            promotion: false,
            language: this.language,
            images_upload_url: this.uploadUrl,
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: this.handleFilePicker.bind(this),
            setup: this.setupEditor.bind(this)
        });
    }

    handleFilePicker(callback, value, meta) {
        if (meta.filetype === 'image') {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');

            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.readAsDataURL(file);
                
                reader.onload = () => {
                    const id = 'blobid' + (new Date()).getTime();
                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    const base64 = reader.result.split(',')[1];
                    const blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    callback(blobInfo.blobUri(), { title: file.name });
                };
            });

            input.click();
        }
    }

    setupEditor(editor) {
        editor.on('change', () => {
            editor.save(); // Ensure form submission includes editor content
        });
    }
} 