<div class="modal fade" id="viewingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Насрочване на оглед</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="viewingForm" action="/admin/viewings/schedule" method="POST">
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

                    <!-- Данни за клиента -->
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Име на клиента</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="client_phone" name="client_phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_email" class="form-label">Имейл</label>
                                <input type="email" class="form-control" id="client_email" name="client_email">
                            </div>
                        </div>
                    </div>

                    <!-- Дата и час -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="viewing_date" class="form-label">Дата</label>
                                <input type="date" class="form-control" id="viewing_date" name="date" required
                                       min="<?= date('Y-m-d') ?>"
                                       max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="viewing_time" class="form-label">Час</label>
                                <select class="form-select" id="viewing_time" name="time" required>
                                    <option value="">Изберете час</option>
                                    <?php for ($hour = 9; $hour < 18; $hour++): ?>
                                        <option value="<?= sprintf('%02d:00', $hour) ?>"><?= sprintf('%02d:00', $hour) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Бележки -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Бележки</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>
                            Насрочи оглед
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('viewingForm');
    const dateInput = document.getElementById('viewing_date');
    const timeSelect = document.getElementById('viewing_time');
    
    // Минимална дата - днес + 2 часа
    const now = new Date();
    const minDate = new Date(now.getTime() + 2 * 60 * 60 * 1000);
    dateInput.min = minDate.toISOString().split('T')[0];
    
    // Максимална дата - 30 дни напред
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 30);
    dateInput.max = maxDate.toISOString().split('T')[0];
    
    // Актуализиране на наличните часове според избраната дата
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        
        // Ако е избран днешния ден, деактивираме миналите часове
        if (selectedDate.toDateString() === today.toDateString()) {
            const currentHour = today.getHours();
            
            Array.from(timeSelect.options).forEach(option => {
                if (option.value) {
                    const hour = parseInt(option.value);
                    option.disabled = hour <= (currentHour + 2);
                }
            });
        } else {
            // За други дни всички часове са налични
            Array.from(timeSelect.options).forEach(option => {
                option.disabled = false;
            });
        }
        
        // Ако избраният час е деактивиран, нулираме избора
        if (timeSelect.selectedOptions[0].disabled) {
            timeSelect.value = '';
        }
    });
    
    // Валидация и изпращане на формата
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Насрочване на оглед...');

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingToast.hide();
            
            if (data.success) {
                showNotification('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('viewingModal')).hide();
                // Опционално: презареждане на календара или списъка с огледи
                if (typeof reloadViewings === 'function') {
                    reloadViewings();
                }
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при насрочване на огледа.');
        });
    });
});</script> 