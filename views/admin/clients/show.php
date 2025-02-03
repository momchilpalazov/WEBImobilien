<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Основна информация -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Информация за клиента</h5>
                    <a href="/admin/clients/<?= $client['id'] ?>/edit" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Редактирай
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Име</label>
                        <div class="fw-bold"><?= htmlspecialchars($client['name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Имейл</label>
                        <div>
                            <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client['email']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Телефон</label>
                        <div>
                            <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Адрес</label>
                        <div><?= htmlspecialchars($client['address'] ?? 'Не е посочен') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Тип клиент</label>
                        <div>
                            <?php
                            $typeLabels = [
                                'buyer' => 'Купувач',
                                'seller' => 'Продавач',
                                'tenant' => 'Наемател',
                                'landlord' => 'Наемодател'
                            ];
                            echo $typeLabels[$client['type']] ?? 'Неизвестен';
                            ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Статус</label>
                        <div>
                            <?php if ($client['status'] === 'active'): ?>
                                <span class="badge bg-success">Активен</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Неактивен</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Източник</label>
                        <div>
                            <?php
                            $sourceLabels = [
                                'website' => 'Уебсайт',
                                'referral' => 'Препоръка',
                                'social' => 'Социални мрежи',
                                'other' => 'Друго'
                            ];
                            echo $sourceLabels[$client['source']] ?? 'Неизвестен';
                            ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Бележки</label>
                        <div><?= nl2br(htmlspecialchars($client['notes'] ?? '')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Предпочитания -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Предпочитания за имоти</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#preferencesModal">
                        <i class="fas fa-edit me-2"></i>
                        Редактирай
                    </button>
                </div>
                <div class="card-body">
                    <?php
                    $preferences = json_decode($client['preferences'], true) ?? [];
                    ?>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Тип имот</label>
                        <div>
                            <?php if (!empty($preferences['property_type'])): ?>
                                <?php foreach ($preferences['property_type'] as $type): ?>
                                    <span class="badge bg-info me-1"><?= htmlspecialchars($type) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Не е посочено</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Ценови диапазон</label>
                        <div>
                            <?php if (!empty($preferences['price_range']['min']) || !empty($preferences['price_range']['max'])): ?>
                                <?= number_format($preferences['price_range']['min'] ?? 0) ?> € - 
                                <?= !empty($preferences['price_range']['max']) ? number_format($preferences['price_range']['max']) . ' €' : 'Без лимит' ?>
                            <?php else: ?>
                                <span class="text-muted">Не е посочено</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Площ</label>
                        <div>
                            <?php if (!empty($preferences['area_range']['min']) || !empty($preferences['area_range']['max'])): ?>
                                <?= number_format($preferences['area_range']['min'] ?? 0) ?> m² - 
                                <?= !empty($preferences['area_range']['max']) ? number_format($preferences['area_range']['max']) . ' m²' : 'Без лимит' ?>
                            <?php else: ?>
                                <span class="text-muted">Не е посочено</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Локации</label>
                        <div>
                            <?php if (!empty($preferences['locations'])): ?>
                                <?php foreach ($preferences['locations'] as $location): ?>
                                    <span class="badge bg-info me-1"><?= htmlspecialchars($location) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Не е посочено</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Характеристики</label>
                        <div>
                            <?php if (!empty($preferences['features'])): ?>
                                <?php foreach ($preferences['features'] as $feature): ?>
                                    <span class="badge bg-info me-1"><?= htmlspecialchars($feature) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Не е посочено</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Тип сделка</label>
                        <div>
                            <?php
                            $transactionLabels = [
                                'buy' => 'Покупка',
                                'rent' => 'Наем',
                                'any' => 'Без значение'
                            ];
                            echo $transactionLabels[$preferences['transaction_type'] ?? 'any'] ?? 'Не е посочено';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Подходящи имоти -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Подходящи имоти</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($matchingProperties)): ?>
                        <div class="list-group">
                            <?php foreach ($matchingProperties as $property): ?>
                                <a href="/admin/properties/<?= $property['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($property['title']) ?></h6>
                                        <small class="text-primary"><?= number_format($property['price']) ?> €</small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($property['address']) ?></p>
                                    <small class="text-muted"><?= $property['area'] ?> m² • <?= $property['rooms'] ?> стаи</small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-search mb-2"></i>
                            <p>Няма намерени подходящи имоти</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- История на взаимодействията -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">История на взаимодействията</h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#interactionModal">
                        <i class="fas fa-plus me-2"></i>
                        Ново взаимодействие
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($history)): ?>
                        <div class="timeline">
                            <?php foreach ($history as $interaction): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?= date('d.m.Y H:i', strtotime($interaction['created_at'])) ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="mb-2">
                                            <span class="badge bg-info"><?= htmlspecialchars($interaction['type']) ?></span>
                                        </div>
                                        <p><?= nl2br(htmlspecialchars($interaction['description'])) ?></p>
                                        <?php if (!empty($interaction['data'])): ?>
                                            <div class="small text-muted">
                                                <?php
                                                $data = json_decode($interaction['data'], true);
                                                foreach ($data as $key => $value) {
                                                    echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '<br>';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="small text-muted mt-2">
                                            Създадено от: <?= htmlspecialchars($interaction['created_by_name']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-history mb-2"></i>
                            <p>Няма записани взаимодействия</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за редактиране на предпочитания -->
<div class="modal fade" id="preferencesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактиране на предпочитания</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="preferencesForm" action="/admin/clients/<?= $client['id'] ?>/preferences" method="POST">
                    <?php
                    $preferences = json_decode($client['preferences'], true) ?? [];
                    ?>

                    <div class="mb-3">
                        <label for="property_type" class="form-label">Тип имот</label>
                        <select class="form-select" id="property_type" name="preferences[property_type][]" multiple>
                            <option value="apartment" <?= in_array('apartment', $preferences['property_type'] ?? []) ? 'selected' : '' ?>>Апартамент</option>
                            <option value="house" <?= in_array('house', $preferences['property_type'] ?? []) ? 'selected' : '' ?>>Къща</option>
                            <option value="office" <?= in_array('office', $preferences['property_type'] ?? []) ? 'selected' : '' ?>>Офис</option>
                            <option value="land" <?= in_array('land', $preferences['property_type'] ?? []) ? 'selected' : '' ?>>Парцел</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_min" class="form-label">Минимална цена (€)</label>
                                <input type="number" class="form-control" id="price_min" name="preferences[price_min]"
                                       value="<?= $preferences['price_range']['min'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_max" class="form-label">Максимална цена (€)</label>
                                <input type="number" class="form-control" id="price_max" name="preferences[price_max]"
                                       value="<?= $preferences['price_range']['max'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="area_min" class="form-label">Минимална площ (m²)</label>
                                <input type="number" class="form-control" id="area_min" name="preferences[area_min]"
                                       value="<?= $preferences['area_range']['min'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="area_max" class="form-label">Максимална площ (m²)</label>
                                <input type="number" class="form-control" id="area_max" name="preferences[area_max]"
                                       value="<?= $preferences['area_range']['max'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="locations" class="form-label">Локации</label>
                        <select class="form-select" id="locations" name="preferences[locations][]" multiple>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>" 
                                        <?= in_array($location['id'], $preferences['locations'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($location['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="features" class="form-label">Характеристики</label>
                        <select class="form-select" id="features" name="preferences[features][]" multiple>
                            <?php foreach ($features as $feature): ?>
                                <option value="<?= $feature['id'] ?>"
                                        <?= in_array($feature['id'], $preferences['features'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($feature['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_type" class="form-label">Тип сделка</label>
                        <select class="form-select" id="transaction_type" name="preferences[transaction_type]">
                            <option value="any" <?= ($preferences['transaction_type'] ?? 'any') === 'any' ? 'selected' : '' ?>>Без значение</option>
                            <option value="buy" <?= ($preferences['transaction_type'] ?? '') === 'buy' ? 'selected' : '' ?>>Покупка</option>
                            <option value="rent" <?= ($preferences['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>Наем</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notifications_enabled" 
                                   name="preferences[notifications_enabled]" value="1"
                                   <?= ($preferences['notifications_enabled'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="notifications_enabled">
                                Известия за подходящи имоти
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Запази
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за ново взаимодействие -->
<div class="modal fade" id="interactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ново взаимодействие</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interactionForm" action="/admin/clients/<?= $client['id'] ?>/interactions" method="POST">
                    <div class="mb-3">
                        <label for="interaction_type" class="form-label">Тип взаимодействие</label>
                        <select class="form-select" id="interaction_type" name="type" required>
                            <option value="call">Обаждане</option>
                            <option value="email">Имейл</option>
                            <option value="meeting">Среща</option>
                            <option value="viewing">Оглед</option>
                            <option value="note">Бележка</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="interaction_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="interaction_description" name="description" rows="3" required></textarea>
                    </div>

                    <div id="additionalFields" class="d-none">
                        <!-- Допълнителни полета според типа взаимодействие -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>
                            Запиши
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация на select2 за множествен избор
    $('#property_type, #locations, #features').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Форма за предпочитания
    const preferencesForm = document.getElementById('preferencesForm');
    preferencesForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Записване на предпочитания...');

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
                bootstrap.Modal.getInstance(document.getElementById('preferencesModal')).hide();
                location.reload();
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при записване на предпочитанията.');
        });
    });

    // Форма за взаимодействие
    const interactionForm = document.getElementById('interactionForm');
    const interactionType = document.getElementById('interaction_type');
    const additionalFields = document.getElementById('additionalFields');

    // Показване на допълнителни полета според типа взаимодействие
    interactionType.addEventListener('change', function() {
        additionalFields.innerHTML = '';
        additionalFields.classList.add('d-none');

        const type = this.value;
        let fields = '';

        switch (type) {
            case 'call':
                fields = `
                    <div class="mb-3">
                        <label class="form-label">Продължителност (минути)</label>
                        <input type="number" class="form-control" name="additional_data[duration]" min="1">
                    </div>
                `;
                break;
            case 'meeting':
                fields = `
                    <div class="mb-3">
                        <label class="form-label">Място на срещата</label>
                        <input type="text" class="form-control" name="additional_data[location]">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Продължителност (минути)</label>
                        <input type="number" class="form-control" name="additional_data[duration]" min="1">
                    </div>
                `;
                break;
            case 'viewing':
                fields = `
                    <div class="mb-3">
                        <label class="form-label">Имот</label>
                        <select class="form-select" name="additional_data[property_id]">
                            <?php foreach ($matchingProperties as $property): ?>
                                <option value="<?= $property['id'] ?>">
                                    <?= htmlspecialchars($property['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                `;
                break;
        }

        if (fields) {
            additionalFields.innerHTML = fields;
            additionalFields.classList.remove('d-none');
        }
    });

    interactionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Записване на взаимодействие...');

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
                bootstrap.Modal.getInstance(document.getElementById('interactionModal')).hide();
                location.reload();
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при записване на взаимодействието.');
        });
    });
});</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: -30px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    bottom: 0;
}

.timeline-item:after {
    content: '';
    position: absolute;
    left: -4px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #007bff;
}

.timeline-date {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}
</style>

<?php include_once '../layouts/footer.php'; ?> 