<?php require_once 'views/admin/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Редактиране на транзакция</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="/admin/transactions/edit/<?= $transaction->id ?>" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Тип транзакция</label>
                        <select name="type" class="form-select" required>
                            <option value="">Изберете тип</option>
                            <option value="sale" <?= $transaction->type === 'sale' ? 'selected' : '' ?>>Продажба</option>
                            <option value="rent" <?= $transaction->type === 'rent' ? 'selected' : '' ?>>Наем</option>
                            <option value="commission" <?= $transaction->type === 'commission' ? 'selected' : '' ?>>Комисионна</option>
                            <option value="expense" <?= $transaction->type === 'expense' ? 'selected' : '' ?>>Разход</option>
                            <option value="other" <?= $transaction->type === 'other' ? 'selected' : '' ?>>Друго</option>
                        </select>
                        <div class="invalid-feedback">Моля, изберете тип транзакция</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" <?= $transaction->status === 'pending' ? 'selected' : '' ?>>Чакаща</option>
                            <option value="completed" <?= $transaction->status === 'completed' ? 'selected' : '' ?>>Завършена</option>
                            <option value="cancelled" <?= $transaction->status === 'cancelled' ? 'selected' : '' ?>>Отказана</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Имот</label>
                        <select name="property_id" class="form-select">
                            <option value="">Изберете имот</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= $property->id ?>" <?= $transaction->property_id === $property->id ? 'selected' : '' ?>>
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
                                <option value="<?= $client->id ?>" <?= $transaction->client_id === $client->id ? 'selected' : '' ?>>
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
                                <option value="<?= $agent->id ?>" <?= $transaction->agent_id === $agent->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Дата на транзакция</label>
                        <input type="date" name="transaction_date" class="form-control" 
                               value="<?= $transaction->transaction_date ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Сума</label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control" 
                                   step="0.01" min="0" required
                                   value="<?= $transaction->amount ?>">
                            <select name="currency" class="form-select" style="max-width: 100px;">
                                <option value="EUR" <?= $transaction->currency === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                <option value="BGN" <?= $transaction->currency === 'BGN' ? 'selected' : '' ?>>BGN</option>
                                <option value="USD" <?= $transaction->currency === 'USD' ? 'selected' : '' ?>>USD</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Комисионна (%)</label>
                        <input type="number" name="commission_rate" class="form-control" 
                               step="0.1" min="0" max="100"
                               value="<?= $transaction->commission_rate ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Краен срок</label>
                        <input type="date" name="due_date" class="form-control"
                               value="<?= $transaction->due_date ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Метод на плащане</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Изберете метод</option>
                            <option value="bank_transfer" <?= $transaction->payment_method === 'bank_transfer' ? 'selected' : '' ?>>Банков превод</option>
                            <option value="cash" <?= $transaction->payment_method === 'cash' ? 'selected' : '' ?>>В брой</option>
                            <option value="card" <?= $transaction->payment_method === 'card' ? 'selected' : '' ?>>Карта</option>
                            <option value="other" <?= $transaction->payment_method === 'other' ? 'selected' : '' ?>>Друго</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Референтен номер</label>
                        <input type="text" name="reference_number" class="form-control"
                               value="<?= htmlspecialchars($transaction->reference_number) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($transaction->description) ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" 
                            onclick="if(confirm('Сигурни ли сте, че искате да изтриете тази транзакция?')) { 
                                window.location.href='/admin/transactions/delete/<?= $transaction->id ?>'; 
                            }">
                        Изтрий
                    </button>
                    
                    <div class="d-flex gap-2">
                        <a href="/admin/transactions" class="btn btn-secondary">Отказ</a>
                        <button type="submit" class="btn btn-primary">Запази</button>
                    </div>
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
    
    function updateCommissionVisibility() {
        if (typeSelect.value === 'sale' || typeSelect.value === 'rent') {
            commissionGroup.style.display = 'block';
        } else {
            commissionGroup.style.display = 'none';
        }
    }
    
    typeSelect.addEventListener('change', updateCommissionVisibility);
    updateCommissionVisibility(); // Initial state
})();
</script>

<?php require_once 'views/admin/layout/footer.php'; ?> 