// Property Filters
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filters-form');
    const propertiesGrid = document.querySelector('.properties-grid');
    const paginationContainer = document.querySelector('.pagination');

    if (filtersForm) {
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Update properties list
        const updateProperties = debounce(function() {
            const formData = new FormData(filtersForm);
            const queryString = new URLSearchParams(formData).toString();
            
            // Update URL without reloading the page
            window.history.pushState({}, '', `${window.location.pathname}?${queryString}`);

            // Show loading state
            if (propertiesGrid) {
                propertiesGrid.style.opacity = '0.5';
            }

            // Fetch filtered properties
            fetch(`/api/properties?${queryString}`)
                .then(response => response.json())
                .then(data => {
                    if (propertiesGrid) {
                        propertiesGrid.innerHTML = data.html;
                        propertiesGrid.style.opacity = '1';
                    }
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (propertiesGrid) {
                        propertiesGrid.style.opacity = '1';
                    }
                });
        }, 500);

        // Add event listeners to form inputs
        filtersForm.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', updateProperties);
            if (input.type === 'number' || input.type === 'text') {
                input.addEventListener('input', updateProperties);
            }
        });
    }
});

// Property Gallery
document.addEventListener('DOMContentLoaded', function() {
    const propertyGallery = document.getElementById('propertyGallery');
    
    if (propertyGallery) {
        // Initialize lightbox for gallery images
        const galleryImages = propertyGallery.querySelectorAll('.carousel-item img');
        
        galleryImages.forEach(img => {
            img.addEventListener('click', function() {
                const lightbox = new bootstrap.Modal(document.getElementById('imageLightbox'));
                document.getElementById('lightboxImage').src = this.src;
                lightbox.show();
            });
        });

        // Touch swipe support for gallery
        let touchstartX = 0;
        let touchendX = 0;

        propertyGallery.addEventListener('touchstart', e => {
            touchstartX = e.changedTouches[0].screenX;
        });

        propertyGallery.addEventListener('touchend', e => {
            touchendX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchendX < touchstartX) {
                // Swipe left - next slide
                bootstrap.Carousel.getInstance(propertyGallery).next();
            }
            if (touchendX > touchstartX) {
                // Swipe right - previous slide
                bootstrap.Carousel.getInstance(propertyGallery).prev();
            }
        }
    }
});

// Contact Form
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            // Send form data
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.textContent = data.message;
                    this.insertBefore(alert, this.firstChild);
                    
                    // Reset form
                    this.reset();
                } else {
                    // Show error messages
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger';
                    const ul = document.createElement('ul');
                    ul.className = 'mb-0';
                    data.errors.forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        ul.appendChild(li);
                    });
                    alert.appendChild(ul);
                    this.insertBefore(alert, this.firstChild);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = 'An error occurred. Please try again.';
                this.insertBefore(alert, this.firstChild);
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Send';
            });
        });
    }
}); 