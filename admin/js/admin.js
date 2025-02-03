// Изчистване на кеша при зареждане
window.onload = function() {
    if (window.location.href.indexOf('edit-property.php') > -1) {
        // Добавяме timestamp към URL-а за предотвратяване на кеширането
        const timestamp = new Date().getTime();
        const currentUrl = window.location.href;
        const separator = currentUrl.indexOf('?') > -1 ? '&' : '?';
        const newUrl = `${currentUrl}${separator}_=${timestamp}`;
        window.history.replaceState({}, '', newUrl);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initDataTables();
});

function initDataTables() {
    const tables = document.querySelectorAll('.datatable');
    tables.forEach(table => {
        new DataTable(table, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/bg.json'
            }
        });
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Запазване на съдържанието от всички редактори
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            
            if (validateForm(this)) {
                const formData = new FormData(this);
                const propertyId = formData.get('id');
                const endpoint = propertyId ? 'ajax/save-property.php' : 'ajax/add-property.php';
                
                // Показваме индикатор за зареждане
                const submitButton = this.querySelector('[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Запазване...';
                }
                
                fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        console.error('Server response:', response);
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Error parsing response:', text);
                                throw new Error('Грешка при обработка на отговора от сървъра');
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Server response data:', data);
                    if (data.success) {
                        showNotification(data.message || 'Имотът е запазен успешно', 'success');
                        if (!propertyId && data.propertyId) {
                            // Ако е нов имот, пренасочваме към страницата за редактиране
                            window.location.href = 'edit-property.php?id=' + data.propertyId + '&new=1';
                        } else {
                            // Ако редактираме съществуващ имот, само презареждаме страницата
                            window.location.reload();
                        }
                    } else {
                        console.error('Error from server:', data);
                        showNotification(data.message || 'Възникна грешка при запазване', 'error');
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = '<i class="bi bi-save"></i> Запази';
                        }
                    }
                })
                .catch(error => {
                    console.error('Request error:', error);
                    showNotification(error.message || 'Възникна грешка при запазване', 'error');
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="bi bi-save"></i> Запази';
                    }
                });
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    
    // Изчистване на предишни съобщения за грешки
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    // Проверка на задължителни полета
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Това поле е задължително';
            field.parentNode.appendChild(feedback);
        }
    });

    // Проверка на TinyMCE редакторите
    if (typeof tinymce !== 'undefined') {
        tinymce.get().forEach(editor => {
            const content = editor.getContent().trim();
            if (editor.getElement().hasAttribute('required') && !content) {
                isValid = false;
                const editorElement = editor.getContainer();
                editorElement.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Това поле е задължително';
                editorElement.parentNode.appendChild(feedback);
            }
        });
    }
    
    return isValid;
}

function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const container = document.getElementById('toast-container');
    if (!container) {
        const newContainer = document.createElement('div');
        newContainer.id = 'toast-container';
        newContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(newContainer);
    }
    
    document.getElementById('toast-container').appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
} 