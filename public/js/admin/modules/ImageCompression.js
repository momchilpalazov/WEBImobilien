export class ImageCompression {
    constructor(options = {}) {
        this.maxSizeMB = options.maxSizeMB || 1;
        this.maxWidthOrHeight = options.maxWidthOrHeight || 1920;
        this.useWebWorker = options.useWebWorker !== undefined ? options.useWebWorker : true;
        this.preserveExif = options.preserveExif !== undefined ? options.preserveExif : true;
        this.fileType = options.fileType || null;
        this.initialQuality = options.initialQuality || 0.8;
    }

    async compressImage(file) {
        try {
            const options = {
                maxSizeMB: this.maxSizeMB,
                maxWidthOrHeight: this.maxWidthOrHeight,
                useWebWorker: this.useWebWorker,
                preserveExif: this.preserveExif,
                initialQuality: this.initialQuality
            };

            if (this.fileType) {
                options.fileType = this.fileType;
            }

            // Skip compression for small images
            if (file.size <= this.maxSizeMB * 1024 * 1024) {
                console.log('Image is already smaller than max size');
                return file;
            }

            const compressedFile = await imageCompression(file, options);
            
            // Create a new File object with the original name
            return new File([compressedFile], file.name, {
                type: compressedFile.type,
                lastModified: new Date().getTime()
            });
        } catch (error) {
            console.error('Error compressing image:', error);
            return file; // Return original file if compression fails
        }
    }

    async compressMultiple(files) {
        const compressedFiles = [];
        
        for (const file of files) {
            if (file.type.startsWith('image/')) {
                const compressedFile = await this.compressImage(file);
                compressedFiles.push(compressedFile);
            } else {
                compressedFiles.push(file);
            }
        }
        
        return compressedFiles;
    }

    getCompressionStats(originalFile, compressedFile) {
        const originalSize = originalFile.size / 1024; // KB
        const compressedSize = compressedFile.size / 1024; // KB
        const savings = originalSize - compressedSize;
        const percentage = (savings / originalSize) * 100;

        return {
            originalSize: originalSize.toFixed(2),
            compressedSize: compressedSize.toFixed(2),
            savings: savings.toFixed(2),
            percentage: percentage.toFixed(1)
        };
    }
} 