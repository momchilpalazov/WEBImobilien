<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Представяне на агент</h1>
    
    <!-- Филтри -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Начална дата</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= $filters['start_date'] ?? date('Y-m-01') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Крайна дата</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="<?= $filters['end_date'] ?? date('Y-m-t') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Приложи</button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="exportReport()">
                        <i class="fas fa-download"></i> Експорт
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Основни показатели -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($report['performance']['total_revenue'], 2) ?> €</h4>
                    <div>Общи приходи</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($report['performance']['total_commission'], 2) ?> €</h4>
                    <div>Общо комисионни</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= $report['performance']['sales_count'] ?></h4>
                    <div>Брой продажби</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= $report['performance']['rentals_count'] ?></h4>
                    <div>Брой наеми</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Цели -->
    <div class="row">
        <?php foreach ($report['goals'] as $goal): ?>
        <div class="col-xl-4 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bullseye me-1"></i>
                    <?= htmlspecialchars($goal['type']) ?>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div>Цел:</div>
                        <div><strong><?= number_format($goal['target_amount'], 2) ?> €</strong></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div>Постигнато:</div>
                        <div><strong><?= number_format($goal['achieved_amount'], 2) ?> €</strong></div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?= $goal['achievement_rate'] >= 100 ? 'bg-success' : 'bg-primary' ?>" 
                             role="progressbar" 
                             style="width: <?= min($goal['achievement_rate'], 100) ?>%">
                            <?= number_format($goal['achievement_rate'], 1) ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- История -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    История на приходите
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Разпределение на сделките
                </div>
                <div class="card-body">
                    <canvas id="transactionsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- История по месеци -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            История по месеци
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Месец</th>
                            <th>Брой сделки</th>
                            <th>Приходи</th>
                            <th>Комисионни</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['history'] as $month): ?>
                        <tr>
                            <td><?= $month['month'] ?></td>
                            <td><?= $month['transactions'] ?></td>
                            <td><?= number_format($month['revenue'], 2) ?> €</td>
                            <td><?= number_format($month['commission'], 2) ?> €</td>
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
// История на приходите
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($report['history'], 'month')) ?>,
        datasets: [{
            label: 'Приходи',
            data: <?= json_encode(array_column($report['history'], 'revenue')) ?>,
            borderColor: 'rgba(0, 123, 255, 1)',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true
        }, {
            label: 'Комисионни',
            data: <?= json_encode(array_column($report['history'], 'commission')) ?>,
            borderColor: 'rgba(40, 167, 69, 1)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true
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

// Разпределение на сделките
const transactionsCtx = document.getElementById('transactionsChart');
new Chart(transactionsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Продажби', 'Наеми'],
        datasets: [{
            data: [
                <?= $report['performance']['sales_count'] ?>,
                <?= $report['performance']['rentals_count'] ?>
            ],
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(23, 162, 184, 0.8)'
            ],
            borderColor: [
                'rgba(255, 193, 7, 1)',
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
    reportType.value = 'agent_performance';
    form.appendChild(reportType);
    
    const agentId = document.createElement('input');
    agentId.type = 'hidden';
    agentId.name = 'agent_id';
    agentId.value = '<?= $agent_id ?>';
    form.appendChild(agentId);
    
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