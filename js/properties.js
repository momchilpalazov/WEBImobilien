let isLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    // Обработка на бутона "Назад"
    window.addEventListener('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('page')) || 1;
        loadProperties(page);
    });
    
    // Изчистване на филтрите
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const form = document.getElementById('filterForm');
            
            // Изчистваме всички полета
            form.querySelectorAll('input[type="number"]').forEach(input => {
                input.value = '';
            });
            
            form.querySelectorAll('select').forEach(select => {
                select.value = '';
            });
            
            form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Изпращаме формата
            form.submit();
        });
    }
    
    // Валидация на формата
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadProperties(1);
        });
    }
    
    // Анимации при скролване
    const animateOnScroll = () => {
        const cards = document.querySelectorAll('.property-card:not(.animate)');
        
        cards.forEach(card => {
            const cardTop = card.getBoundingClientRect().top;
            const cardBottom = card.getBoundingClientRect().bottom;
            
            if (cardTop < window.innerHeight && cardBottom > 0) {
                card.classList.add('animate');
            }
        });
    };
    
    // Инициализация на анимациите
    window.addEventListener('scroll', animateOnScroll);
    
    // Първоначална анимация след малко закъснение
    setTimeout(animateOnScroll, 100);
    
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
});

// Функция за зареждане на имоти
function loadProperties(page = 1) {
    if (isLoading) return;
    isLoading = true;
    
    // Показваме loading индикатор
    const loadingIndicator = document.getElementById('loadingIndicator');
    const propertiesGrid = document.querySelector('.properties-grid');
    
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
    }
    
    // Плавно намаляваме opacity на текущите резултати
    propertiesGrid.style.transition = 'opacity 0.3s';
    propertiesGrid.style.opacity = '0.3';
    
    // Вземаме всички параметри от формата
    const formData = new FormData(document.getElementById('filterForm'));
    formData.append('page', page);
    
    // Създаваме URL с параметрите
    const params = new URLSearchParams(formData);
    
    // Актуализираме URL на браузъра без презареждане
    window.history.pushState({}, '', `?${params.toString()}`);
    
    // Правим AJAX заявка
    fetch(`ajax/load-properties.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновяваме съдържанието
                propertiesGrid.innerHTML = data.html;
                
                // Изчакваме малко преди да стартираме анимациите
                setTimeout(() => {
                    // Премахваме всички animate класове
                    document.querySelectorAll('.property-card.animate').forEach(card => {
                        card.classList.remove('animate');
                    });
                    
                    // Стартираме анимациите отново
                    animateOnScroll();
                }, 100);
                
                // Обновяваме пагинацията
                updatePagination(data.current_page, data.pages);
                
                // Показваме броя резултати
                updateResultsCount(data.total);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while loading properties', 'error');
        })
        .finally(() => {
            isLoading = false;
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        });
}

// Функция за обновяване на пагинацията
function updatePagination(currentPage, totalPages) {
    const pagination = document.querySelector('.pagination');
    if (!pagination) return;
    
    let html = '';
    
    // Previous button
    if (currentPage > 1) {
        html += `<li class="page-item">
            <a class="page-link" href="#" data-page="${currentPage - 1}">
                <i class="mdi mdi-chevron-left"></i>
            </a>
        </li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `<li class="page-item">
            <a class="page-link" href="#" data-page="${currentPage + 1}">
                <i class="mdi mdi-chevron-right"></i>
            </a>
        </li>`;
    }
    
    pagination.innerHTML = html;
    
    // Add click handlers
    pagination.querySelectorAll('.page-link[data-page]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadProperties(parseInt(this.dataset.page));
        });
    });
}

// Функция за обновяване на броя резултати
function updateResultsCount(total) {
    const resultsInfo = document.querySelector('.results-info');
    if (resultsInfo) {
        resultsInfo.textContent = `Showing ${total} properties`;
    }
} 