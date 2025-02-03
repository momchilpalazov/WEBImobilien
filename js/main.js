document.addEventListener('DOMContentLoaded', function() {
    // Анимация при скролване
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.property-card');
        
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementBottom = element.getBoundingClientRect().bottom;
            
            if (elementTop < window.innerHeight && elementBottom > 0) {
                element.classList.add('animate__animated', 'animate__fadeInUp');
            }
        });
    };
    
    // Добавяме animate.css
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';
    document.head.appendChild(link);
    
    // Инициализация на анимациите
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
    
    // Форма за търсене - валидация
    const searchForm = document.querySelector('form[action="/properties.php"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const minArea = this.querySelector('[name="min_area"]').value;
            const maxPrice = this.querySelector('[name="max_price"]').value;
            
            if (minArea && minArea < 0) {
                e.preventDefault();
                alert('Минималната площ не може да бъде отрицателна');
                return;
            }
            
            if (maxPrice && maxPrice < 0) {
                e.preventDefault();
                alert('Максималната цена не може да бъде отрицателна');
                return;
            }
        });
    }
    
    // Lazy loading за изображения
    const images = document.querySelectorAll('.property-card img');
    if ('loading' in HTMLImageElement.prototype) {
        images.forEach(img => {
            img.loading = 'lazy';
        });
    } else {
        // Fallback за браузъри, които не поддържат lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lozad.js/1.16.0/lozad.min.js';
        script.onload = function() {
            const observer = lozad('.property-card img');
            observer.observe();
        };
        document.body.appendChild(script);
    }
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}); 