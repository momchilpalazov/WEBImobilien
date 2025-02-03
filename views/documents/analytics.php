<?php
use App\Utils\Format;
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Статистика на документи</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="exportData('pdf')">
                <i class="fas fa-file-pdf"></i> Експорт PDF
            </button>
            <button type="button" class="btn btn-outline-success" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i> Експорт Excel
            </button>
        </div>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Период</label>
                    <select name="period" class="form-select">
                        <option value="7d" <?= ($filters['period'] ?? '') === '7d' ? 'selected' : '' ?>>Последните 7 дни</option>
                        <option value="30d" <?= ($filters['period'] ?? '') === '30d' ? 'selected' : '' ?>>Последните 30 дни</option>
                        <option value="90d" <?= ($filters['period'] ?? '') === '90d' ? 'selected' : '' ?>>Последните 90 дни</option>
                        <option value="1y" <?= ($filters['period'] ?? '') === '1y' ? 'selected' : '' ?>>Последната година</option>
                        <option value="custom" <?= ($filters['period'] ?? '') === 'custom' ? 'selected' : '' ?>>Избран период</option>
                    </select>
                </div>
                <div class="col-md-3 custom-date-range" style="display: none;">
                    <label class="form-label">От дата</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-3 custom-date-range" style="display: none;">
                    <label class="form-label">До дата</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Категория</label>
                    <select name="category" class="form-select">
                        <option value="">Всички</option>
                        <option value="contract" <?= ($filters['category'] ?? '') === 'contract' ? 'selected' : '' ?>>Договори</option>
                        <option value="deed" <?= ($filters['category'] ?? '') === 'deed' ? 'selected' : '' ?>>Нотариални актове</option>
                        <option value="certificate" <?= ($filters['category'] ?? '') === 'certificate' ? 'selected' : '' ?>>Сертификати</option>
                        <option value="permit" <?= ($filters['category'] ?? '') === 'permit' ? 'selected' : '' ?>>Разрешителни</option>
                        <option value="tax" <?= ($filters['category'] ?? '') === 'tax' ? 'selected' : '' ?>>Данъчни документи</option>
                        <option value="insurance" <?= ($filters['category'] ?? '') === 'insurance' ? 'selected' : '' ?>>Застраховки</option>
                        <option value="appraisal" <?= ($filters['category'] ?? '') === 'appraisal' ? 'selected' : '' ?>>Оценки</option>
                        <option value="other" <?= ($filters['category'] ?? '') === 'other' ? 'selected' : '' ?>>Други</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync"></i> Обнови
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Обща статистика -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Общо документи</div>
                            <div class="fs-4"><?= number_format($stats['total_documents']) ?></div>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-25"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <div>Спрямо предходен период</div>
                    <?php if ($stats['documents_change'] > 0): ?>
                        <div class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            <?= number_format($stats['documents_change'], 1) ?>%
                        </div>
                    <?php elseif ($stats['documents_change'] < 0): ?>
                        <div class="text-danger">
                            <i class="fas fa-arrow-down me-1"></i>
                            <?= number_format(abs($stats['documents_change']), 1) ?>%
                        </div>
                    <?php else: ?>
                        <div>0%</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Подписани документи</div>
                            <div class="fs-4"><?= number_format($stats['signed_documents']) ?></div>
                        </div>
                        <i class="fas fa-file-signature fa-2x opacity-25"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <div>Успеваемост на подписване</div>
                    <div><?= number_format($stats['signature_success_rate'], 1) ?>%</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Споделени документи</div>
                            <div class="fs-4"><?= number_format($stats['shared_documents']) ?></div>
                        </div>
                        <i class="fas fa-share-alt fa-2x opacity-25"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <div>Средно споделяния на документ</div>
                    <div><?= number_format($stats['avg_shares_per_document'], 1) ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Общ размер</div>
                            <div class="fs-4"><?= Format::fileSize($stats['total_size']) ?></div>
                        </div>
                        <i class="fas fa-database fa-2x opacity-25"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <div>Среден размер на документ</div>
                    <div><?= Format::fileSize($stats['avg_document_size']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Графика на активността -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Активност
                </div>
                <div class="card-body">
                    <canvas id="activityChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Разпределение по категории -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Разпределение по категории
                </div>
                <div class="card-body">
                    <canvas id="categoriesChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Най-използвани документи -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-star me-1"></i>
                    Най-използвани документи
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Документ</th>
                                    <th>Категория</th>
                                    <th class="text-center">Прегледи</th>
                                    <th class="text-center">Изтегляния</th>
                                    <th class="text-center">Споделяния</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popular_documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <a href="/documents/view/<?= $doc['id'] ?>">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $categories = [
                                                'contract' => 'Договор',
                                                'deed' => 'Нотариален акт',
                                                'certificate' => 'Сертификат',
                                                'permit' => 'Разрешително',
                                                'tax' => 'Данъчен документ',
                                                'insurance' => 'Застраховка',
                                                'appraisal' => 'Оценка',
                                                'other' => 'Друго'
                                            ];
                                            echo $categories[$doc['category']] ?? 'Неизвестно';
                                            ?>
                                        </td>
                                        <td class="text-center"><?= number_format($doc['views']) ?></td>
                                        <td class="text-center"><?= number_format($doc['downloads']) ?></td>
                                        <td class="text-center"><?= number_format($doc['shares']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика на подписите -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-signature me-1"></i>
                    Статистика на подписите
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th class="text-center">Брой</th>
                                    <th class="text-end">Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statusClasses = [
                                    'signed' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'expired' => 'secondary'
                                ];
                                $statusText = [
                                    'signed' => 'Подписани',
                                    'pending' => 'Очакващи',
                                    'rejected' => 'Отказани',
                                    'expired' => 'Изтекли'
                                ];
                                ?>
                                <?php foreach ($signature_stats as $status => $data): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $statusClasses[$status] ?>">
                                                <?= $statusText[$status] ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= number_format($data['count']) ?></td>
                                        <td class="text-end"><?= number_format($data['percentage'], 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-4">
                            <h6 class="mb-3">Средно време за подписване</h6>
                            <div class="progress" style="height: 25px;">
                                <?php
                                $signTimes = [
                                    ['range' => '< 1 час', 'percent' => $signature_time_stats['under_1h']],
                                    ['range' => '1-24 часа', 'percent' => $signature_time_stats['1h_to_24h']],
                                    ['range' => '1-3 дни', 'percent' => $signature_time_stats['1d_to_3d']],
                                    ['range' => '> 3 дни', 'percent' => $signature_time_stats['over_3d']]
                                ];
                                $colors = ['success', 'info', 'warning', 'danger'];
                                ?>
                                <?php foreach ($signTimes as $i => $time): ?>
                                    <div class="progress-bar bg-<?= $colors[$i] ?>" 
                                         role="progressbar" 
                                         style="width: <?= $time['percent'] ?>%"
                                         title="<?= $time['range'] ?>: <?= number_format($time['percent'], 1) ?>%">
                                        <?= $time['percent'] > 10 ? $time['range'] : '' ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small text-muted">
                                <?php foreach ($signTimes as $i => $time): ?>
                                    <div>
                                        <i class="fas fa-square text-<?= $colors[$i] ?>"></i>
                                        <?= $time['range'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Инициализация на графиките
document.addEventListener('DOMContentLoaded', function() {
    // Графика на активността
    const activityCtx = document.getElementById('activityChart');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($activity_data['labels']) ?>,
            datasets: [
                {
                    label: 'Нови документи',
                    data: <?= json_encode($activity_data['new_documents']) ?>,
                    borderColor: 'rgba(13, 110, 253, 1)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Подписвания',
                    data: <?= json_encode($activity_data['signatures']) ?>,
                    borderColor: 'rgba(25, 135, 84, 1)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Споделяния',
                    data: <?= json_encode($activity_data['shares']) ?>,
                    borderColor: 'rgba(13, 202, 240, 1)',
                    backgroundColor: 'rgba(13, 202, 240, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Графика на категориите
    const categoriesCtx = document.getElementById('categoriesChart');
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_values($categories)) ?>,
            datasets: [{
                data: <?= json_encode($category_stats) ?>,
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(111, 66, 193, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});

// Показване/скриване на полетата за избор на период
document.querySelector('select[name="period"]').addEventListener('change', function() {
    const customDateFields = document.querySelectorAll('.custom-date-range');
    customDateFields.forEach(field => {
        field.style.display = this.value === 'custom' ? 'block' : 'none';
    });
});

// Експорт на данните
function exportData(format) {
    const url = new URL(window.location.href);
    url.searchParams.append('export', format);
    window.location.href = url.toString();
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 