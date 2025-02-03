// Import modules
import { initializeAuth } from './modules/auth.js';
import { initializeUI } from './modules/ui.js';
import { initializeNotifications } from './modules/notifications.js';
import { initializeBackup } from './modules/backup.js';
import { initializeSettings } from './modules/settings.js';
import { initializeValidation } from './modules/validation.js';
import { initializeCharts } from './modules/charts.js';
import { initializeFileUpload } from './modules/fileUpload.js';

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    // Initialize core modules first
    initializeAuth();
    initializeUI();
    
    // Lazy load feature modules based on page content
    const modulePromises = [];
    
    // Check if notifications are needed
    if (document.querySelector('.notifications-container')) {
        modulePromises.push(
            import('./modules/notifications.js')
                .then(module => module.initializeNotifications())
                .catch(err => console.error('Error loading notifications module:', err))
        );
    }
    
    // Check if backup functionality is needed
    if (document.querySelector('.backup-section')) {
        modulePromises.push(
            import('./modules/backup.js')
                .then(module => module.initializeBackup())
                .catch(err => console.error('Error loading backup module:', err))
        );
    }
    
    // Check if settings are needed
    if (document.querySelector('.settings-section')) {
        modulePromises.push(
            import('./modules/settings.js')
                .then(module => module.initializeSettings())
                .catch(err => console.error('Error loading settings module:', err))
        );
    }
    
    // Check if form validation is needed
    if (document.querySelector('form[data-validate]')) {
        modulePromises.push(
            import('./modules/validation.js')
                .then(module => module.initializeValidation())
                .catch(err => console.error('Error loading validation module:', err))
        );
    }
    
    // Check if charts are needed
    if (document.querySelector('.chart-container')) {
        modulePromises.push(
            import('./modules/charts.js')
                .then(module => module.initializeCharts())
                .catch(err => console.error('Error loading charts module:', err))
        );
    }
    
    // Check if file upload is needed
    if (document.querySelector('.file-upload')) {
        modulePromises.push(
            import('./modules/fileUpload.js')
                .then(module => module.initializeFileUpload())
                .catch(err => console.error('Error loading file upload module:', err))
        );
    }
    
    // Wait for all modules to load
    try {
        await Promise.all(modulePromises);
        console.log('All required modules initialized successfully');
    } catch (error) {
        console.error('Error initializing modules:', error);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Мобилно меню
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '<span></span><span></span><span></span>';
    
    const nav = document.querySelector('.main-nav');
    nav.insertBefore(mobileMenuBtn, nav.firstChild);
    
    mobileMenuBtn.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
        this.classList.toggle('active');
    });

    // Плавен скрол при клик върху линкове в менюто
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Анимация на хедъра при скрол
    let lastScroll = 0;
    const header = document.querySelector('.main-header');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.classList.remove('scroll-up');
            return;
        }
        
        if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
            // Скрол надолу
            header.classList.remove('scroll-up');
            header.classList.add('scroll-down');
        } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
            // Скрол нагоре
            header.classList.remove('scroll-down');
            header.classList.add('scroll-up');
        }
        lastScroll = currentScroll;
    });

    // Lazy loading на изображения
    const images = document.querySelectorAll('img[data-src]');
    const imageOptions = {
        threshold: 0,
        rootMargin: '0px 0px 50px 0px'
    };

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    }, imageOptions);

    images.forEach(img => imageObserver.observe(img));

    // Анимации при скрол
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    const animationOptions = {
        threshold: 0.2
    };

    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, animationOptions);

    animatedElements.forEach(el => animationObserver.observe(el));

    // Валидация на формата за контакт
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = this.querySelector('[name="name"]').value;
            const email = this.querySelector('[name="email"]').value;
            const message = this.querySelector('[name="message"]').value;
            let isValid = true;
            
            // Изчистване на предишни грешки
            this.querySelectorAll('.error-message').forEach(el => el.remove());
            
            // Валидация на име
            if (name.trim().length < 2) {
                showError(this.querySelector('[name="name"]'), 'Моля, въведете валидно име');
                isValid = false;
            }
            
            // Валидация на имейл
            if (!isValidEmail(email)) {
                showError(this.querySelector('[name="email"]'), 'Моля, въведете валиден имейл адрес');
                isValid = false;
            }
            
            // Валидация на съобщение
            if (message.trim().length < 10) {
                showError(this.querySelector('[name="message"]'), 'Съобщението трябва да бъде поне 10 символа');
                isValid = false;
            }
            
            if (isValid) {
                // Изпращане на формата
                this.submit();
            }
        });
    }

    animateNumbers();

    initAboutAnimations();

    initSearchPage();
});

// Помощни функции
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function showError(input, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentNode.insertBefore(errorDiv, input.nextSibling);
    input.classList.add('error');
}

// Добавяне на допълнителни стилове за мобилното меню
const style = document.createElement('style');
style.textContent = `
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px;
    }

    .mobile-menu-btn span {
        display: block;
        width: 25px;
        height: 3px;
        background: #333;
        margin: 5px 0;
        transition: 0.3s;
    }

    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }

        .nav-links {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            background: #fff;
            padding: 20px;
            flex-direction: column;
            text-align: center;
            transform: translateY(-100%);
            transition: transform 0.3s;
        }

        .nav-links.active {
            transform: translateY(0);
        }

        .mobile-menu-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
    }
`;

document.head.appendChild(style);

function animateNumbers() {
    const stats = document.querySelectorAll('.stat-number');
    
    stats.forEach(stat => {
        const target = parseInt(stat.textContent);
        const duration = 2500; // Увеличаваме продължителността
        const frames = 60;
        const step = target / (duration / (1000 / frames));
        let current = 0;
        
        function easeOutQuart(x) {
            return 1 - Math.pow(1 - x, 4);
        }
        
        function updateNumber(timestamp) {
            if (!updateNumber.startTime) {
                updateNumber.startTime = timestamp;
            }
            
            const elapsed = timestamp - updateNumber.startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            current = Math.round(target * easeOutQuart(progress));
            stat.textContent = current + '+';
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    requestAnimationFrame(updateNumber);
                    observer.unobserve(entry.target);
                    entry.target.closest('.stat-item').classList.add('animated');
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(stat);
    });
}

// Анимации при скролване за About страницата
function initAboutAnimations() {
    const aboutContent = document.querySelector('.about-content');
    const whyUsItems = document.querySelectorAll('.why-us-item');
    const teamMembers = document.querySelectorAll('.team-member');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // Специална анимация за Why Us секцията
                if (entry.target.classList.contains('why-us-item')) {
                    const delay = Array.from(whyUsItems).indexOf(entry.target) * 200;
                    entry.target.style.transitionDelay = `${delay}ms`;
                }
                
                // Специална анимация за екипа
                if (entry.target.classList.contains('team-member')) {
                    const delay = Array.from(teamMembers).indexOf(entry.target) * 200;
                    entry.target.style.transitionDelay = `${delay}ms`;
                }
                
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    
    if (aboutContent) observer.observe(aboutContent);
    whyUsItems.forEach(item => observer.observe(item));
    teamMembers.forEach(member => observer.observe(member));
    
    // Добавяне на floating ефект
    const floatingElements = document.querySelectorAll('.floating');
    floatingElements.forEach(element => {
        element.style.animationDelay = Math.random() * 2 + 's';
    });
    
    // Анимация на skill bars
    const skillBars = document.querySelectorAll('.skill-progress');
    const skillObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progress = entry.target;
                const value = progress.getAttribute('data-value');
                progress.style.transform = `scaleX(${value / 100})`;
                skillObserver.unobserve(progress);
            }
        });
    }, { threshold: 0.5 });
    
    skillBars.forEach(bar => skillObserver.observe(bar));
    
    // Паралакс ефект за About секцията
    const aboutSection = document.querySelector('.about-section');
    if (aboutSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.15;
            
            const image = aboutSection.querySelector('.about-image');
            if (image) {
                image.style.transform = `translate3d(0, ${rate}px, 0)`;
            }
        });
    }

    createParticles();
    initTimelineAnimation();
    init3DCardEffect();
}

function createParticles() {
    const container = document.querySelector('.particles-container');
    if (!container) return;

    const particleCount = 20;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Случайно позициониране
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        
        // Случайно закъснение на анимацията
        particle.style.animationDelay = Math.random() * 5 + 's';
        
        container.appendChild(particle);
    }
}

function initTimelineAnimation() {
    const timelineItems = document.querySelectorAll('.timeline-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // Добавяме закъснение за последователно появяване
                const index = Array.from(timelineItems).indexOf(entry.target);
                entry.target.style.transitionDelay = `${index * 200}ms`;
                
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    
    timelineItems.forEach(item => observer.observe(item));
}

// 3D Card Effect
function init3DCardEffect() {
    const cards = document.querySelectorAll('.about-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `
                perspective(1000px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                scale3d(1.05, 1.05, 1.05)
            `;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'none';
        });
    });
}

// Функционалности за страницата за търсене
function initSearchPage() {
    const filtersForm = document.querySelector('.filters-form');
    const activeFilters = document.querySelector('.active-filters');
    const rangeInputs = document.querySelectorAll('input[type="number"]');
    
    if (!filtersForm) return;

    // Автоматично изпращане на формата при промяна на селект полетата
    filtersForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', () => {
            filtersForm.submit();
        });
    });

    // Забавяне на изпращането при въвеждане в полетата за цена и площ
    let timeout;
    rangeInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                filtersForm.submit();
            }, 1000);
        });
    });

    // Добавяне на активни филтри като тагове
    function updateActiveFilters() {
        if (!activeFilters) return;
        
        activeFilters.innerHTML = '';
        const formData = new FormData(filtersForm);
        
        for (let [key, value] of formData.entries()) {
            if (value && key !== 'page') {
                const tag = document.createElement('span');
                tag.className = 'filter-tag';
                
                // Вземане на текста на избраната опция за селект полетата
                if (key.includes('type') || key.includes('status') || key.includes('location')) {
                    const select = filtersForm.querySelector(`select[name="${key}"]`);
                    const option = select.options[select.selectedIndex];
                    value = option.text;
                }
                
                tag.innerHTML = `
                    ${value}
                    <span class="remove" data-filter="${key}">×</span>
                `;
                
                activeFilters.appendChild(tag);
            }
        }
        
        // Премахване на филтър при клик върху X
        activeFilters.querySelectorAll('.remove').forEach(removeBtn => {
            removeBtn.addEventListener('click', () => {
                const filterName = removeBtn.dataset.filter;
                const input = filtersForm.querySelector(`[name="${filterName}"]`);
                
                if (input.tagName === 'SELECT') {
                    input.value = '';
                } else {
                    input.value = '';
                }
                
                filtersForm.submit();
            });
        });
    }

    // Анимация на картите с имоти при скролване
    function animatePropertyCards() {
        const cards = document.querySelectorAll('.property-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        cards.forEach(card => {
            card.style.opacity = '0';
            observer.observe(card);
        });
    }

    // Валидация на ценовите диапазони
    function validateRanges() {
        const minPrice = document.querySelector('[name="min_price"]');
        const maxPrice = document.querySelector('[name="max_price"]');
        const minArea = document.querySelector('[name="min_area"]');
        const maxArea = document.querySelector('[name="max_area"]');

        function validateRange(min, max) {
            if (!min || !max) return;
            
            min.addEventListener('input', () => {
                if (Number(min.value) > Number(max.value) && max.value) {
                    min.value = max.value;
                }
            });
            
            max.addEventListener('input', () => {
                if (Number(max.value) < Number(min.value) && min.value) {
                    max.value = min.value;
                }
            });
        }

        validateRange(minPrice, maxPrice);
        validateRange(minArea, maxArea);
    }

    // Добавяне на сортиране с анимация
    function initSorting() {
        const sortSelect = document.querySelector('.sort-select');
        if (!sortSelect) return;
        
        sortSelect.addEventListener('change', () => {
            const cards = document.querySelectorAll('.property-card');
            cards.forEach(card => {
                card.style.animation = 'fadeOut 0.3s ease forwards';
            });
            
            setTimeout(() => filtersForm.submit(), 300);
        });
    }

    // Добавяне на филтър за запазени имоти
    function initFavorites() {
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const favoritesFilter = document.querySelector('.favorites-filter');
        
        if (favoritesFilter) {
            favoritesFilter.addEventListener('change', () => {
                if (favoritesFilter.checked) {
                    document.querySelectorAll('.property-card').forEach(card => {
                        if (!favorites.includes(card.dataset.id)) {
                            card.style.display = 'none';
                        }
                    });
                } else {
                    document.querySelectorAll('.property-card').forEach(card => {
                        card.style.display = 'block';
                    });
                }
            });
        }
    }

    // Добавяне на интерактивна карта с имотите
    function initMap() {
        const mapContainer = document.querySelector('#properties-map');
        if (!mapContainer) return;

        const map = L.map(mapContainer).setView([42.6977, 23.3219], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Добавяне на маркери за всеки имот
        document.querySelectorAll('.property-card').forEach(card => {
            const lat = card.dataset.lat;
            const lng = card.dataset.lng;
            if (lat && lng) {
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div class="map-popup">
                        <img src="${card.querySelector('img').src}" alt="">
                        <h3>${card.querySelector('h3').textContent}</h3>
                        <p>${card.querySelector('.price').textContent}</p>
                        <a href="${card.querySelector('a').href}">
                            ${card.querySelector('a').textContent}
                        </a>
                    </div>
                `);
            }
        });
    }

    // Добавяне на филтри за допълнителни характеристики
    function initAdvancedFilters() {
        const advancedBtn = document.querySelector('.advanced-filters-btn');
        const advancedFilters = document.querySelector('.advanced-filters');
        
        if (advancedBtn && advancedFilters) {
            advancedBtn.addEventListener('click', () => {
                advancedFilters.classList.toggle('active');
                advancedBtn.textContent = advancedFilters.classList.contains('active') 
                    ? 'Скрий филтри' 
                    : 'Покажи още филтри';
            });
        }
    }

    // Добавяне на сравнение на имоти
    function initCompare() {
        const compareCheckboxes = document.querySelectorAll('.compare-checkbox');
        const compareBtn = document.querySelector('.compare-btn');
        const compareList = [];

        compareCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const propertyId = checkbox.dataset.id;
                
                if (checkbox.checked) {
                    if (compareList.length >= 3) {
                        checkbox.checked = false;
                        alert('Можете да сравните максимум 3 имота');
                        return;
                    }
                    compareList.push(propertyId);
                } else {
                    const index = compareList.indexOf(propertyId);
                    if (index > -1) compareList.splice(index, 1);
                }

                compareBtn.disabled = compareList.length < 2;
            });
        });

        if (compareBtn) {
            compareBtn.addEventListener('click', () => {
                window.location.href = `compare.php?ids=${compareList.join(',')}`;
            });
        }
    }

    // Инициализация на всички функционалности
    updateActiveFilters();
    animatePropertyCards();
    validateRanges();
    initSorting();
    initFavorites();
    initMap();
    initAdvancedFilters();
    initCompare();
}

// Добавяне към основната инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Съществуващ код...
    
    initSearchPage();
});

function initPropertyPage() {
    if (!document.querySelector('.property-page')) return;
    
    // Инициализация на Swiper галерията
    const thumbsSwiper = new Swiper('.thumbs-swiper', {
        spaceBetween: 10,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
        centerInsufficientSlides: true
    });

    const mainSwiper = new Swiper('.main-swiper', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: thumbsSwiper
        }
    });

    // Инициализация на картата
    const mapElement = document.getElementById('property-map');
    if (mapElement) {
        const lat = parseFloat(mapElement.dataset.lat);
        const lng = parseFloat(mapElement.dataset.lng);
        
        const map = new google.maps.Map(mapElement, {
            center: { lat, lng },
            zoom: 15,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        new google.maps.Marker({
            position: { lat, lng },
            map,
            icon: {
                url: 'assets/images/marker.png',
                scaledSize: new google.maps.Size(40, 40)
            }
        });
    }

    // Функционалност за запазване на имот
    const saveButton = document.querySelector('.save-property');
    if (saveButton) {
        const propertyId = saveButton.dataset.id;
        const savedProperties = JSON.parse(localStorage.getItem('savedProperties') || '[]');
        
        if (savedProperties.includes(propertyId)) {
            saveButton.classList.add('saved');
        }
        
        saveButton.addEventListener('click', function() {
            const index = savedProperties.indexOf(propertyId);
            
            if (index === -1) {
                savedProperties.push(propertyId);
                this.classList.add('saved');
                showNotification(translations.property_saved);
            } else {
                savedProperties.splice(index, 1);
                this.classList.remove('saved');
                showNotification(translations.property_removed);
            }
            
            localStorage.setItem('savedProperties', JSON.stringify(savedProperties));
        });
    }

    // Функционалност за споделяне
    const shareButton = document.querySelector('.share-property');
    if (shareButton) {
        shareButton.addEventListener('click', async function() {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: document.title,
                        url: window.location.href
                    });
                } catch (err) {
                    console.error('Error sharing:', err);
                }
            } else {
                // Fallback за браузъри без Web Share API
                const dummy = document.createElement('input');
                document.body.appendChild(dummy);
                dummy.value = window.location.href;
                dummy.select();
                document.execCommand('copy');
                document.body.removeChild(dummy);
                showNotification('Линкът е копиран в клипборда');
            }
        });
    }

    // Функционалност за принтиране
    const printButton = document.querySelector('.print-property');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }

    // Валидация на контактната форма
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('send-inquiry.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    this.reset();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Възникна грешка при изпращането', 'error');
            });
        });
    }
}

// Добавяне към основната инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Съществуващ код...
    
    initPropertyPage();
}); 