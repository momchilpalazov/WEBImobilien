<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Клиенти</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">
                        <i class="fas fa-plus me-2"></i>
                        Нов клиент
                    </button>
                </div>
                <div class="card-body">
                    <!-- Филтри -->
                    <form id="filterForm" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="filter_type" class="form-label">Тип клиент</label>
                            <select class="form-select" id="filter_type" name="filter[type]">
                                <option value="">Всички</option>
                                <option value="buyer" <?= $filter['type'] === 'buyer' ? 'selected' : '' ?>>Купувач</option>
                                <option value="seller" <?= $filter['type'] === 'seller' ? 'selected' : '' ?>>Продавач</option>
                                <option value="tenant" <?= $filter['type'] === 'tenant' ? 'selected' : '' ?>>Наемател</option>
                                <option value="landlord" <?= $filter['type'] === 'landlord' ? 'selected' : '' ?>>Наемодател</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status" class="form-label">Статус</label>
                            <select class="form-select" id="filter_status" name="filter[status]">
                                <option value="">Всички</option>
                                <option value="active" <?= $filter['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                                <option value="inactive" <?= $filter['status'] === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_source" class="form-label">Източник</label>
                            <select class="form-select" id="filter_source" name="filter[source]">
                                <option value="">Всички</option>
                                <option value="website" <?= $filter['source'] === 'website' ? 'selected' : '' ?>>Уебсайт</option>
                                <option value="referral" <?= $filter['source'] === 'referral' ? 'selected' : '' ?>>Препоръка</option>
                                <option value="social" <?= $filter['source'] === 'social' ? 'selected' : '' ?>>Социални мрежи</option>
                                <option value="other" <?= $filter['source'] === 'other' ? 'selected' : '' ?>>Друго</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_search" class="form-label">Търсене</label>
                            <input type="text" class="form-control" id="filter_search" name="filter[search]" 
                                   value="<?= htmlspecialchars($filter['search'] ?? '') ?>" 
                                   placeholder="Име, имейл, телефон...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>
                                Филтрирай
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearFilters">
                                <i class="fas fa-times me-2"></i>
                                Изчисти филтрите
                            </button>
                        </div>
                    </form>

                    <!-- Таблица с клиенти -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Име</th>
                                    <th>Контакти</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th>Източник</th>
                                    <th>Последно взаимодействие</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td>
                                            <a href="/admin/clients/<?= $client['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($client['name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($client['email']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($client['phone']) ?></div>
                                        </td>
                                        <td>
                                            <?php
                                            $typeLabels = [
                                                'buyer' => 'Купувач',
                                                'seller' => 'Продавач',
                                                'tenant' => 'Наемател',
                                                'landlord' => 'Наемодател'
                                            ];
                                            echo $typeLabels[$client['type']] ?? 'Неизвестен';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($client['status'] === 'active'): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Неактивен</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $sourceLabels = [
                                                'website' => 'Уебсайт',
                                                'referral' => 'Препоръка',
                                                'social' => 'Социални мрежи',
                                                'other' => 'Друго'
                                            ];
                                            echo $sourceLabels[$client['source']] ?? 'Неизвестен';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($client['last_interaction']): ?>
                                                <div><?= date('d.m.Y H:i', strtotime($client['last_interaction']['created_at'])) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($client['last_interaction']['description']) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">Няма</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/admin/clients/<?= $client['id'] ?>" class="btn btn-sm btn-info text-white" title="Преглед">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="/admin/clients/<?= $client['id'] ?>/edit" class="btn btn-sm btn-warning" title="Редактиране">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-success quick-interaction" 
                                                        data-id="<?= $client['id'] ?>" title="Бързо взаимодействие">
                                                    <i class="fas fa-comment"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за създаване на клиент -->
<div class="modal fade" id="createClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Нов клиент</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createClientForm" action="/admin/clients" method="POST">
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Име</label>
                        <input type="text" class="form-control" id="client_name" name="name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_email" class="form-label">Имейл</label>
                                <input type="email" class="form-control" id="client_email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="client_phone" name="phone" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_address" class="form-label">Адрес</label>
                        <input type="text" class="form-control" id="client_address" name="address">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_type" class="form-label">Тип клиент</label>
                                <select class="form-select" id="client_type" name="type" required>
                                    <option value="buyer">Купувач</option>
                                    <option value="seller">Продавач</option>
                                    <option value="tenant">Наемател</option>
                                    <option value="landlord">Наемодател</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_source" class="form-label">Източник</label>
                                <select class="form-select" id="client_source" name="source">
                                    <option value="website">Уебсайт</option>
                                    <option value="referral">Препоръка</option>
                                    <option value="social">Социални мрежи</option>
                                    <option value="other">Друго</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_notes" class="form-label">Бележки</label>
                        <textarea class="form-control" id="client_notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Създай клиент
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за бързо взаимодействие -->
<div class="modal fade" id="quickInteractionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Бързо взаимодействие</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickInteractionForm" action="/admin/clients/{id}/interactions" method="POST">
                    <input type="hidden" name="type" value="note">
                    
                    <div class="mb-3">
                        <label for="interaction_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="interaction_description" name="description" rows="3" required></textarea>
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
    // Филтриране
    const filterForm = document.getElementById('filterForm');
    const clearFiltersBtn = document.getElementById('clearFilters');

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/admin/clients?' + params.toString();
    });

    clearFiltersBtn.addEventListener('click', function() {
        window.location.href = '/admin/clients';
    });

    // Създаване на клиент
    const createClientForm = document.getElementById('createClientForm');
    createClientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Създаване на клиент...');

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
                bootstrap.Modal.getInstance(document.getElementById('createClientModal')).hide();
                window.location.href = `/admin/clients/${data.client_id}`;
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при създаване на клиента.');
        });
    });

    // Бързо взаимодействие
    const quickInteractionBtns = document.querySelectorAll('.quick-interaction');
    const quickInteractionForm = document.getElementById('quickInteractionForm');
    
    quickInteractionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.dataset.id;
            quickInteractionForm.action = `/admin/clients/${clientId}/interactions`;
            new bootstrap.Modal(document.getElementById('quickInteractionModal')).show();
        });
    });

    quickInteractionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Записване...');

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
                bootstrap.Modal.getInstance(document.getElementById('quickInteractionModal')).hide();
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

<?php include_once '../layouts/footer.php'; ?> 