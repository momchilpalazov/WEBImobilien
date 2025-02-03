<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Генериране на договор</h3>
                    <div class="card-tools">
                        <a href="/admin/properties/<?= $property['id'] ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="contractForm" action="/admin/contracts/generate/<?= $property['id'] ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Информация за имота</h5>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Адрес:</dt>
                                            <dd class="col-sm-8"><?= htmlspecialchars($property['address']) ?></dd>
                                            
                                            <dt class="col-sm-4">Площ:</dt>
                                            <dd class="col-sm-8"><?= $property['area'] ?> кв.м.</dd>
                                            
                                            <dt class="col-sm-4">Тип:</dt>
                                            <dd class="col-sm-8"><?= htmlspecialchars($property['type']) ?></dd>
                                            
                                            <dt class="col-sm-4">Цена:</dt>
                                            <dd class="col-sm-8"><?= number_format($property['price'], 2) ?> лв.</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Параметри на договора</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Тип договор -->
                                        <div class="mb-3">
                                            <label for="contract_type" class="form-label">Тип договор</label>
                                            <select class="form-select" id="contract_type" name="type" required>
                                                <option value="">Изберете тип договор</option>
                                                <option value="rental">Договор за наем</option>
                                                <option value="sale">Договор за продажба</option>
                                                <option value="preliminary">Предварителен договор</option>
                                                <option value="brokerage">Брокерски договор</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Данни за клиента -->
                                        <div class="mb-3">
                                            <label for="client_name" class="form-label">Име на клиента</label>
                                            <input type="text" class="form-control" id="client_name" name="client_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="client_egn" class="form-label">ЕГН</label>
                                            <input type="text" class="form-control" id="client_egn" name="client_egn" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="client_address" class="form-label">Адрес</label>
                                            <input type="text" class="form-control" id="client_address" name="client_address" required>
                                        </div>
                                        
                                        <!-- Полета за договор за наем -->
                                        <div id="rental_fields" class="contract-fields d-none">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="rental_period" class="form-label">Период (месеци)</label>
                                                        <input type="number" class="form-control" id="rental_period" name="rental_period" min="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="monthly_rent" class="form-label">Месечен наем</label>
                                                        <input type="number" class="form-control" id="monthly_rent" name="monthly_rent" min="0" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="deposit" class="form-label">Депозит</label>
                                                <input type="number" class="form-control" id="deposit" name="deposit" min="0" step="0.01">
                                            </div>
                                        </div>
                                        
                                        <!-- Полета за договор за продажба -->
                                        <div id="sale_fields" class="contract-fields d-none">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="price" class="form-label">Цена</label>
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

                                        <!-- Полета за брокерски договор -->
                                        <div id="brokerage_fields" class="contract-fields d-none">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="service_type" class="form-label">Тип услуга</label>
                                                        <select class="form-select" id="service_type" name="service_type">
                                                            <option value="продажба на недвижим имот">Продажба</option>
                                                            <option value="отдаване под наем на недвижим имот">Отдаване под наем</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="service_period" class="form-label">Срок на договора (месеци)</label>
                                                        <input type="number" class="form-control" id="service_period" name="service_period" min="1" value="6">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="commission_rate" class="form-label">Комисионна (%)</label>
                                                        <input type="number" class="form-control" id="commission_rate" name="commission_rate" min="0" max="100" step="0.1" value="3">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="agency_representative" class="form-label">Представител на агенцията</label>
                                                        <input type="text" class="form-control" id="agency_representative" name="agency_representative">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Генериране на договор
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contractType = document.getElementById('contract_type');
    const contractFields = document.querySelectorAll('.contract-fields');
    
    // Handle contract type change
    contractType.addEventListener('change', function() {
        // Hide all contract fields
        contractFields.forEach(fields => {
            fields.classList.add('d-none');
            
            // Remove required attribute from all inputs
            fields.querySelectorAll('input, select').forEach(input => {
                input.required = false;
            });
        });
        
        // Show selected contract fields
        const selectedFields = document.getElementById(this.value + '_fields');
        if (selectedFields) {
            selectedFields.classList.remove('d-none');
            
            // Add required attribute to visible inputs
            selectedFields.querySelectorAll('input, select').forEach(input => {
                input.required = true;
            });
        }
    });
    
    // Handle form submission
    document.getElementById('contractForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let loadingToast = new bootstrap.Toast(document.createElement('div'));
        loadingToast.show();
        
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
            } else {
                alert(data.message || 'Възникна грешка при генерирането на договора.');
            }
        })
        .catch(() => {
            loadingToast.hide();
            alert('Възникна грешка при генерирането на договора.');
        });
    });
});
</script>
<?php $this->end() ?> 