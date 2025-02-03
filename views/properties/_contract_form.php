<div class="modal fade" id="contractModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Генериране на договор</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contractForm" action="/admin/contracts/generate/<?= $property['id'] ?>" method="POST">
                    <!-- Тип договор -->
                    <div class="mb-3">
                        <label for="contract_type" class="form-label">Тип договор</label>
                        <select class="form-select" id="contract_type" name="type" required>
                            <option value="">Изберете тип договор</option>
                            <option value="rental">Договор за наем</option>
                            <option value="sale">Договор за продажба</option>
                            <option value="preliminary">Предварителен договор</option>
                        </select>
                    </div>

                    <!-- Данни за клиента -->
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Име на клиента</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_egn" class="form-label">ЕГН</label>
                                <input type="text" class="form-control" id="client_egn" name="client_egn" required 
                                       pattern="\d{10}" title="ЕГН трябва да съдържа 10 цифри">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="client_phone" name="client_phone">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_address" class="form-label">Адрес</label>
                        <input type="text" class="form-control" id="client_address" name="client_address" required>
                    </div>

                    <!-- Полета за договор за наем -->
                    <div id="rental_fields" class="contract-fields d-none">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rental_period" class="form-label">Период (месеци)</label>
                                    <input type="number" class="form-control" id="rental_period" name="rental_period" min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="monthly_rent" class="form-label">Месечен наем</label>
                                    <input type="number" class="form-control" id="monthly_rent" name="monthly_rent" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="deposit" class="form-label">Депозит</label>
                                    <input type="number" class="form-control" id="deposit" name="deposit" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Полета за договор за продажба -->
                    <div id="sale_fields" class="contract-fields d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Продажна цена</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Начин на плащане</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="cash">В брой</option>
                                        <option value="bank">Банков превод</option>
                                        <option value="escrow">Ескроу сметка</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Полета за предварителен договор -->
                    <div id="preliminary_fields" class="contract-fields d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="deposit_amount" class="form-label">Капаро</label>
                                    <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="final_contract_date" class="form-label">Дата на окончателен договор</label>
                                    <input type="date" class="form-control" id="final_contract_date" name="final_contract_date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-contract me-2"></i>
                            Генерирай договор
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contractForm');
    const typeSelect = document.getElementById('contract_type');
    const contractFields = document.querySelectorAll('.contract-fields');
    
    // Показване/скриване на полетата според типа договор
    typeSelect.addEventListener('change', function() {
        contractFields.forEach(field => field.classList.add('d-none'));
        
        const selectedType = this.value;
        if (selectedType) {
            document.getElementById(selectedType + '_fields')?.classList.remove('d-none');
        }
    });
    
    // Валидация и изпращане на формата
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const type = typeSelect.value;
        if (!validateContractFields(type)) {
            return;
        }
        
        const loadingToast = showLoading('Генериране на договор...');

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
                window.location.href = data.url;
                bootstrap.Modal.getInstance(document.getElementById('contractModal')).hide();
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при генерирането на договора.');
        });
    });
    
    // Валидация на полетата според типа договор
    function validateContractFields(type) {
        const fields = document.getElementById(type + '_fields');
        if (!fields) return true;
        
        const requiredInputs = fields.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!input.value) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
});</script> 