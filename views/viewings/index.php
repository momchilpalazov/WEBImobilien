<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Огледи</h1>
        <div>
            <a href="/viewings/calendar" class="btn btn-outline-primary me-2">
                <i class="fas fa-calendar"></i> Календар
            </a>
            <a href="/viewings/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Нов оглед
            </a>
        </div>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>
                            Планиран
                        </option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                            Завършен
                        </option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                            Отказан
                        </option>
                        <option value="rescheduled" <?= ($filters['status'] ?? '') === 'rescheduled' ? 'selected' : '' ?>>
                            Пренасрочен
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">От дата</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?= $filters['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">До дата</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?= $filters['date_to'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Търсене</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Имот, клиент..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с огледи -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if (empty($viewings)): ?>
                <p class="text-muted text-center mb-0">Няма намерени огледи</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Дата и час</th>
                                <th>Имот</th>
                                <th>Клиент</th>
                                <th>Агент</th>
                                <th>Статус</th>
                                <th>Обратна връзка</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($viewings as $viewing): ?>
                                <tr>
                                    <td>
                                        <?= date('d.m.Y H:i', strtotime($viewing['scheduled_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="/properties/view/<?= $viewing['property_id'] ?>">
                                            <?= htmlspecialchars($viewing['property_title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/clients/view/<?= $viewing['client_id'] ?>">
                                            <?= htmlspecialchars($viewing['client_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($viewing['agent_name']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'scheduled' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'rescheduled' => 'warning'
                                        ][$viewing['status']] ?? 'secondary';
                                        
                                        $statusText = [
                                            'scheduled' => 'Планиран',
                                            'completed' => 'Завършен',
                                            'cancelled' => 'Отказан',
                                            'rescheduled' => 'Пренасрочен'
                                        ][$viewing['status']] ?? 'Неизвестен';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($viewing['feedback_rating']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?= number_format($viewing['feedback_rating'], 1) ?>
                                                </div>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?= $viewing['feedback_rating'] * 20 ?>%"
                                                         aria-valuenow="<?= $viewing['feedback_rating'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="5">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Няма</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/viewings/view/<?= $viewing['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Преглед">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($viewing['status'] === 'scheduled'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="updateStatus(<?= $viewing['id'] ?>, 'completed')"
                                                        title="Маркирай като завършен">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <a href="/viewings/reschedule/<?= $viewing['id'] ?>"
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Пренасрочи">
                                                    <i class="fas fa-clock"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="updateStatus(<?= $viewing['id'] ?>, 'cancelled')"
                                                        title="Отказване">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($viewing['status'] === 'completed' && !$viewing['feedback_rating']): ?>
                                                <a href="/viewings/add-feedback/<?= $viewing['id'] ?>"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Добави обратна връзка">
                                                    <i class="fas fa-comment"></i>
                                                </a>
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
function updateStatus(viewingId, status) {
    if (!confirm('Сигурни ли сте?')) {
        return;
    }

    fetch(`/viewings/update-status/${viewingId}`, {
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
</script>

<?php require_once 'views/layout/footer.php'; ?> 