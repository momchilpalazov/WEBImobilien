<?php require_once 'views/admin/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Нова транзакция</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="/admin/transactions/create" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Тип транзакция</label>
                        <select name="type" class="form-select" required>
                            <option value="">Изберете тип</option>
                            <option value="sale">Продажба</option>
                            <option value="rent">Наем</option>
                            <option value="commission">Комисионна</option>
                            <option value="expense">Разход</option>
                            <option value="other">Друго</option>
                        </select>
                        <div class="invalid-feedback">Моля, изберете тип транзакция</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select" required>
                            <option value="pending">Чакаща</option>
                            <option value="completed">Завършена</option>
                            <option value="cancelled">Отказана</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Имот</label>
                        <select name="property_id" class="form-select">
                            <option value="">Изберете имот</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= $property->id ?>">
                                    <?= htmlspecialchars($property->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Клиент</label>
                        <select name="client_id" class="form-select">
                            <option value="">Изберете клиент</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client->id ?>">
                                    <?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Агент</label>
                        <select name="agent_id" class="form-select">
                            <option value="">Изберете агент</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent->id ?>">
                                    <?= htmlspecialchars($agent->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Дата на транзакция</label>
                        <input type="date" name="transaction_date" class="form-control" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Сума</label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control" 
                                   step="0.01" min="0" required>
                            <select name="currency" class="form-select" style="max-width: 100px;">
                                <option value="EUR">EUR</option>
                                <option value="BGN">BGN</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Комисионна (%)</label>
                        <input type="number" name="commission_rate" class="form-control" 
                               step="0.1" min="0" max="100">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Краен срок</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Метод на плащане</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Изберете метод</option>
                            <option value="bank_transfer">Банков превод</option>
                            <option value="cash">В брой</option>
                            <option value="card">Карта</option>
                            <option value="other">Друго</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Референтен номер</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/admin/transactions" class="btn btn-secondary">Отказ</a>
                    <button type="submit" class="btn btn-primary">Създай</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Dynamic commission rate field
    const typeSelect = document.querySelector('select[name="type"]');
    const commissionGroup = document.querySelector('input[name="commission_rate"]').closest('.col-md-4');
    
    typeSelect.addEventListener('change', function() {
        if (this.value === 'sale' || this.value === 'rent') {
            commissionGroup.style.display = 'block';
        } else {
            commissionGroup.style.display = 'none';
        }
    });
})();
</script>

<?php require_once 'views/admin/layout/footer.php'; ?> 