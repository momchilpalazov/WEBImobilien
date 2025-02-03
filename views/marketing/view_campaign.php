<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4"><?= htmlspecialchars($campaign['title']) ?></h1>
        <div>
            <a href="/marketing/campaigns/edit/<?= $campaign['id'] ?>" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Редактиране
            </a>
            <a href="/marketing/campaigns" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Основна информация -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Информация за кампанията</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Статус:</strong>
                                <?php
                                $statusClass = [
                                    'draft' => 'secondary',
                                    'active' => 'success',
                                    'completed' => 'primary',
                                    'cancelled' => 'danger'
                                ][$campaign['status']] ?? 'secondary';
                                
                                $statusText = [
                                    'draft' => 'Чернова',
                                    'active' => 'Активна',
                                    'completed' => 'Завършена',
                                    'cancelled' => 'Отказана'
                                ][$campaign['status']] ?? 'Неизвестен';
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </p>
                            <p><strong>Период:</strong>
                                <?= date('d.m.Y', strtotime($campaign['start_date'])) ?>
                                <?php if ($campaign['end_date']): ?>
                                    - <?= date('d.m.Y', strtotime($campaign['end_date'])) ?>
                                <?php endif; ?>
                            </p>
                            <p><strong>Бюджет:</strong>
                                <?php if ($campaign['budget']): ?>
                                    <?= number_format($campaign['budget'], 2) ?> лв.
                                <?php else: ?>
                                    <span class="text-muted">Не е зададен</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($campaign['description'])): ?>
                                <p><strong>Описание:</strong></p>
                                <p><?= nl2br(htmlspecialchars($campaign['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Имоти -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Имоти в кампанията</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($campaign['properties'])): ?>
                        <p class="text-muted mb-0">Няма добавени имоти</p>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($campaign['properties'] as $property): ?>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="/properties/view/<?= $property['id'] ?>">
                                                    <?= htmlspecialchars($property['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?= htmlspecialchars($property['address']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Канали -->
            <div class="card mb-4" id="channels">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Маркетингови канали</h5>
                    <a href="/marketing/campaigns/<?= $campaign['id'] ?>/add-channel" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Добави канал
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($campaign['channels'])): ?>
                        <p class="text-muted mb-0">Няма добавени канали</p>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($campaign['channels'] as $channel): ?>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <?= htmlspecialchars($channel['channel_name']) ?>
                                                </h6>
                                                <span class="badge bg-info">
                                                    <?php
                                                    $channelTypes = [
                                                        'social_media' => 'Социални медии',
                                                        'email' => 'Имейл маркетинг',
                                                        'website' => 'Уебсайт',
                                                        'print' => 'Печатни материали',
                                                        'portal' => 'Имотен портал',
                                                        'other' => 'Друго'
                                                    ];
                                                    echo $channelTypes[$channel['channel_type']] ?? 'Неизвестен';
                                                    ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($channel['target_audience'])): ?>
                                                <p class="card-text small">
                                                    <strong>Целева аудитория:</strong><br>
                                                    <?= nl2br(htmlspecialchars($channel['target_audience'])) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="row g-2 mb-2">
                                                <?php if ($channel['budget_allocation']): ?>
                                                    <div class="col-auto">
                                                        <span class="badge bg-secondary">
                                                            Бюджет: <?= number_format($channel['budget_allocation'], 2) ?> лв.
                                                        </span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($channel['start_date']): ?>
                                                    <div class="col-auto">
                                                        <span class="badge bg-secondary">
                                                            От: <?= date('d.m.Y', strtotime($channel['start_date'])) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($channel['end_date']): ?>
                                                    <div class="col-auto">
                                                        <span class="badge bg-secondary">
                                                            До: <?= date('d.m.Y', strtotime($channel['end_date'])) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="btn-group">
                                                <a href="/marketing/campaigns/channels/edit/<?= $channel['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteChannel(<?= $channel['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Статистика</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="date" name="date_from" class="form-control" 
                                       value="<?= $report['period']['from'] ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="date" name="date_to" class="form-control" 
                                       value="<?= $report['period']['to'] ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-sync"></i> Обнови
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4">
                        <h6>Общи показатели</h6>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Прегледи
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['views']) ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Кликове
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['clicks']) ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Запитвания
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['inquiries']) ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Споделяния
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['shares']) ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Лийдове
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['leads']) ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Конверсии
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($report['totals']['conversions']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6>Конверсионни проценти</h6>
                        <div class="mb-3">
                            <label class="form-label small">CTR (Click-through rate)</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?= min($report['conversion_rates']['click_through_rate'], 100) ?>%"
                                     aria-valuenow="<?= $report['conversion_rates']['click_through_rate'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= number_format($report['conversion_rates']['click_through_rate'], 1) ?>%
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Процент запитвания</label>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= min($report['conversion_rates']['inquiry_rate'], 100) ?>%"
                                     aria-valuenow="<?= $report['conversion_rates']['inquiry_rate'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= number_format($report['conversion_rates']['inquiry_rate'], 1) ?>%
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Конверсия на лийдове</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= min($report['conversion_rates']['lead_conversion_rate'], 100) ?>%"
                                     aria-valuenow="<?= $report['conversion_rates']['lead_conversion_rate'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= number_format($report['conversion_rates']['lead_conversion_rate'], 1) ?>%
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Финална конверсия</label>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?= min($report['conversion_rates']['final_conversion_rate'], 100) ?>%"
                                     aria-valuenow="<?= $report['conversion_rates']['final_conversion_rate'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= number_format($report['conversion_rates']['final_conversion_rate'], 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteChannel(id) {
    if (!confirm('Сигурни ли сте?')) {
        return;
    }

    fetch(`/marketing/campaigns/channels/delete/${id}`, {
        method: 'POST'
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