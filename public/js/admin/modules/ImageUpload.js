import { ImageCompression } from './ImageCompression.js';

export class ImageUpload {
    constructor(options = {}) {
        this.dropZone = document.getElementById(options.dropZoneId || 'images');
        this.previewContainer = document.getElementById(options.previewContainerId || 'image-preview');
        this.maxFiles = options.maxFiles || 10;
        this.maxFileSize = options.maxFileSize || 5 * 1024 * 1024; // 5MB
        this.allowedTypes = options.allowedTypes || ['image/jpeg', 'image/png', 'image/webp'];
        
        // Initialize image compression
        this.compression = new ImageCompression({
            maxSizeMB: 1,
            maxWidthOrHeight: 1920,
            useWebWorker: true,
            initialQuality: 0.8
        });
        
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

        this.dropZone.addEventListener('drop', async (e) => {
            const files = e.dataTransfer.files;
            await this.handleFiles(files);
        });

        this.dropZone.addEventListener('change', async (e) => {
            const files = e.target.files;
            await this.handleFiles(files);
        });
    }

    async handleFiles(files) {
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

        // Show loading indicator
        this.showLoading();

        try {
            // Compress images
            const compressedFiles = await this.compression.compressMultiple(validFiles);
            
            // Preview compressed files
            for (let i = 0; i < compressedFiles.length; i++) {
                const originalFile = validFiles[i];
                const compressedFile = compressedFiles[i];
                
                // Get compression stats
                const stats = this.compression.getCompressionStats(originalFile, compressedFile);
                
                await this.previewFile(compressedFile, stats);
            }
        } catch (error) {
            console.error('Error processing files:', error);
            this.showError('Error processing files');
        } finally {
            // Hide loading indicator
            this.hideLoading();
        }
    }

    showLoading() {
        const loading = document.createElement('div');
        loading.className = 'loading-overlay';
        loading.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Compressing images...</div>
        `;
        this.dropZone.appendChild(loading);
    }

    hideLoading() {
        const loading = this.dropZone.querySelector('.loading-overlay');
        if (loading) {
            loading.remove();
        }
    }

    async previewFile(file, stats) {
        return new Promise((resolve) => {
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
                        ${stats ? `
                        <div class="compression-info position-absolute bottom-0 end-0 p-1 bg-dark bg-opacity-75 text-white small">
                            <small>${stats.percentage}% smaller</small>
                        </div>
                        ` : ''}
                    </div>
                `;

                this.previewContainer.appendChild(preview);
                this.addOrderInput(preview, this.previewContainer.children.length - 1);
                resolve();
            };
        });
    }

    // ... rest of the ImageUpload class code ...
} 
