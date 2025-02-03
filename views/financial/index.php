<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Финансово табло</h1>
    
    <!-- Филтри -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Начална дата</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= $filters['start_date'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Крайна дата</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="<?= $filters['end_date'] ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Агент</label>
                    <select name="agent_id" class="form-select">
                        <option value="">Всички агенти</option>
                        <!-- TODO: Add agents list -->
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Приложи</button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="exportReport()">
                        <i class="fas fa-download"></i> Експорт
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Основни метрики -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($metrics['total_revenue'], 2) ?> €</h4>
                    <div>Общи приходи</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small">
                        <?php $revenueChange = $comparison['revenue']['percent_change']; ?>
                        <?php if ($revenueChange > 0): ?>
                            <i class="fas fa-arrow-up"></i>
                            <span class="text-white">+<?= number_format($revenueChange, 1) ?>%</span>
                        <?php else: ?>
                            <i class="fas fa-arrow-down"></i>
                            <span class="text-white"><?= number_format($revenueChange, 1) ?>%</span>
                        <?php endif; ?>
                        спрямо предходен период
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($metrics['total_commission'], 2) ?> €</h4>
                    <div>Общо комисионни</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small">
                        <?php $commissionChange = $comparison['commission']['percent_change']; ?>
                        <?php if ($commissionChange > 0): ?>
                            <i class="fas fa-arrow-up"></i>
                            <span class="text-white">+<?= number_format($commissionChange, 1) ?>%</span>
                        <?php else: ?>
                            <i class="fas fa-arrow-down"></i>
                            <span class="text-white"><?= number_format($commissionChange, 1) ?>%</span>
                        <?php endif; ?>
                        спрямо предходен период
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= $metrics['sales_count'] ?></h4>
                    <div>Брой продажби</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small">
                        <?php $salesChange = $comparison['sales_count']['percent_change']; ?>
                        <?php if ($salesChange > 0): ?>
                            <i class="fas fa-arrow-up"></i>
                            <span class="text-white">+<?= number_format($salesChange, 1) ?>%</span>
                        <?php else: ?>
                            <i class="fas fa-arrow-down"></i>
                            <span class="text-white"><?= number_format($salesChange, 1) ?>%</span>
                        <?php endif; ?>
                        спрямо предходен период
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= $metrics['rentals_count'] ?></h4>
                    <div>Брой наеми</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small">
                        <?php $rentalsChange = $comparison['rentals_count']['percent_change']; ?>
                        <?php if ($rentalsChange > 0): ?>
                            <i class="fas fa-arrow-up"></i>
                            <span class="text-white">+<?= number_format($rentalsChange, 1) ?>%</span>
                        <?php else: ?>
                            <i class="fas fa-arrow-down"></i>
                            <span class="text-white"><?= number_format($rentalsChange, 1) ?>%</span>
                        <?php endif; ?>
                        спрямо предходен период
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Графики -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Приходи по месеци
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Разпределение по тип имот
                </div>
                <div class="card-body">
                    <canvas id="propertyTypeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Топ агенти -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Топ агенти за периода
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Агент</th>
                            <th>Брой сделки</th>
                            <th>Приходи</th>
                            <th>Комисионни</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenue['by_agent'] as $agent): ?>
                        <tr>
                            <td><?= htmlspecialchars($agent['agent_name']) ?></td>
                            <td><?= $agent['transactions'] ?></td>
                            <td><?= number_format($agent['revenue'], 2) ?> €</td>
                            <td><?= number_format($agent['commission'], 2) ?> €</td>
                            <td>
                                <a href="/financial/agent-performance/<?= $agent['agent_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    Детайли
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Прогнози -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-line me-1"></i>
            Финансови прогнози
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Период</th>
                            <th>Тип</th>
                            <th>Прогноза</th>
                            <th>Реален резултат</th>
                            <th>Точност</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forecasts as $forecast): ?>
                        <tr>
                            <td><?= date('m/Y', strtotime($forecast['period_start'])) ?></td>
                            <td><?= htmlspecialchars($forecast['type']) ?></td>
                            <td><?= number_format($forecast['forecast_amount'], 2) ?> €</td>
                            <td>
                                <?= $forecast['actual_amount'] ? number_format($forecast['actual_amount'], 2) . ' €' : '-' ?>
                            </td>
                            <td>
                                <?php if (isset($forecast['accuracy'])): ?>
                                    <?= number_format($forecast['accuracy'], 1) ?>%
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Приходи по месеци
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($revenue['by_month'], 'month')) ?>,
        datasets: [{
            label: 'Приходи',
            data: <?= json_encode(array_column($revenue['by_month'], 'revenue')) ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.5)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 1
        }, {
            label: 'Комисионни',
            data: <?= json_encode(array_column($revenue['by_month'], 'commission')) ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.5)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Разпределение по тип имот
const propertyTypeCtx = document.getElementById('propertyTypeChart');
new Chart(propertyTypeCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($revenue['by_property_type'], 'property_type')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($revenue['by_property_type'], 'revenue')) ?>,
            backgroundColor: [
                'rgba(0, 123, 255, 0.5)',
                'rgba(40, 167, 69, 0.5)',
                'rgba(255, 193, 7, 0.5)',
                'rgba(220, 53, 69, 0.5)',
                'rgba(23, 162, 184, 0.5)'
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(23, 162, 184, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

function exportReport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/financial/export';
    
    const reportType = document.createElement('input');
    reportType.type = 'hidden';
    reportType.name = 'report_type';
    reportType.value = 'dashboard';
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