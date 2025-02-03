<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Договори</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-filter"></i> Филтри
                        </button>
                    </div>
                </div>
                
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="/admin/contracts" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter_type" class="form-label">Тип договор</label>
                                        <select class="form-select" id="filter_type" name="filter[type]">
                                            <option value="">Всички</option>
                                            <option value="rental" <?= ($filters['type'] ?? '') === 'rental' ? 'selected' : '' ?>>Наем</option>
                                            <option value="sale" <?= ($filters['type'] ?? '') === 'sale' ? 'selected' : '' ?>>Продажба</option>
                                            <option value="preliminary" <?= ($filters['type'] ?? '') === 'preliminary' ? 'selected' : '' ?>>Предварителен</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter_status" class="form-label">Статус</label>
                                        <select class="form-select" id="filter_status" name="filter[status]">
                                            <option value="">Всички</option>
                                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Чернова</option>
                                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
                                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Приключен</option>
                                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Отказан</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter_date_from" class="form-label">От дата</label>
                                        <input type="date" class="form-control" id="filter_date_from" name="filter[date_from]" value="<?= $filters['date_from'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter_date_to" class="form-label">До дата</label>
                                        <input type="date" class="form-control" id="filter_date_to" name="filter[date_to]" value="<?= $filters['date_to'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Търсене
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearFilters">
                                        <i class="fas fa-times"></i> Изчистване
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Дата</th>
                                    <th>Тип</th>
                                    <th>Имот</th>
                                    <th>Клиент</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td><?= $contract['number'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($contract['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $types = [
                                            'rental' => 'Наем',
                                            'sale' => 'Продажба',
                                            'preliminary' => 'Предварителен'
                                        ];
                                        echo $types[$contract['type']] ?? $contract['type'];
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($contract['property_address']) ?></td>
                                    <td><?= htmlspecialchars($contract['client_name']) ?></td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'draft' => 'badge bg-secondary',
                                            'active' => 'badge bg-success',
                                            'completed' => 'badge bg-info',
                                            'cancelled' => 'badge bg-danger'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Чернова',
                                            'active' => 'Активен',
                                            'completed' => 'Приключен',
                                            'cancelled' => 'Отказан'
                                        ];
                                        $class = $statusClasses[$contract['status']] ?? 'badge bg-secondary';
                                        $label = $statusLabels[$contract['status']] ?? $contract['status'];
                                        ?>
                                        <span class="<?= $class ?>"><?= $label ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/contracts/download/<?= $contract['filename'] ?>" class="btn btn-sm btn-info" title="Изтегляне">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <?php if ($contract['status'] === 'draft'): ?>
                                            <button type="button" class="btn btn-sm btn-success update-status" data-id="<?= $contract['id'] ?>" data-status="active" title="Активиране">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php endif ?>
                                            <?php if ($contract['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-info update-status" data-id="<?= $contract['id'] ?>" data-status="completed" title="Приключване">
                                                <i class="fas fa-flag-checkered"></i>
                                            </button>
                                            <?php endif ?>
                                            <?php if (in_array($contract['status'], ['draft', 'active'])): ?>
                                            <button type="button" class="btn btn-sm btn-danger update-status" data-id="<?= $contract['id'] ?>" data-status="cancelled" title="Отказване">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif ?>
                                            <?php if ($contract['status'] === 'draft'): ?>
                                            <button type="button" class="btn btn-sm btn-danger delete-contract" data-id="<?= $contract['id'] ?>" title="Изтриване">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for delete confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Потвърждение за изтриване</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Сигурни ли сте, че искате да изтриете този договор?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Изтриване</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for status update confirmation -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Потвърждение за промяна на статус</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Сигурни ли сте, че искате да промените статуса на договора?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" id="confirmStatus">Промяна</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    let contractToDelete = null;
    let contractToUpdate = null;
    let statusToSet = null;
    
    // Handle filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let params = new URLSearchParams(formData);
        window.location.href = this.action + '?' + params.toString();
    });
    
    // Handle clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        window.location.href = '/admin/contracts';
    });
    
    // Handle delete button click
    document.querySelectorAll('.delete-contract').forEach(button => {
        button.addEventListener('click', function() {
            contractToDelete = this.dataset.id;
            deleteModal.show();
        });
    });
    
    // Handle delete confirmation
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!contractToDelete) return;
        
        fetch(`/admin/contracts/delete/${contractToDelete}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            deleteModal.hide();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Възникна грешка при изтриването на договора.');
            }
        })
        .catch(() => {
            deleteModal.hide();
            alert('Възникна грешка при изтриването на договора.');
        });
    });
    
    // Handle status update button click
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            contractToUpdate = this.dataset.id;
            statusToSet = this.dataset.status;
            statusModal.show();
        });
    });
    
    // Handle status update confirmation
    document.getElementById('confirmStatus').addEventListener('click', function() {
        if (!contractToUpdate || !statusToSet) return;
        
        let formData = new FormData();
        formData.append('status', statusToSet);
        
        fetch(`/admin/contracts/update-status/${contractToUpdate}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            statusModal.hide();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Възникна грешка при обновяване на статуса.');
            }
        })
        .catch(() => {
            statusModal.hide();
            alert('Възникна грешка при обновяване на статуса.');
        });
    });
});
</script>
<?php $this->end() ?> 