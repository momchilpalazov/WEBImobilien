document.addEventListener('DOMContentLoaded', function() {
    // Форма за запитване
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Проверка на reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                alert('Моля, потвърдете, че не сте робот');
                return;
            }

            const formData = new FormData(this);
            formData.append('g-recaptcha-response', recaptchaResponse);

            try {
                const response = await fetch('/ajax/send-inquiry.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Показване на съобщение за успех
                    alert(result.message);
                    // Изчистване на формата
                    inquiryForm.reset();
                    // Ресетване на reCAPTCHA
                    grecaptcha.reset();
                } else {
                    // Показване на съобщение за грешка
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Възникна грешка. Моля, опитайте отново.');
            }
        });
    }

    // Галерия
    const galleryModal = document.getElementById('galleryModal');
    if (galleryModal) {
        const modalImage = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalCaption');

        document.querySelectorAll('.gallery-thumbnail').forEach(img => {
            img.addEventListener('click', function() {
                modalImage.src = this.dataset.image;
                modalCaption.textContent = this.dataset.caption;
            });
        });
    }
}); 