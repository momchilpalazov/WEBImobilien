<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">
            <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
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
            <span class="badge bg-<?= $statusClass ?> fs-6">
                <?= $statusText ?>
            </span>
        </h1>
        <div>
            <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактиране
            </a>
            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $client['id'] ?>)">
                <i class="fas fa-trash"></i> Изтриване
            </button>
        </div>
    </div>

    <!-- Основна информация -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Основна информация
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">Email:</th>
                            <td><?= htmlspecialchars($client['email'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Телефон:</th>
                            <td><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Източник:</th>
                            <td><?= htmlspecialchars($client['source'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Създаден на:</th>
                            <td><?= date('d.m.Y H:i', strtotime($client['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-heart me-1"></i>
                    Предпочитания
                </div>
                <div class="card-body">
                    <?php if ($preferences): ?>
                        <table class="table">
                            <tr>
                                <th style="width: 30%">Тип имот:</th>
                                <td><?= htmlspecialchars($preferences['property_type'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Цена:</th>
                                <td>
                                    <?php if ($preferences['min_price'] || $preferences['max_price']): ?>
                                        <?= number_format($preferences['min_price'] ?? 0, 2) ?> € - 
                                        <?= number_format($preferences['max_price'] ?? 0, 2) ?> €
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Площ:</th>
                                <td>
                                    <?php if ($preferences['min_area'] || $preferences['max_area']): ?>
                                        <?= $preferences['min_area'] ?? 0 ?> м² - 
                                        <?= $preferences['max_area'] ?? 0 ?> м²
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Локация:</th>
                                <td><?= htmlspecialchars($preferences['location'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Допълнително:</th>
                                <td><?= nl2br(htmlspecialchars($preferences['additional_features'] ?? '-')) ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted mb-0">Няма зададени предпочитания</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Взаимодействия -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-comments me-1"></i>
                Взаимодействия
            </div>
            <a href="/clients/add-interaction/<?= $client['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Ново взаимодействие
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($interactions)): ?>
                <p class="text-muted mb-0">Няма записани взаимодействия</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Описание</th>
                                <th>Имот</th>
                                <th>Статус</th>
                                <th>Агент</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interactions as $interaction): ?>
                                <tr>
                                    <td>
                                        <?= date('d.m.Y H:i', strtotime($interaction['scheduled_at'] ?? $interaction['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $typeText = [
                                            'call' => 'Обаждане',
                                            'email' => 'Имейл',
                                            'meeting' => 'Среща',
                                            'viewing' => 'Оглед',
                                            'offer' => 'Оферта',
                                            'other' => 'Друго'
                                        ][$interaction['interaction_type']] ?? 'Неизвестно';
                                        ?>
                                        <?= $typeText ?>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($interaction['description'])) ?></td>
                                    <td>
                                        <?php if ($interaction['property_title']): ?>
                                            <a href="/properties/view/<?= $interaction['property_id'] ?>">
                                                <?= htmlspecialchars($interaction['property_title']) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'planned' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ][$interaction['status']] ?? 'secondary';
                                        $statusText = [
                                            'planned' => 'Планирано',
                                            'completed' => 'Завършено',
                                            'cancelled' => 'Отказано'
                                        ][$interaction['status']] ?? 'Неизвестно';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($interaction['agent_name'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Съвпадащи имоти -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-home me-1"></i>
                Съвпадащи имоти
            </div>
            <form method="POST" action="/clients/calculate-matches/<?= $client['id'] ?>" style="display: inline;">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-sync"></i> Преизчисли съвпаденията
                </button>
            </form>
        </div>
        <div class="card-body">
            <?php if (empty($matches)): ?>
                <p class="text-muted mb-0">Няма намерени съвпадащи имоти</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Имот</th>
                                <th>Цена</th>
                                <th>Локация</th>
                                <th>Съвпадение</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $match): ?>
                                <tr>
                                    <td>
                                        <a href="/properties/view/<?= $match['property_id'] ?>">
                                            <?= htmlspecialchars($match['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= number_format($match['price'], 2) ?> €</td>
                                    <td><?= htmlspecialchars($match['location']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $match['match_score'] ?>%"
                                                 aria-valuenow="<?= $match['match_score'] ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= number_format($match['match_score'], 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'shown' => 'info',
                                            'interested' => 'success',
                                            'not_interested' => 'danger'
                                        ][$match['status']] ?? 'secondary';
                                        $statusText = [
                                            'pending' => 'Очаква',
                                            'shown' => 'Показан',
                                            'interested' => 'Интересува се',
                                            'not_interested' => 'Не се интересува'
                                        ][$match['status']] ?? 'Неизвестно';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="updateMatchStatus(<?= $match['id'] ?>, 'interested')">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="updateMatchStatus(<?= $match['id'] ?>, 'not_interested')">
                                                <i class="fas fa-thumbs-down"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Документи -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-file-alt me-1"></i>
                Документи
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#linkDocumentModal">
                <i class="fas fa-link"></i> Свържи документ
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <p class="text-muted mb-0">Няма свързани документи</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Име</th>
                                <th>Тип</th>
                                <th>Размер</th>
                                <th>Добавен на</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td><?= htmlspecialchars($document['name']) ?></td>
                                    <td><?= strtoupper($document['type']) ?></td>
                                    <td><?= Format::filesize($document['size']) ?></td>
                                    <td><?= Format::datetime($document['created_at']) ?></td>
                                    <td>
                                        <a href="/documents/download/<?= $document['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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

<!-- Link Document Modal -->
<div class="modal fade" id="linkDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Свързване на документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/clients/link-document/<?= $client['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_id" class="form-label">Изберете документ</label>
                        <select name="document_id" id="document_id" class="form-select" required>
                            <option value="">-- Изберете --</option>
                            <?php foreach ($available_documents as $doc): ?>
                                <option value="<?= $doc['id'] ?>">
                                    <?= htmlspecialchars($doc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                    <button type="submit" class="btn btn-primary">Свържи</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(clientId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteForm').action = `/clients/delete/${clientId}`;
    modal.show();
}

function updateMatchStatus(matchId, status) {
    fetch(`/clients/update-match-status/${matchId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 
