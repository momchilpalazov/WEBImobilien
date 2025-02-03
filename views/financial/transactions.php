<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">История на транзакциите</h1>
    
    <!-- Филтри -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Начална дата</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= $filters['start_date'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Крайна дата</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="<?= $filters['end_date'] ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Тип</label>
                    <select name="type" class="form-select">
                        <option value="">Всички</option>
                        <option value="sale" <?= ($filters['type'] ?? '') === 'sale' ? 'selected' : '' ?>>Продажба</option>
                        <option value="rent" <?= ($filters['type'] ?? '') === 'rent' ? 'selected' : '' ?>>Наем</option>
                        <option value="commission" <?= ($filters['type'] ?? '') === 'commission' ? 'selected' : '' ?>>Комисионна</option>
                        <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Разход</option>
                        <option value="other" <?= ($filters['type'] ?? '') === 'other' ? 'selected' : '' ?>>Друго</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Чакащи</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Завършени</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Отказани</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Агент</label>
                    <select name="agent_id" class="form-select">
                        <option value="">Всички агенти</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent->id ?>" <?= ($filters['agent_id'] ?? '') == $agent->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($agent->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Приложи</button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="exportReport()">
                        <i class="fas fa-download"></i> Експорт
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Транзакции -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Списък транзакции
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'transaction_date', 'direction' => $sorting['field'] === 'transaction_date' && $sorting['direction'] === 'asc' ? 'desc' : 'asc'])) ?>" 
                                   class="text-decoration-none text-dark">
                                    Дата
                                    <?php if ($sorting['field'] === 'transaction_date'): ?>
                                        <i class="fas fa-sort-<?= $sorting['direction'] === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Тип</th>
                            <th>Имот</th>
                            <th>Клиент</th>
                            <th>Агент</th>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'amount', 'direction' => $sorting['field'] === 'amount' && $sorting['direction'] === 'asc' ? 'desc' : 'asc'])) ?>" 
                                   class="text-decoration-none text-dark">
                                    Сума
                                    <?php if ($sorting['field'] === 'amount'): ?>
                                        <i class="fas fa-sort-<?= $sorting['direction'] === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Комисионна</th>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'status', 'direction' => $sorting['field'] === 'status' && $sorting['direction'] === 'asc' ? 'desc' : 'asc'])) ?>" 
                                   class="text-decoration-none text-dark">
                                    Статус
                                    <?php if ($sorting['field'] === 'status'): ?>
                                        <i class="fas fa-sort-<?= $sorting['direction'] === 'asc' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history['transactions'] as $transaction): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($transaction['transaction_date'])) ?></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'sale' => '<span class="badge bg-success">Продажба</span>',
                                    'rent' => '<span class="badge bg-info">Наем</span>',
                                    'commission' => '<span class="badge bg-primary">Комисионна</span>',
                                    'expense' => '<span class="badge bg-danger">Разход</span>',
                                    'other' => '<span class="badge bg-secondary">Друго</span>'
                                ];
                                echo $typeLabels[$transaction['type']] ?? $transaction['type'];
                                ?>
                            </td>
                            <td><?= htmlspecialchars($transaction['property_title']) ?></td>
                            <td><?= htmlspecialchars($transaction['client_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['agent_name']) ?></td>
                            <td><?= number_format($transaction['amount'], 2) ?> €</td>
                            <td>
                                <?php if ($transaction['commission_amount']): ?>
                                    <?= number_format($transaction['commission_amount'], 2) ?> €
                                    <small class="text-muted">(<?= number_format($transaction['commission_rate'], 1) ?>%)</small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'pending' => '<span class="badge bg-warning">Чакаща</span>',
                                    'completed' => '<span class="badge bg-success">Завършена</span>',
                                    'cancelled' => '<span class="badge bg-danger">Отказана</span>'
                                ];
                                echo $statusLabels[$transaction['status']] ?? $transaction['status'];
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($history['total'] > $history['per_page']): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Показани <?= ($history['page'] - 1) * $history['per_page'] + 1 ?> - 
                    <?= min($history['page'] * $history['per_page'], $history['total']) ?> 
                    от <?= $history['total'] ?> резултата
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php
                        $totalPages = ceil($history['total'] / $history['per_page']);
                        $range = 2;
                        $start = max(1, $history['page'] - $range);
                        $end = min($totalPages, $history['page'] + $range);
                        
                        // Първа страница
                        if ($history['page'] > 1):
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $history['page'] - 1])) ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $history['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($history['page'] < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $history['page'] + 1])) ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportReport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/financial/export';
    
    const reportType = document.createElement('input');
    reportType.type = 'hidden';
    reportType.name = 'report_type';
    reportType.value = 'transactions';
    form.appendChild(reportType);
    
    const format = document.createElement('input');
    format.type = 'hidden';
    format.name = 'format';
    format.value = 'pdf';
    form.appendChild(format);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 