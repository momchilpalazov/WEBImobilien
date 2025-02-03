// Инициализация на Dropzone за снимки
Dropzone.autoDiscover = false;

import Sortable from 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/modular/sortable.esm.js';

document.addEventListener('DOMContentLoaded', function() {
    // Dropzone за снимки
    const imageDropzone = new Dropzone("#imageUpload", {
        url: "ajax/upload-images.php",
        paramName: "file",
        maxFilesize: 5, // MB
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        dictDefaultMessage: "Плъзнете снимки тук или кликнете за избор",
        dictRemoveFile: "Изтрий",
        dictCancelUpload: "Отказ",
        dictFileTooBig: "Файлът е твърде голям ({{filesize}}MB). Максимален размер: {{maxFilesize}}MB.",
        init: function() {
            const dz = this;
            const propertyId = document.querySelector('[name="id"]').value;
            
            // Добавяне на property_id към всяко качване
            this.on("sending", function(file, xhr, formData) {
                formData.append("property_id", propertyId);
            });
            
            // При успешно качване
            this.on("success", function(file, response) {
                if (response.success) {
                    file.serverId = response.file.id;
                    file.serverUrl = response.file.url;
                    
                    // Добавяне на снимката към галерията
                    addToGallery(response.file);
                } else {
                    this.removeFile(file);
                    showNotification(response.message, 'error');
                }
            });
            
            // При грешка
            this.on("error", function(file, errorMessage) {
                showNotification(errorMessage, 'error');
            });
        }
    });
    
    // Dropzone за документи
    const documentDropzone = new Dropzone("#documentUpload", {
        url: "ajax/upload-document.php",
        paramName: "file",
        maxFilesize: 10,
        acceptedFiles: ".pdf,.doc,.docx",
        addRemoveLinks: true,
        dictDefaultMessage: "Плъзнете документи тук или кликнете за избор",
        dictRemoveFile: "Изтрий",
        init: function() {
            const dz = this;
            const propertyId = document.querySelector('[name="id"]').value;
            
            this.on("sending", function(file, xhr, formData) {
                formData.append("property_id", propertyId);
                
                // Добавяне на заглавия на различни езици
                const title = file.name.replace(/\.[^/.]+$/, "");
                formData.append("title_bg", title);
                formData.append("title_de", title);
                formData.append("title_ru", title);
            });
            
            this.on("success", function(file, response) {
                if (response.success) {
                    file.serverId = response.file.id;
                    addToDocumentList(response.file);
                } else {
                    this.removeFile(file);
                    showNotification(response.message, 'error');
                }
            });
        }
    });
    
    // Функция за добавяне на снимка към галерията
    function addToGallery(file) {
        const gallery = document.querySelector('.image-gallery');
        const item = document.createElement('div');
        item.className = 'image-item';
        item.dataset.id = file.id;
        
        item.innerHTML = `
            <a href="${file.url}" 
               data-fancybox="property-gallery" 
               data-caption="${file.name}">
                <img src="${file.thumbnail}" alt="${file.name}">
            </a>
            <div class="image-actions">
                <button type="button" class="set-main" title="Задай като основна">
                    <i class="icon-star"></i>
                </button>
                <button type="button" class="delete-image" title="Изтрий">
                    <i class="icon-delete"></i>
                </button>
            </div>
        `;
        
        gallery.appendChild(item);
        initImageActions(item);
    }
    
    // Функция за добавяне на документ към списъка
    function addToDocumentList(file) {
        const list = document.querySelector('.document-list');
        const item = document.createElement('div');
        item.className = 'document-item';
        item.dataset.id = file.id;
        
        const filesize = formatFileSize(file.size);
        
        item.innerHTML = `
            <div class="document-info">
                <i class="icon-document"></i>
                <div class="document-details">
                    <span class="document-name">${file.name}</span>
                    <span class="document-meta">${filesize}</span>
                </div>
            </div>
            <div class="document-actions">
                <a href="${file.url}" target="_blank" class="btn btn-sm btn-outline-info">
                    <i class="icon-download"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger delete-document">
                    <i class="icon-delete"></i>
                </button>
            </div>
        `;
        
        list.appendChild(item);
        initDocumentActions(item);
    }
    
    // Помощни функции
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function initImageActions(item) {
        const setMainBtn = item.querySelector('.set-main');
        const deleteBtn = item.querySelector('.delete-image');
        
        setMainBtn.addEventListener('click', function() {
            const imageId = item.dataset.id;
            fetch('ajax/set-main-image.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: imageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.image-item').forEach(el => {
                        el.classList.remove('is-main');
                    });
                    item.classList.add('is-main');
                    showNotification('Основната снимка беше променена');
                } else {
                    showNotification(data.message, 'error');
                }
            });
        });
        
        deleteBtn.addEventListener('click', function() {
            if (confirm('Сигурни ли сте, че искате да изтриете тази снимка?')) {
                const imageId = item.dataset.id;
                fetch('ajax/delete-image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: imageId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        showNotification('Снимката беше изтрита');
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        });
    }
    
    function initDocumentActions(item) {
        const deleteBtn = item.querySelector('.delete-document');
        
        deleteBtn.addEventListener('click', function() {
            if (confirm('Сигурни ли сте, че искате да изтриете този документ?')) {
                const docId = item.dataset.id;
                fetch('ajax/delete-document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: docId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        showNotification('Документът беше изтрит');
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        });
    }
    
    // Инициализация на сортирането
    const gallery = document.querySelector('.image-gallery');
    if (gallery) {
        const sortable = new Sortable(gallery, {
            animation: 150,
            handle: '.image-item', // Можем да влачим навсякъде по снимката
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                const images = Array.from(gallery.children).map(item => item.dataset.id);
                
                fetch('ajax/sort-images.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ images: images })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Подредбата беше запазена');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Възникна грешка при запазването на подредбата', 'error');
                });
            }
        });
    }
    
    // Инициализация на Fancybox
    Fancybox.bind("[data-fancybox]", {
        // Опции за Fancybox
        Carousel: {
            infinite: false
        },
        Thumbs: {
            autoStart: true
        },
        Toolbar: {
            display: {
                left: ["infobar"],
                middle: [
                    "zoomIn",
                    "zoomOut",
                    "toggle1to1",
                    "rotateCCW",
                    "rotateCW",
                    "flipX",
                    "flipY",
                ],
                right: ["download", "slideshow", "thumbs", "close"]
            },
            items: {
                download: {
                    type: "button",
                    label: "Download",
                    icon: "icon-download",
                    click: function(event) {
                        const currentSlide = this.getSlide();
                        const imageId = currentSlide.$trigger.closest('.image-item').dataset.id;
                        
                        // Създаваме временен линк за изтегляне
                        const link = document.createElement('a');
                        link.href = `ajax/download-image.php?id=${imageId}`;
                        link.download = ''; // Браузърът ще използва оригиналното име на файла
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }
            }
        }
    });
}); 