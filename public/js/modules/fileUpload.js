// Import UI module for notifications
import { ui } from './ui.js';

// File upload state
let uploads = new Map();
let totalUploads = 0;

// Supported file types
const SUPPORTED_TYPES = {
    images: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    documents: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    spreadsheets: ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    archives: ['application/zip', 'application/x-rar-compressed']
};

// File size limits (in bytes)
const SIZE_LIMITS = {
    image: 5 * 1024 * 1024, // 5MB
    document: 10 * 1024 * 1024, // 10MB
    spreadsheet: 10 * 1024 * 1024, // 10MB
    archive: 50 * 1024 * 1024 // 50MB
};

// Event handlers
const handleFileSelect = (event) => {
    const input = event.target;
    const files = Array.from(input.files);
    const uploaderId = input.dataset.uploader;
    
    // Validate files
    const validFiles = files.filter(file => validateFile(file));
    
    if (validFiles.length !== files.length) {
        ui.showToast('Some files were rejected due to invalid type or size', 'warning');
    }
    
    // Start upload for valid files
    validFiles.forEach(file => {
        uploadFile(file, uploaderId);
    });
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.currentTarget.classList.add('dragover');
};

const handleDragLeave = (event) => {
    event.preventDefault();
    event.currentTarget.classList.remove('dragover');
};

const handleDrop = (event) => {
    event.preventDefault();
    event.currentTarget.classList.remove('dragover');
    
    const files = Array.from(event.dataTransfer.files);
    const uploaderId = event.currentTarget.dataset.uploader;
    
    // Validate files
    const validFiles = files.filter(file => validateFile(file));
    
    if (validFiles.length !== files.length) {
        ui.showToast('Some files were rejected due to invalid type or size', 'warning');
    }
    
    // Start upload for valid files
    validFiles.forEach(file => {
        uploadFile(file, uploaderId);
    });
};

// File validation
const validateFile = (file) => {
    // Check file type
    const isValidType = Object.values(SUPPORTED_TYPES)
        .flat()
        .includes(file.type);
        
    if (!isValidType) {
        ui.showToast(`File type ${file.type} is not supported`, 'error');
        return false;
    }
    
    // Check file size
    const sizeLimit = getSizeLimit(file.type);
    if (file.size > sizeLimit) {
        ui.showToast(`File size exceeds the limit of ${formatSize(sizeLimit)}`, 'error');
        return false;
    }
    
    return true;
};

const getSizeLimit = (fileType) => {
    if (SUPPORTED_TYPES.images.includes(fileType)) return SIZE_LIMITS.image;
    if (SUPPORTED_TYPES.documents.includes(fileType)) return SIZE_LIMITS.document;
    if (SUPPORTED_TYPES.spreadsheets.includes(fileType)) return SIZE_LIMITS.spreadsheet;
    if (SUPPORTED_TYPES.archives.includes(fileType)) return SIZE_LIMITS.archive;
    return 0;
};

// File upload
const uploadFile = async (file, uploaderId) => {
    const uploadId = ++totalUploads;
    const upload = {
        id: uploadId,
        file,
        progress: 0,
        status: 'pending',
        uploaderId
    };
    
    uploads.set(uploadId, upload);
    createUploadElement(upload);
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await fetch('/api/upload', {
            method: 'POST',
            body: formData,
            onUploadProgress: (progressEvent) => {
                updateProgress(uploadId, (progressEvent.loaded / progressEvent.total) * 100);
            }
        });
        
        if (!response.ok) throw new Error('Upload failed');
        
        const result = await response.json();
        completeUpload(uploadId, result);
        
        ui.showToast('File uploaded successfully', 'success');
    } catch (error) {
        failUpload(uploadId, error.message);
        ui.showToast('Failed to upload file', 'error');
    }
};

// UI updates
const createUploadElement = (upload) => {
    const container = document.querySelector(`#${upload.uploaderId} .upload-list`);
    if (!container) return;
    
    const element = document.createElement('div');
    element.className = 'upload-item';
    element.dataset.uploadId = upload.id;
    element.innerHTML = `
        <div class="upload-info">
            <div class="upload-name">${upload.file.name}</div>
            <div class="upload-size">${formatSize(upload.file.size)}</div>
        </div>
        <div class="upload-progress">
            <div class="progress-bar" style="width: 0%"></div>
        </div>
        <div class="upload-actions">
            <button class="btn btn-sm btn-danger cancel-upload" title="Cancel upload">
                <i class="icon-cancel"></i>
            </button>
        </div>
    `;
    
    // Add cancel handler
    element.querySelector('.cancel-upload').addEventListener('click', () => {
        cancelUpload(upload.id);
    });
    
    container.appendChild(element);
};

const updateProgress = (uploadId, progress) => {
    const upload = uploads.get(uploadId);
    if (!upload) return;
    
    upload.progress = progress;
    
    const element = document.querySelector(`[data-upload-id="${uploadId}"]`);
    if (element) {
        const progressBar = element.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
    }
};

const completeUpload = (uploadId, result) => {
    const upload = uploads.get(uploadId);
    if (!upload) return;
    
    upload.status = 'complete';
    upload.result = result;
    
    const element = document.querySelector(`[data-upload-id="${uploadId}"]`);
    if (element) {
        element.classList.add('complete');
        element.querySelector('.upload-actions').innerHTML = `
            <button class="btn btn-sm btn-success" disabled>
                <i class="icon-check"></i>
            </button>
        `;
    }
    
    // Remove from uploads map after animation
    setTimeout(() => {
        uploads.delete(uploadId);
        element?.remove();
    }, 3000);
};

const failUpload = (uploadId, error) => {
    const upload = uploads.get(uploadId);
    if (!upload) return;
    
    upload.status = 'failed';
    upload.error = error;
    
    const element = document.querySelector(`[data-upload-id="${uploadId}"]`);
    if (element) {
        element.classList.add('failed');
        element.querySelector('.upload-actions').innerHTML = `
            <button class="btn btn-sm btn-danger" disabled>
                <i class="icon-error"></i>
            </button>
        `;
    }
};

const cancelUpload = (uploadId) => {
    const upload = uploads.get(uploadId);
    if (!upload) return;
    
    upload.status = 'cancelled';
    
    const element = document.querySelector(`[data-upload-id="${uploadId}"]`);
    if (element) {
        element.remove();
    }
    
    uploads.delete(uploadId);
};

// Helper functions
const formatSize = (bytes) => {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(1)} ${units[unitIndex]}`;
};

// Initialize file upload module
export const initializeFileUpload = () => {
    // Add event listeners to all file upload areas
    document.querySelectorAll('.file-upload').forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const dropZone = uploader.querySelector('.drop-zone');
        
        if (input) {
            input.addEventListener('change', handleFileSelect);
        }
        
        if (dropZone) {
            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('dragleave', handleDragLeave);
            dropZone.addEventListener('drop', handleDrop);
        }
    });
};

// Export file upload module
export const fileUpload = {
    getUploads: () => Array.from(uploads.values()),
    cancelUpload,
    getSupportedTypes: () => SUPPORTED_TYPES,
    getSizeLimits: () => SIZE_LIMITS
}; 