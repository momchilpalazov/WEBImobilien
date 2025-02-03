<?php
/**
 * @var array $stats
 * @var array $types Property types from Property::TYPES
 * @var array $statuses Property statuses from Property::STATUSES
 */
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Статистика на имотите</h1>
        <a href="/admin/properties/export-statistics<?= !empty($stats['applied_filters']) ? '?' . http_build_query($stats['applied_filters']) : '' ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Експорт в Excel
        </a>
    </div>

    <!-- Филтри -->
    <div class="card mb-4 mt-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Филтри
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <!-- Период -->
                <div class="col-md-3">
                    <label for="date_from" class="form-label">От дата</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= $stats['applied_filters']['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">До дата</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= $stats['applied_filters']['date_to'] ?? '' ?>">
                </div>

                <!-- Тип имот -->
                <div class="col-md-3">
                    <label for="type" class="form-label">Тип имот</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Всички типове</option>
                        <?php foreach ($types as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($stats['applied_filters']['type'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Статус -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Всички статуси</option>
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($stats['applied_filters']['status'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Бутони -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Приложи филтрите
                    </button>
                    <a href="/admin/properties/statistics" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Изчисти филтрите
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Общи показатели -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Общ брой имоти</h5>
                    <h2 class="mb-0"><?= number_format($stats['total']) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Обща стойност</h5>
                    <h2 class="mb-0"><?= number_format($stats['total_value']) ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Средна цена</h5>
                    <h2 class="mb-0"><?= number_format($stats['avg_price']) ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Средна площ</h5>
                    <h2 class="mb-0"><?= number_format($stats['avg_area']) ?> m²</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Статистика по тип имот -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Разпределение по тип имот
                </div>
                <div class="card-body">
                    <canvas id="propertyTypeChart" width="100%" height="50"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Тип</th>
                                    <th>Брой</th>
                                    <th>Обща стойност</th>
                                    <th>Средна цена</th>
                                    <th>Средна площ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_type'] as $type => $data): ?>
                                    <tr>
                                        <td><?= $types[$type] ?></td>
                                        <td><?= number_format($data['count']) ?></td>
                                        <td><?= number_format($data['total_value']) ?> €</td>
                                        <td><?= number_format($data['avg_price']) ?> €</td>
                                        <td><?= number_format($data['avg_area']) ?> m²</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика по статус -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Разпределение по статус
                </div>
                <div class="card-body">
                    <canvas id="propertyStatusChart" width="100%" height="50"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th>Брой</th>
                                    <th>Обща стойност</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_status'] as $status => $data): ?>
                                    <tr>
                                        <td><?= $statuses[$status] ?></td>
                                        <td><?= number_format($data['count']) ?></td>
                                        <td><?= number_format($data['total_value']) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Ценови диапазони -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Ценови диапазони
                </div>
                <div class="card-body">
                    <canvas id="priceRangeChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>

        <!-- Диапазони по площ -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Диапазони по площ
                </div>
                <div class="card-body">
                    <canvas id="areaRangeChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Месечна статистика -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-line me-1"></i>
            Месечна статистика
        </div>
        <div class="card-body">
            <canvas id="monthlyStatsChart" width="100%" height="30"></canvas>
        </div>
    </div>

    <!-- След месечната статистика и преди последните промени -->
    <!-- Прогнози -->
    <div class="row">
        <!-- Прогноза за следващия месец -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Прогноза за следващия месец
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Очаквани нови имоти</h5>
                                    <p class="display-6 mb-0"><?= $stats['predictions']['next_month']['expected_listings'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card <?= $stats['predictions']['next_month']['price_trend'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Ценови тренд</h5>
                                    <p class="display-6 mb-0">
                                        <?= $stats['predictions']['next_month']['price_trend'] ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Най-популярни типове имоти:</h5>
                    <ul class="list-group">
                        <?php foreach ($stats['predictions']['next_month']['popular_types'] as $type): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= $types[$type] ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?= $stats['by_type'][$type]['count'] ?> имота
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Прогноза за следващото тримесечие -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Прогноза за следващото тримесечие
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Пазарен тренд</h5>
                                    <p class="display-6 mb-0">
                                        <?= $stats['predictions']['next_quarter']['market_trend'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card <?= $stats['predictions']['next_quarter']['avg_price_change'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Очаквана промяна в цените</h5>
                                    <p class="display-6 mb-0">
                                        <?= $stats['predictions']['next_quarter']['avg_price_change'] ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5>Прогноза за търсенето по тип имот:</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Тип имот</th>
                                    <th>Очаквано търсене</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['predictions']['next_quarter']['demand_forecast'] as $type => $trend): ?>
                                    <tr>
                                        <td><?= $types[$type] ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match($trend) {
                                                'Висока' => 'bg-success',
                                                'Умерена' => 'bg-info',
                                                'Ниска' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $trend ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последни промени -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Последни промени
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заглавие</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>Цена</th>
                            <th>Последна промяна</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_changes'] as $property): ?>
                            <tr>
                                <td><?= $property->id ?></td>
                                <td><?= htmlspecialchars($property->title_bg) ?></td>
                                <td><?= $types[$property->type] ?></td>
                                <td>
                                    <span class="badge bg-<?= $property->status === 'available' ? 'success' : 'warning' ?>">
                                        <?= $statuses[$property->status] ?>
                                    </span>
                                </td>
                                <td><?= number_format($property->price) ?> €</td>
                                <td><?= date('d.m.Y H:i', strtotime($property->updated_at)) ?></td>
                                <td>
                                    <a href="/admin/properties/<?= $property->id ?>/edit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
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

<!-- Chart.js и допълнителни плъгини -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script>
// Общи опции за форматиране на числа
const numberFormatter = new Intl.NumberFormat('bg-BG');
const euroFormatter = new Intl.NumberFormat('bg-BG', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0
});

// Общи опции за tooltips
const commonTooltipOptions = {
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    titleFont: { weight: 'bold' },
    padding: 12,
    cornerRadius: 6,
    usePointStyle: true
};

// Подготовка на данните за графиките
const typeData = {
    labels: <?= json_encode(array_map(fn($type) => $types[$type], array_keys($stats['by_type']))) ?>,
    datasets: [{
        data: <?= json_encode(array_map(fn($data) => $data['count'], $stats['by_type'])) ?>,
        backgroundColor: [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
        ]
    }]
};

const statusData = {
    labels: <?= json_encode(array_map(fn($status) => $statuses[$status], array_keys($stats['by_status']))) ?>,
    datasets: [{
        data: <?= json_encode(array_map(fn($data) => $data['count'], $stats['by_status'])) ?>,
        backgroundColor: [
            '#1cc88a', '#f6c23e', '#e74a3b', '#858796'
        ]
    }]
};

const priceRangeData = {
    labels: <?= json_encode(array_keys($stats['price_ranges'])) ?>,
    datasets: [{
        label: 'Брой имоти',
        data: <?= json_encode(array_values($stats['price_ranges'])) ?>,
        backgroundColor: '#4e73df'
    }]
};

const areaRangeData = {
    labels: <?= json_encode(array_keys($stats['area_ranges'])) ?>,
    datasets: [{
        label: 'Брой имоти',
        data: <?= json_encode(array_values($stats['area_ranges'])) ?>,
        backgroundColor: '#1cc88a'
    }]
};

const monthlyData = {
    labels: <?= json_encode(array_keys($stats['monthly_stats'])) ?>,
    datasets: [{
        label: 'Брой имоти',
        data: <?= json_encode(array_map(fn($data) => $data['count'], $stats['monthly_stats'])) ?>,
        borderColor: '#4e73df',
        tension: 0.1,
        fill: false
    }, {
        label: 'Обща стойност (€)',
        data: <?= json_encode(array_map(fn($data) => $data['total_value'], $stats['monthly_stats'])) ?>,
        borderColor: '#1cc88a',
        tension: 0.1,
        fill: false,
        yAxisID: 'y1'
    }]
};

// Инициализация на графиките
new Chart(document.getElementById('propertyTypeChart'), {
    type: 'pie',
    data: typeData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                ...commonTooltipOptions,
                callbacks: {
                    label: (context) => {
                        const label = context.label || '';
                        const value = context.raw;
                        const percentage = ((value / context.dataset.data.reduce((a, b) => a + b)) * 100).toFixed(1);
                        return `${label}: ${numberFormatter.format(value)} (${percentage}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold' },
                formatter: (value, ctx) => {
                    const sum = ctx.dataset.data.reduce((a, b) => a + b);
                    const percentage = ((value / sum) * 100).toFixed(1);
                    return percentage + '%';
                }
            }
        }
    }
});

new Chart(document.getElementById('propertyStatusChart'), {
    type: 'pie',
    data: statusData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                ...commonTooltipOptions,
                callbacks: {
                    label: (context) => {
                        const label = context.label || '';
                        const value = context.raw;
                        const percentage = ((value / context.dataset.data.reduce((a, b) => a + b)) * 100).toFixed(1);
                        return `${label}: ${numberFormatter.format(value)} (${percentage}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold' },
                formatter: (value, ctx) => {
                    const sum = ctx.dataset.data.reduce((a, b) => a + b);
                    const percentage = ((value / sum) * 100).toFixed(1);
                    return percentage + '%';
                }
            }
        }
    }
});

new Chart(document.getElementById('priceRangeChart'), {
    type: 'bar',
    data: priceRangeData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                ...commonTooltipOptions,
                callbacks: {
                    label: (context) => {
                        return `Брой имоти: ${numberFormatter.format(context.raw)}`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold' },
                anchor: 'center',
                align: 'center',
                formatter: value => numberFormatter.format(value)
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => numberFormatter.format(value)
                }
            }
        }
    }
});

new Chart(document.getElementById('areaRangeChart'), {
    type: 'bar',
    data: areaRangeData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                ...commonTooltipOptions,
                callbacks: {
                    label: (context) => {
                        return `Брой имоти: ${numberFormatter.format(context.raw)}`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold' },
                anchor: 'center',
                align: 'center',
                formatter: value => numberFormatter.format(value)
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => numberFormatter.format(value)
                }
            }
        }
    }
});

new Chart(document.getElementById('monthlyStatsChart'), {
    type: 'line',
    data: monthlyData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            tooltip: {
                ...commonTooltipOptions,
                callbacks: {
                    label: (context) => {
                        const label = context.dataset.label || '';
                        const value = context.raw;
                        return context.datasetIndex === 0 
                            ? `${label}: ${numberFormatter.format(value)}`
                            : `${label}: ${euroFormatter.format(value)}`;
                    }
                }
            },
            zoom: {
                pan: {
                    enabled: true,
                    mode: 'x'
                },
                zoom: {
                    wheel: {
                        enabled: true
                    },
                    pinch: {
                        enabled: true
                    },
                    mode: 'x'
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Брой имоти'
                },
                ticks: {
                    callback: value => numberFormatter.format(value)
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Обща стойност (€)'
                },
                ticks: {
                    callback: value => euroFormatter.format(value)
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Добавяне на бутони за експорт на графики
document.querySelectorAll('canvas').forEach(canvas => {
    const exportButton = document.createElement('button');
    exportButton.className = 'btn btn-sm btn-outline-secondary mt-2';
    exportButton.innerHTML = '<i class="fas fa-download me-1"></i> Запази като изображение';
    exportButton.onclick = () => {
        const link = document.createElement('a');
        link.download = 'chart.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    };
    canvas.parentNode.appendChild(exportButton);
});
</script> 