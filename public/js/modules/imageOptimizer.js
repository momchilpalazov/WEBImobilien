// Image optimization module
export class ImageOptimizer {
    constructor(options = {}) {
        this.options = {
            lazyLoadSelector: '[data-src]',
            responsiveBreakpoints: {
                sm: 576,
                md: 768,
                lg: 992,
                xl: 1200
            },
            defaultQuality: 80,
            webpSupport: this.checkWebPSupport(),
            ...options
        };

        this.init();
    }

    async init() {
        // Initialize Intersection Observer for lazy loading
        this.initLazyLoading();
        
        // Initialize responsive images
        this.initResponsiveImages();
        
        // Initialize WebP conversion if supported
        if (this.options.webpSupport) {
            this.initWebPConversion();
        }
    }

    initLazyLoading() {
        const imageObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            },
            {
                rootMargin: '50px 0px',
                threshold: 0.01
            }
        );

        document.querySelectorAll(this.options.lazyLoadSelector).forEach(img => {
            imageObserver.observe(img);
        });
    }

    async loadImage(img) {
        try {
            const src = img.dataset.src;
            const srcset = img.dataset.srcset;
            
            // Load optimized version based on device capabilities
            const optimizedSrc = await this.getOptimizedImageUrl(src);
            
            if (srcset) {
                const optimizedSrcset = await this.getOptimizedSrcset(srcset);
                img.srcset = optimizedSrcset;
            }
            
            img.src = optimizedSrc;
            img.classList.add('loaded');
            
            // Remove data attributes
            delete img.dataset.src;
            delete img.dataset.srcset;
        } catch (error) {
            console.error('Error loading image:', error);
            // Fallback to original source
            img.src = img.dataset.src;
        }
    }

    initResponsiveImages() {
        const images = document.querySelectorAll('img[data-responsive]');
        
        images.forEach(img => {
            const sizes = this.getResponsiveSizes(img);
            if (sizes) {
                img.sizes = sizes;
            }
        });
    }

    getResponsiveSizes(img) {
        const container = img.closest('[data-container-type]');
        if (!container) return null;

        const containerType = container.dataset.containerType;
        
        switch (containerType) {
            case 'fluid':
                return '100vw';
            case 'fixed':
                return `(max-width: ${this.options.responsiveBreakpoints.sm}px) 100vw,
                        (max-width: ${this.options.responsiveBreakpoints.md}px) 720px,
                        (max-width: ${this.options.responsiveBreakpoints.lg}px) 960px,
                        1140px`;
            case 'gallery':
                return `(max-width: ${this.options.responsiveBreakpoints.sm}px) 100vw,
                        (max-width: ${this.options.responsiveBreakpoints.md}px) 50vw,
                        (max-width: ${this.options.responsiveBreakpoints.lg}px) 33.3vw,
                        25vw`;
            default:
                return null;
        }
    }

    async getOptimizedImageUrl(url) {
        // Check if we should convert to WebP
        if (this.options.webpSupport && !url.endsWith('.webp')) {
            try {
                const webpUrl = await this.convertToWebP(url);
                return webpUrl;
            } catch (error) {
                console.warn('WebP conversion failed:', error);
            }
        }
        
        return this.addOptimizationParams(url);
    }

    async getOptimizedSrcset(srcset) {
        const srcsetParts = srcset.split(',').map(s => s.trim());
        const optimizedParts = await Promise.all(
            srcsetParts.map(async part => {
                const [url, size] = part.split(' ');
                const optimizedUrl = await this.getOptimizedImageUrl(url);
                return `${optimizedUrl} ${size}`;
            })
        );
        
        return optimizedParts.join(', ');
    }

    addOptimizationParams(url) {
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.set('q', this.options.defaultQuality);
        return urlObj.toString();
    }

    async convertToWebP(url) {
        // This would typically call your server's image conversion endpoint
        const conversionEndpoint = '/api/images/convert-webp';
        try {
            const response = await fetch(conversionEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ url })
            });
            
            if (!response.ok) throw new Error('WebP conversion failed');
            
            const data = await response.json();
            return data.webpUrl;
        } catch (error) {
            throw new Error(`WebP conversion failed: ${error.message}`);
        }
    }

    async checkWebPSupport() {
        try {
            const canvas = document.createElement('canvas');
            if (canvas.getContext && canvas.getContext('2d')) {
                return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
            }
            return false;
        } catch (e) {
            return false;
        }
    }

    // Helper method to generate srcset for responsive images
    generateSrcset(baseUrl, widths = [320, 640, 960, 1280, 1920]) {
        return widths
            .map(width => {
                const url = this.addOptimizationParams(baseUrl);
                return `${url}&w=${width} ${width}w`;
            })
            .join(', ');
    }
}

// Initialize image optimizer
export const initializeImageOptimizer = (options = {}) => {
    return new ImageOptimizer(options);
}; 