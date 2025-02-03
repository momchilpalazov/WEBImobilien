<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Клиенти</h1>
        <a href="/clients/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Нов клиент
        </a>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Търсене по име, email или телефон" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Всички статуси</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
                        <option value="potential" <?= ($filters['status'] ?? '') === 'potential' ? 'selected' : '' ?>>Потенциален</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Търси
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с клиенти -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Име</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Статус</th>
                            <th>Източник</th>
                            <th>Последна активност</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Няма намерени клиенти</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td>
                                        <a href="/clients/details/<?= $client['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($client['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'active' => 'success',
                                            'inactive' => 'danger',
                                            'potential' => 'warning'
                                        ][$client['status']] ?? 'secondary';
                                        $statusText = [
                                            'active' => 'Активен',
                                            'inactive' => 'Неактивен',
                                            'potential' => 'Потенциален'
                                        ][$client['status']] ?? 'Неизвестен';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($client['source'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($client['last_interaction'])): ?>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($client['last_interaction'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/clients/details/<?= $client['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Преглед">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/clients/edit/<?= $client['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Редактиране">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Изтриване"
                                                    onclick="confirmDelete(<?= $client['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Потвърждение за изтриване</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Сигурни ли сте, че искате да изтриете този клиент?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Изтрий</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(clientId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteForm').action = `/clients/delete/${clientId}`;
    modal.show();
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 