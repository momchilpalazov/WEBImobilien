<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Анализ по локация</h1>
    
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
                    <label class="form-label">Тип имот</label>
                    <select name="property_type" class="form-select">
                        <option value="">Всички типове</option>
                        <!-- TODO: Add property types list -->
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
    
    <!-- Графики -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Разпределение на приходите
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Средни цени по локация
                </div>
                <div class="card-body">
                    <canvas id="avgPriceChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Показатели по локация -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Показатели по локация
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Локация</th>
                            <th>Брой сделки</th>
                            <th>Общо приходи</th>
                            <th>Средна цена</th>
                            <th>Комисионни</th>
                            <th>Средна комисионна</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['by_location'] as $location): ?>
                        <tr>
                            <td><?= htmlspecialchars($location['location']) ?></td>
                            <td><?= $location['transactions'] ?></td>
                            <td><?= number_format($location['revenue'], 2) ?> €</td>
                            <td><?= number_format($location['avg_price'], 2) ?> €</td>
                            <td><?= number_format($location['commission'], 2) ?> €</td>
                            <td><?= number_format($location['avg_commission_rate'], 1) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Тенденции -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-line me-1"></i>
            Тенденции по месеци
        </div>
        <div class="card-body">
            <canvas id="trendsChart" width="100%" height="30"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Разпределение на приходите
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($report['by_location'], 'location')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($report['by_location'], 'revenue')) ?>,
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(111, 66, 193, 0.8)',
                'rgba(248, 108, 107, 0.8)',
                'rgba(91, 192, 222, 0.8)'
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(111, 66, 193, 1)',
                'rgba(248, 108, 107, 1)',
                'rgba(91, 192, 222, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

// Средни цени
const avgPriceCtx = document.getElementById('avgPriceChart');
new Chart(avgPriceCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($report['by_location'], 'location')) ?>,
        datasets: [{
            label: 'Средна цена',
            data: <?= json_encode(array_column($report['by_location'], 'avg_price')) ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.5)',
            borderColor: 'rgba(0, 123, 255, 1)',
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

// Тенденции по месеци
const trendsCtx = document.getElementById('trendsChart');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_unique(array_column($report['trends'], 'month'))) ?>,
        datasets: [
            <?php
            $locations = array_unique(array_column($report['trends'], 'location'));
            $colors = [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(111, 66, 193, 1)',
                'rgba(248, 108, 107, 1)',
                'rgba(91, 192, 222, 1)'
            ];
            foreach ($locations as $index => $location):
                $locationData = array_filter($report['trends'], function($item) use ($location) {
                    return $item['location'] === $location;
                });
                $revenue = array_column($locationData, 'revenue');
            ?>,
            {
                label: <?= json_encode($location) ?>,
                data: <?= json_encode(array_values($revenue)) ?>,
                borderColor: '<?= $colors[$index % count($colors)] ?>',
                backgroundColor: '<?= str_replace('1)', '0.1)', $colors[$index % count($colors)]) ?>',
                borderWidth: 2,
                fill: true
            },
            <?php endforeach; ?>
        ]
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

function exportReport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/financial/export';
    
    const reportType = document.createElement('input');
    reportType.type = 'hidden';
    reportType.name = 'report_type';
    reportType.value = 'locations';
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