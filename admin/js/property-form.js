document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('propertyForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Изчакваме TinyMCE да обнови textarea полетата
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }
        
        const formData = new FormData(this);
        
        // Показваме индикатор за зареждане
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass"></i> Запазване...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Показваме съобщение за успех
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Успех!</strong> Имотът беше обновен успешно.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                form.insertBefore(alert, form.firstChild);
                
                // Пренасочваме към списъка с имоти след 1 секунда
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                throw new Error(data.message || 'Възникна грешка при запазване на имота');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Показваме съобщение за грешка
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Грешка!</strong> ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            form.insertBefore(alert, form.firstChild);
        })
        .finally(() => {
            // Възстановяваме бутона
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
}); 