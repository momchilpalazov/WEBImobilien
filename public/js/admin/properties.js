import { ImageUpload } from './modules/ImageUpload.js';
import { Geocoding } from './modules/Geocoding.js';
import { RichTextEditor } from './modules/RichTextEditor.js';

// Image Upload and Preview
class PropertyImageUpload {
    constructor(options = {}) {
        this.dropZone = document.getElementById(options.dropZoneId || 'images');
        this.previewContainer = document.getElementById(options.previewContainerId || 'image-preview');
        this.maxFiles = options.maxFiles || 10;
        this.maxFileSize = options.maxFileSize || 5 * 1024 * 1024; // 5MB
        this.allowedTypes = options.allowedTypes || ['image/jpeg', 'image/png', 'image/webp'];
        
        this.init();
        this.initSortable();
    }

    init() {
        if (!this.dropZone || !this.previewContainer) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => {
                this.dropZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => {
                this.dropZone.classList.remove('drag-over');
            });
        });

        this.dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        });

        this.dropZone.addEventListener('change', (e) => {
            const files = e.target.files;
            this.handleFiles(files);
        });
    }

    initSortable() {
        if (!this.previewContainer) return;

        // Initialize Sortable
        new Sortable(this.previewContainer, {
            animation: 150,
            handle: '.img-thumbnail',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: (evt) => {
                this.updateImageOrder();
            }
        });

        // Add drag handles and order inputs to existing images
        this.previewContainer.querySelectorAll('.image-preview-item').forEach((item, index) => {
            this.addOrderInput(item, index);
            this.addDragHandle(item);
        });
    }

    handleFiles(files) {
        if (!files || !files.length) return;

        // Convert FileList to Array and filter valid files
        const validFiles = Array.from(files).filter(file => {
            if (!this.allowedTypes.includes(file.type)) {
                this.showError(`File type ${file.type} is not allowed`);
                return false;
            }
            if (file.size > this.maxFileSize) {
                this.showError(`File ${file.name} is too large (max ${this.maxFileSize / 1024 / 1024}MB)`);
                return false;
            }
            return true;
        });

        // Check total number of files
        const totalFiles = this.previewContainer.querySelectorAll('.image-preview-item').length + validFiles.length;
        if (totalFiles > this.maxFiles) {
            this.showError(`Maximum ${this.maxFiles} files allowed`);
            return;
        }

        validFiles.forEach(file => this.previewFile(file));
    }

    previewFile(file) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        
        reader.onload = () => {
            const preview = document.createElement('div');
            preview.className = 'col-md-2 mb-3 image-preview-item';
            preview.innerHTML = `
                <div class="position-relative">
                    <img src="${reader.result}" class="img-thumbnail" alt="${file.name}">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                            onclick="this.closest('.image-preview-item').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="drag-handle position-absolute bottom-0 start-0 p-2">
                        <i class="fas fa-grip-vertical text-muted"></i>
                    </div>
                </div>
            `;

            this.previewContainer.appendChild(preview);
            this.addOrderInput(preview, this.previewContainer.children.length - 1);
        };
    }

    addOrderInput(item, index) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'image_order[]';
        input.value = index;
        item.appendChild(input);
    }

    addDragHandle(item) {
        const handle = document.createElement('div');
        handle.className = 'drag-handle position-absolute bottom-0 start-0 p-2';
        handle.innerHTML = '<i class="fas fa-grip-vertical text-muted"></i>';
        item.querySelector('.position-relative').appendChild(handle);
    }

    updateImageOrder() {
        this.previewContainer.querySelectorAll('.image-preview-item').forEach((item, index) => {
            const input = item.querySelector('input[name="image_order[]"]');
            if (input) {
                input.value = index;
            }
        });

        // Update main image checkbox
        const firstItem = this.previewContainer.querySelector('.image-preview-item');
        if (firstItem) {
            const mainImageInput = firstItem.querySelector('input[name="main_image"]');
            if (!mainImageInput) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'main_image';
                input.value = '1';
                firstItem.appendChild(input);
            }
        }
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        this.dropZone.parentNode.insertBefore(errorDiv, this.dropZone.nextSibling);
    }
}

// HERE Maps Integration
class PropertyGeocoding {
    constructor(options = {}) {
        this.addressInput = document.getElementById(options.addressInputId || 'address');
        this.searchButton = document.getElementById(options.searchButtonId || 'searchAddress');
        this.latInput = document.getElementById(options.latInputId || 'lat');
        this.lngInput = document.getElementById(options.lngInputId || 'lng');
        this.mapContainer = document.getElementById(options.mapContainerId || 'map-container');
        
        // HERE Maps API credentials
        this.apiKey = options.apiKey || 'YOUR_HERE_API_KEY';
        
        this.platform = new H.service.Platform({
            apikey: this.apiKey
        });
        
        this.init();
    }

    init() {
        if (!this.addressInput || !this.latInput || !this.lngInput || !this.mapContainer) return;

        // Initialize map
        const defaultLayers = this.platform.createDefaultLayers();
        this.map = new H.Map(
            this.mapContainer,
            defaultLayers.vector.normal.map,
            {
                zoom: 15,
                center: { lat: 42.6977, lng: 23.3219 } // Default center (Sofia)
            }
        );

        // Enable map interaction
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(this.map));
        
        // Add UI components
        const ui = H.ui.UI.createDefault(this.map, defaultLayers);
        
        // Create marker
        this.marker = null;

        // Add event listeners
        this.searchButton.addEventListener('click', () => this.geocodeAddress());
        this.map.addEventListener('tap', (evt) => {
            const coord = this.map.screenToGeo(evt.currentPointer.viewportX, evt.currentPointer.viewportY);
            this.updateMarker(coord);
            this.reverseGeocode(coord);
        });

        // Initialize with existing coordinates if available
        if (this.latInput.value && this.lngInput.value) {
            const coord = {
                lat: parseFloat(this.latInput.value),
                lng: parseFloat(this.lngInput.value)
            };
            this.updateMarker(coord);
            this.map.setCenter(coord);
        }
    }

    async geocodeAddress() {
        const address = this.addressInput.value;
        if (!address) return;

        const geocoder = this.platform.getSearchService();
        
        try {
            const result = await new Promise((resolve, reject) => {
                geocoder.geocode({
                    q: address
                }, (result) => {
                    resolve(result);
                }, (error) => {
                    reject(error);
                });
            });

            if (result.items && result.items.length > 0) {
                const location = result.items[0].position;
                this.updateMarker(location);
                this.map.setCenter(location);
                this.map.setZoom(15);
                
                this.latInput.value = location.lat;
                this.lngInput.value = location.lng;
            }
        } catch (error) {
            console.error('Geocoding failed:', error);
            this.showError('Address not found');
        }
    }

    async reverseGeocode(coord) {
        const reverseGeocodingParameters = {
            at: `${coord.lat},${coord.lng}`
        };

        const geocoder = this.platform.getSearchService();
        
        try {
            const result = await new Promise((resolve, reject) => {
                geocoder.reverseGeocode(
                    reverseGeocodingParameters,
                    (result) => {
                        resolve(result);
                    },
                    (error) => {
                        reject(error);
                    }
                );
            });

            if (result.items && result.items.length > 0) {
                const address = result.items[0].address;
                this.addressInput.value = address.label;
            }
        } catch (error) {
            console.error('Reverse geocoding failed:', error);
        }
    }

    updateMarker(coord) {
        if (this.marker) {
            this.map.removeObject(this.marker);
        }
        
        this.marker = new H.map.Marker(coord);
        this.map.addObject(this.marker);
        
        this.latInput.value = coord.lat;
        this.lngInput.value = coord.lng;
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        this.addressInput.parentNode.insertBefore(errorDiv, this.addressInput.nextSibling);
    }
}

// Rich Text Editor
class PropertyDescription {
    constructor() {
        this.init();
    }

    init() {
        tinymce.init({
            selector: 'textarea[id^="description_"]',
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
            language: 'en',
            images_upload_url: '/admin/upload-image',
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: (callback, value, meta) => {
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
            },
            setup: (editor) => {
                // Add custom buttons or event handlers here
                editor.on('change', () => {
                    editor.save(); // Ensure form submission includes editor content
                });
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize image upload
    new ImageUpload({
        dropZoneId: 'images',
        previewContainerId: 'image-preview'
    });

    // Initialize HERE Maps
    new Geocoding({
        apiKey: 'YOUR_HERE_API_KEY'
    });

    // Initialize rich text editor
    new RichTextEditor({
        language: document.documentElement.lang || 'en'
    });
}); 