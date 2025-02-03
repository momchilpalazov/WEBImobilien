<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Маркетингови кампании</h1>
        <a href="/marketing/campaigns/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Нова кампания
        </a>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                            Чернова
                        </option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                            Активна
                        </option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                            Завършена
                        </option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                            Отказана
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">От дата</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?= $filters['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">До дата</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?= $filters['date_to'] ?? '' ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Приложи
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с кампании -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if (empty($campaigns)): ?>
                <p class="text-muted text-center mb-0">Няма намерени кампании</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Заглавие</th>
                                <th>Период</th>
                                <th>Имоти</th>
                                <th>Канали</th>
                                <th>Бюджет</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td>
                                        <a href="/marketing/campaigns/view/<?= $campaign['id'] ?>">
                                            <?= htmlspecialchars($campaign['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($campaign['start_date'])) ?>
                                        <?php if ($campaign['end_date']): ?>
                                            - <?= date('d.m.Y', strtotime($campaign['end_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $campaign['property_count'] ?> имота
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $campaign['channel_count'] ?> канала
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($campaign['budget']): ?>
                                            <?= number_format($campaign['budget'], 2) ?> лв.
                                        <?php else: ?>
                                            <span class="text-muted">Не е зададен</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'draft' => 'secondary',
                                            'active' => 'success',
                                            'completed' => 'primary',
                                            'cancelled' => 'danger'
                                        ][$campaign['status']] ?? 'secondary';
                                        
                                        $statusText = [
                                            'draft' => 'Чернова',
                                            'active' => 'Активна',
                                            'completed' => 'Завършена',
                                            'cancelled' => 'Отказана'
                                        ][$campaign['status']] ?? 'Неизвестен';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/marketing/campaigns/view/<?= $campaign['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Преглед">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/marketing/campaigns/edit/<?= $campaign['id'] ?>"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Редактиране">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($campaign['status'] === 'draft'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="updateStatus(<?= $campaign['id'] ?>, 'active')"
                                                        title="Стартиране">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($campaign['status'] === 'active'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary"
                                                        onclick="updateStatus(<?= $campaign['id'] ?>, 'completed')"
                                                        title="Приключване">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="updateStatus(<?= $campaign['id'] ?>, 'cancelled')"
                                                        title="Отказване">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($campaign['status'], ['draft', 'cancelled'])): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteCampaign(<?= $campaign['id'] ?>)"
                                                        title="Изтриване">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    if (!confirm('Сигурни ли сте?')) {
        return;
    }

    fetch(`/marketing/campaigns/update-status/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteCampaign(id) {
    if (!confirm('Сигурни ли сте? Това действие е необратимо!')) {
        return;
    }

    fetch(`/marketing/campaigns/delete/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Auto-submit filters
document.querySelector('select[name="status"]').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 