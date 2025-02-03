<?php
use App\Utils\Format;
require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">
            <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> me-2"></i>
            <?= htmlspecialchars($document['title']) ?>
        </h1>
        <div>
            <a href="/documents/download/<?= $document['id'] ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-download"></i> Изтегли
            </a>
            <?php if ($document['status'] !== 'archived'): ?>
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Действия
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="/documents/update/<?= $document['id'] ?>">
                                <i class="fas fa-edit"></i> Редактирай
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="shareDocument(<?= $document['id'] ?>)">
                                <i class="fas fa-share-alt"></i> Сподели
                            </a>
                        </li>
                        <?php if ($document['status'] === 'active'): ?>
                            <li>
                                <a class="dropdown-item" href="#" onclick="archiveDocument(<?= $document['id'] ?>)">
                                    <i class="fas fa-archive"></i> Архивирай
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteDocument(<?= $document['id'] ?>)">
                                <i class="fas fa-trash"></i> Изтрий
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <a href="/documents" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Основна информация -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Основна информация
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Категория:</strong>
                        </div>
                        <div class="col-md-9">
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
                            echo $categories[$document['category']] ?? 'Неизвестно';
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Описание:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= nl2br(htmlspecialchars($document['description'] ?? '')) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Тип файл:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= $document['file_type'] ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Размер:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= Format::fileSize($document['file_size']) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Статус:</strong>
                        </div>
                        <div class="col-md-9">
                            <?php
                            $statusClass = [
                                'draft' => 'secondary',
                                'active' => 'success',
                                'archived' => 'warning'
                            ][$document['status']] ?? 'secondary';
                            
                            $statusText = [
                                'draft' => 'Чернова',
                                'active' => 'Активен',
                                'archived' => 'Архивиран'
                            ][$document['status']] ?? 'Неизвестно';
                            ?>
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Създаден на:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= Format::date($document['created_at'], 'd.m.Y H:i') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Създаден от:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= htmlspecialchars($document['created_by_name']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Версии -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-history me-1"></i>
                        Версии
                    </div>
                    <?php if ($document['status'] !== 'archived'): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="uploadVersion(<?= $document['id'] ?>)">
                            <i class="fas fa-upload"></i> Качи нова версия
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($versions)): ?>
                        <p class="text-muted mb-0">Няма налични версии</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Версия</th>
                                        <th>Размер</th>
                                        <th>Промени</th>
                                        <th>Създадена от</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($versions as $version): ?>
                                        <tr>
                                            <td><?= $version['version_number'] ?></td>
                                            <td><?= Format::fileSize($version['file_size']) ?></td>
                                            <td><?= nl2br(htmlspecialchars($version['changes_description'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($version['created_by_name']) ?></td>
                                            <td><?= Format::date($version['created_at'], 'd.m.Y H:i') ?></td>
                                            <td>
                                                <a href="/documents/download-version/<?= $version['id'] ?>" 
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

        <div class="col-md-4">
            <!-- Свързани с -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-link me-1"></i>
                    Свързани с
                </div>
                <div class="card-body">
                    <?php if (empty($relations)): ?>
                        <p class="text-muted mb-0">Няма свързани обекти</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($relations as $relation): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="d-block text-muted">
                                            <?= Format::entityType($relation['relation_type']) ?>
                                        </small>
                                        <?= htmlspecialchars($relation['name']) ?>
                                    </div>
                                    <a href="/<?= $relation['relation_type'] ?>s/view/<?= $relation['relation_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Подписи -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-signature me-1"></i>
                    Подписи
                </div>
                <div class="card-body">
                    <?php if (empty($signatures)): ?>
                        <p class="text-muted mb-0">Няма изискани подписи</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($signatures as $signature): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong><?= htmlspecialchars($signature['signer_name']) ?></strong>
                                            <small class="d-block text-muted">
                                                <?= $signature['signer_email'] ?>
                                            </small>
                                        </div>
                                        <?php
                                        $signatureClass = [
                                            'pending' => 'warning',
                                            'signed' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'secondary'
                                        ][$signature['signature_status']] ?? 'secondary';
                                        
                                        $signatureText = [
                                            'pending' => 'Очаква подпис',
                                            'signed' => 'Подписан',
                                            'rejected' => 'Отказан',
                                            'expired' => 'Изтекъл'
                                        ][$signature['signature_status']] ?? 'Неизвестно';
                                        ?>
                                        <span class="badge bg-<?= $signatureClass ?>">
                                            <?= $signatureText ?>
                                        </span>
                                    </div>
                                    <?php if ($signature['signature_status'] === 'signed'): ?>
                                        <small class="d-block text-muted">
                                            Подписан на: <?= Format::date($signature['signature_date'], 'd.m.Y H:i') ?>
                                        </small>
                                    <?php elseif ($signature['signature_status'] === 'pending' && $signature['expiration_date']): ?>
                                        <small class="d-block text-muted">
                                            Валиден до: <?= Format::date($signature['expiration_date'], 'd.m.Y H:i') ?>
                                        </small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Споделяния -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-share-alt me-1"></i>
                    Споделяния
                </div>
                <div class="card-body">
                    <?php if (empty($shares)): ?>
                        <p class="text-muted mb-0">Няма активни споделяния</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($shares as $share): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong><?= htmlspecialchars($share['shared_with_email']) ?></strong>
                                            <small class="d-block text-muted">
                                                Споделено от: <?= htmlspecialchars($share['shared_by_name']) ?>
                                            </small>
                                        </div>
                                        <?php if ($document['status'] !== 'archived'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="revokeShare(<?= $share['id'] ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <small class="d-block text-muted">
                                        Създадено на: <?= Format::date($share['created_at'], 'd.m.Y H:i') ?>
                                    </small>
                                    <?php if ($share['expiration_date']): ?>
                                        <small class="d-block text-muted">
                                            Валидно до: <?= Format::date($share['expiration_date'], 'd.m.Y H:i') ?>
                                        </small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- История на достъпа -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    История на достъпа
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <p class="text-muted mb-0">Няма история на достъпа</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($logs as $log): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                            <small class="d-block text-muted">
                                                <?php
                                                $actionText = [
                                                    'view' => 'Преглед',
                                                    'download' => 'Изтегляне',
                                                    'print' => 'Принтиране',
                                                    'share' => 'Споделяне',
                                                    'edit' => 'Редактиране',
                                                    'delete' => 'Изтриване'
                                                ][$log['action_type']] ?? 'Неизвестно';
                                                echo $actionText;
                                                ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <?= Format::date($log['created_at'], 'd.m.Y H:i') ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Version Modal -->
<div class="modal fade" id="uploadVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Качване на нова версия</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="versionForm" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="version_file" class="form-label">Файл *</label>
                        <input type="file" class="form-control" id="version_file" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label for="changes_description" class="form-label">Описание на промените</label>
                        <textarea class="form-control" id="changes_description" name="changes_description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="submitVersion()">Качи</button>
            </div>
        </div>
    </div>
</div>

<!-- Share Document Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Споделяне на документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shareForm" method="POST">
                    <div class="mb-3">
                        <label for="share_email" class="form-label">Email адрес</label>
                        <input type="email" class="form-control" id="share_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="share_expiration" class="form-label">Валидност на споделянето</label>
                        <select class="form-select" id="share_expiration" name="expiration">
                            <option value="1">1 ден</option>
                            <option value="7">7 дни</option>
                            <option value="30">30 дни</option>
                            <option value="0">Без ограничение</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Разрешения</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm_download" name="permissions[]" value="download" checked>
                            <label class="form-check-label" for="perm_download">Изтегляне</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm_print" name="permissions[]" value="print" checked>
                            <label class="form-check-label" for="perm_print">Принтиране</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="submitShare()">Сподели</button>
            </div>
        </div>
    </div>
</div>

<script>
function uploadVersion(documentId) {
    const modal = new bootstrap.Modal(document.getElementById('uploadVersionModal'));
    modal.show();
}

function submitVersion() {
    const form = document.getElementById('versionForm');
    const formData = new FormData(form);
    
    fetch(`/documents/upload-version/<?= $document['id'] ?>`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при качването');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Възникна грешка при качването');
    });
}

function shareDocument(id) {
    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
    modal.show();
}

function submitShare() {
    const form = document.getElementById('shareForm');
    const formData = new FormData(form);
    
    fetch(`/documents/share/<?= $document['id'] ?>`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при споделянето');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Възникна грешка при споделянето');
    });
}

function revokeShare(shareId) {
    if (!confirm('Сигурни ли сте, че искате да прекратите споделянето?')) {
        return;
    }

    fetch(`/documents/revoke-share/${shareId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при прекратяването');
        }
    })
    .catch(error => console.error('Error:', error));
}

function archiveDocument(id) {
    if (!confirm('Сигурни ли сте, че искате да архивирате този документ?')) {
        return;
    }

    fetch(`/documents/update/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'status=archived'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при архивирането');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteDocument(id) {
    if (!confirm('Сигурни ли сте, че искате да изтриете този документ? Това действие е необратимо!')) {
        return;
    }

    fetch(`/documents/delete/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/documents';
        } else {
            alert(data.error || 'Възникна грешка при изтриването');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 