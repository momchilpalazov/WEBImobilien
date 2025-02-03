<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Анализ по тип имот</h1>
    
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
                    <label class="form-label">Локация</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?= $filters['location'] ?? '' ?>" 
                           placeholder="Въведете локация">
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
                    Средни цени по тип
                </div>
                <div class="card-body">
                    <canvas id="avgPriceChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Показатели по тип -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Показатели по тип имот
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Тип имот</th>
                            <th>Брой сделки</th>
                            <th>Общо приходи</th>
                            <th>Средна цена</th>
                            <th>Комисионни</th>
                            <th>Средна комисионна</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['by_type'] as $type): ?>
                        <tr>
                            <td><?= htmlspecialchars($type['property_type']) ?></td>
                            <td><?= $type['transactions'] ?></td>
                            <td><?= number_format($type['revenue'], 2) ?> €</td>
                            <td><?= number_format($type['avg_price'], 2) ?> €</td>
                            <td><?= number_format($type['commission'], 2) ?> €</td>
                            <td><?= number_format($type['avg_commission_rate'], 1) ?>%</td>
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
        labels: <?= json_encode(array_column($report['by_type'], 'property_type')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($report['by_type'], 'revenue')) ?>,
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)'
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

// Средни цени
const avgPriceCtx = document.getElementById('avgPriceChart');
new Chart(avgPriceCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($report['by_type'], 'property_type')) ?>,
        datasets: [{
            label: 'Средна цена',
            data: <?= json_encode(array_column($report['by_type'], 'avg_price')) ?>,
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
            $propertyTypes = array_unique(array_column($report['trends'], 'property_type'));
            $colors = [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(23, 162, 184, 1)'
            ];
            foreach ($propertyTypes as $index => $type):
                $typeData = array_filter($report['trends'], function($item) use ($type) {
                    return $item['property_type'] === $type;
                });
                $revenue = array_column($typeData, 'revenue');
            ?>,
            {
                label: <?= json_encode($type) ?>,
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
    reportType.value = 'property_types';
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
