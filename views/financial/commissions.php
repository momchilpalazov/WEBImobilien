<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Отчет комисионни</h1>
    
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
    
    <!-- Обща информация -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($report['totals']['total_commission'], 2) ?> €</h4>
                    <div>Общо комисионни</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= number_format($report['totals']['avg_commission_rate'], 1) ?>%</h4>
                    <div>Средна комисионна</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?= $report['totals']['transactions_count'] ?></h4>
                    <div>Брой транзакции</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Графика -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-line me-1"></i>
            Комисионни по месеци
        </div>
        <div class="card-body">
            <canvas id="commissionsChart" width="100%" height="30"></canvas>
        </div>
    </div>
    
    <!-- Комисионни по агент -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Комисионни по агент
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Агент</th>
                            <th>Брой сделки</th>
                            <th>Общо комисионни</th>
                            <th>Средна комисионна</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['by_agent'] as $agent): ?>
                        <tr>
                            <td><?= htmlspecialchars($agent['agent_name']) ?></td>
                            <td><?= $agent['transactions'] ?></td>
                            <td><?= number_format($agent['commission'], 2) ?> €</td>
                            <td><?= number_format($agent['avg_rate'], 1) ?>%</td>
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
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Комисионни по месеци
const ctx = document.getElementById('commissionsChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($report['by_month'], 'month')) ?>,
        datasets: [{
            label: 'Комисионни',
            data: <?= json_encode(array_column($report['by_month'], 'commission')) ?>,
            borderColor: 'rgba(40, 167, 69, 1)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true
        }, {
            label: 'Средна комисионна (%)',
            data: <?= json_encode(array_column($report['by_month'], 'avg_rate')) ?>,
            borderColor: 'rgba(0, 123, 255, 1)',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            yAxisID: 'percentage'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left'
            },
            percentage: {
                beginAtZero: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

function exportReport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/financial/export';
    
    const reportType = document.createElement('input');
    reportType.type = 'hidden';
    reportType.name = 'report_type';
    reportType.value = 'commissions';
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