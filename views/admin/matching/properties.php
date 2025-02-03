<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Подходящи имоти за <?= htmlspecialchars($client['name']) ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Предпочитания на клиента</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $preferences = json_decode($client['preferences'] ?? '{}', true) ?: [];
                                    ?>
                                    <dl class="row">
                                        <dt class="col-sm-4">Тип имот</dt>
                                        <dd class="col-sm-8"><?= $preferences['property_type'] ?? 'Не е зададен' ?></dd>
                                        
                                        <dt class="col-sm-4">Цена</dt>
                                        <dd class="col-sm-8">
                                            <?php if (isset($preferences['price_min']) && isset($preferences['price_max'])): ?>
                                                <?= number_format($preferences['price_min']) ?> - <?= number_format($preferences['price_max']) ?> €
                                            <?php else: ?>
                                                Не е зададена
                                            <?php endif; ?>
                                        </dd>
                                        
                                        <dt class="col-sm-4">Площ</dt>
                                        <dd class="col-sm-8">
                                            <?php if (isset($preferences['area_min']) && isset($preferences['area_max'])): ?>
                                                <?= number_format($preferences['area_min']) ?> - <?= number_format($preferences['area_max']) ?> м²
                                            <?php else: ?>
                                                Не е зададена
                                            <?php endif; ?>
                                        </dd>
                                        
                                        <dt class="col-sm-4">Локации</dt>
                                        <dd class="col-sm-8">
                                            <?php if (!empty($preferences['locations'])): ?>
                                                <?= implode(', ', $preferences['locations']) ?>
                                            <?php else: ?>
                                                Не са зададени
                                            <?php endif; ?>
                                        </dd>
                                        
                                        <dt class="col-sm-4">Характеристики</dt>
                                        <dd class="col-sm-8">
                                            <?php if (!empty($preferences['required_features'])): ?>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($preferences['required_features'] as $feature): ?>
                                                        <li><?= htmlspecialchars($feature) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                Не са зададени
                                            <?php endif; ?>
                                        </dd>
                                    </dl>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#preferencesModal">
                                            <i class="fas fa-edit"></i> Редактиране
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <?php if (empty($matches)): ?>
                                <div class="alert alert-info">
                                    Не са намерени подходящи имоти според зададените критерии.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($matches as $match): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <?php if (!empty($match['property']['main_image'])): ?>
                                                    <img src="/uploads/properties/<?= $match['property']['main_image'] ?>" 
                                                         class="card-img-top" 
                                                         alt="<?= htmlspecialchars($match['property']['title_bg']) ?>">
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h5 class="card-title mb-0">
                                                            <?= htmlspecialchars($match['property']['title_bg']) ?>
                                                        </h5>
                                                        <span class="badge bg-success">
                                                            <?= round($match['match_details']['overall_score']) ?>% съвпадение
                                                        </span>
                                                    </div>
                                                    
                                                    <p class="text-muted mb-2">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <?= htmlspecialchars($match['property']['location']) ?>
                                                    </p>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <small class="text-muted d-block">Цена</small>
                                                            <strong><?= number_format($match['property']['price']) ?> €</strong>
                                                        </div>
                                                        <div class="col">
                                                            <small class="text-muted d-block">Площ</small>
                                                            <strong><?= number_format($match['property']['area']) ?> м²</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="match-details mb-3">
                                                        <small class="text-muted d-block mb-2">Съвпадение по критерии:</small>
                                                        <?php foreach ($match['match_details']['criteria_scores'] as $criterion => $score): ?>
                                                            <div class="d-flex align-items-center mb-1">
                                                                <small class="text-muted" style="width: 100px;">
                                                                    <?= ucfirst($criterion) ?>:
                                                                </small>
                                                                <div class="progress flex-grow-1" style="height: 5px;">
                                                                    <div class="progress-bar" 
                                                                         role="progressbar" 
                                                                         style="width: <?= $score ?>%"
                                                                         aria-valuenow="<?= $score ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100"></div>
                                                                </div>
                                                                <small class="ms-2"><?= round($score) ?>%</small>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <a href="/admin/properties/<?= $match['property']['id'] ?>" 
                                                           class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Преглед
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-success"
                                                                onclick="sendToClient(<?= $match['property']['id'] ?>)">
                                                            <i class="fas fa-paper-plane"></i> Изпращане
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за редактиране на предпочитания -->
<div class="modal fade" id="preferencesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактиране на предпочитания</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="preferencesForm" action="/admin/matching/preferences/<?= $client['id'] ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="property_type" class="form-label">Тип имот</label>
                                <select class="form-select" id="property_type" name="property_type">
                                    <option value="">Изберете тип</option>
                                    <?php foreach ($propertyTypes as $type => $label): ?>
                                        <option value="<?= $type ?>" 
                                                <?= ($preferences['property_type'] ?? '') === $type ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price_min" class="form-label">Минимална цена</label>
                                        <input type="number" class="form-control" id="price_min" name="price_min"
                                               value="<?= $preferences['price_min'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price_max" class="form-label">Максимална цена</label>
                                        <input type="number" class="form-control" id="price_max" name="price_max"
                                               value="<?= $preferences['price_max'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="area_min" class="form-label">Минимална площ</label>
                                        <input type="number" class="form-control" id="area_min" name="area_min"
                                               value="<?= $preferences['area_min'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="area_max" class="form-label">Максимална площ</label>
                                        <input type="number" class="form-control" id="area_max" name="area_max"
                                               value="<?= $preferences['area_max'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="locations" class="form-label">Локации</label>
                                <select class="form-select" id="locations" name="locations[]" multiple>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location ?>"
                                                <?= in_array($location, $preferences['locations'] ?? []) ? 'selected' : '' ?>>
                                            <?= $location ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="required_features" class="form-label">Задължителни характеристики</label>
                                <select class="form-select" id="required_features" name="required_features[]" multiple>
                                    <?php foreach ($features as $feature): ?>
                                        <option value="<?= $feature ?>"
                                                <?= in_array($feature, $preferences['required_features'] ?? []) ? 'selected' : '' ?>>
                                            <?= $feature ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="savePreferences()">Запазване</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
function savePreferences() {
    const form = document.getElementById('preferencesForm');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Възникна грешка при запазване на предпочитанията.');
        }
    })
    .catch(() => {
        alert('Възникна грешка при запазване на предпочитанията.');
    });
}

function sendToClient(propertyId) {
    if (!confirm('Сигурни ли сте, че искате да изпратите този имот на клиента?')) {
        return;
    }
    
    fetch(`/admin/matching/send/${propertyId}/${<?= $client['id'] ?>}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Имотът е изпратен успешно на клиента.');
        } else {
            alert(data.message || 'Възникна грешка при изпращане на имота.');
        }
    })
    .catch(() => {
        alert('Възникна грешка при изпращане на имота.');
    });
}

// Инициализация на Select2 за множествен избор
$(document).ready(function() {
    $('#locations, #required_features').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
<?php $this->end() ?> 