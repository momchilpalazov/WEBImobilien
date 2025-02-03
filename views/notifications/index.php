<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Известия</h1>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="markAllAsRead()">
                <i class="fas fa-check-double me-1"></i> Маркирай всички като прочетени
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="managePreferences()">
                <i class="fas fa-cog me-1"></i> Настройки за известия
            </button>
        </div>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Търсене</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Търси в съдържанието"
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Тип</label>
                    <select name="type" class="form-select">
                        <option value="">Всички</option>
                        <option value="document_upload" <?= ($filters['type'] ?? '') === 'document_upload' ? 'selected' : '' ?>>Качен документ</option>
                        <option value="document_share" <?= ($filters['type'] ?? '') === 'document_share' ? 'selected' : '' ?>>Споделен документ</option>
                        <option value="signature_request" <?= ($filters['type'] ?? '') === 'signature_request' ? 'selected' : '' ?>>Заявка за подпис</option>
                        <option value="document_signed" <?= ($filters['type'] ?? '') === 'document_signed' ? 'selected' : '' ?>>Подписан документ</option>
                        <option value="document_rejected" <?= ($filters['type'] ?? '') === 'document_rejected' ? 'selected' : '' ?>>Отказан подпис</option>
                        <option value="document_expired" <?= ($filters['type'] ?? '') === 'document_expired' ? 'selected' : '' ?>>Изтекъл документ</option>
                        <option value="system" <?= ($filters['type'] ?? '') === 'system' ? 'selected' : '' ?>>Системно</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="unread" <?= ($filters['status'] ?? '') === 'unread' ? 'selected' : '' ?>>Непрочетени</option>
                        <option value="read" <?= ($filters['status'] ?? '') === 'read' ? 'selected' : '' ?>>Прочетени</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Период</label>
                    <select name="period" class="form-select">
                        <option value="all" <?= ($filters['period'] ?? '') === 'all' ? 'selected' : '' ?>>Всички</option>
                        <option value="today" <?= ($filters['period'] ?? '') === 'today' ? 'selected' : '' ?>>Днес</option>
                        <option value="yesterday" <?= ($filters['period'] ?? '') === 'yesterday' ? 'selected' : '' ?>>Вчера</option>
                        <option value="last_week" <?= ($filters['period'] ?? '') === 'last_week' ? 'selected' : '' ?>>Последната седмица</option>
                        <option value="last_month" <?= ($filters['period'] ?? '') === 'last_month' ? 'selected' : '' ?>>Последния месец</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Търси
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с известия -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action py-3 <?= !$notification['is_read'] ? 'active' : '' ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <?php
                                $typeIcons = [
                                    'document_upload' => 'fa-file-upload text-primary',
                                    'document_share' => 'fa-share-alt text-info',
                                    'signature_request' => 'fa-file-signature text-warning',
                                    'document_signed' => 'fa-check-circle text-success',
                                    'document_rejected' => 'fa-times-circle text-danger',
                                    'document_expired' => 'fa-clock text-secondary',
                                    'system' => 'fa-cog text-dark'
                                ];
                                $icon = $typeIcons[$notification['type']] ?? 'fa-bell text-primary';
                                ?>
                                <div class="notification-icon me-3">
                                    <i class="fas <?= $icon ?> fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                    <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?>
                                        <?php if ($notification['related_document_id']): ?>
                                            · <a href="/documents/view/<?= $notification['related_document_id'] ?>">
                                                Виж документа
                                            </a>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if (!$notification['is_read']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="markAsRead(<?= $notification['id'] ?>)"
                                            title="Маркирай като прочетено">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteNotification(<?= $notification['id'] ?>)"
                                        title="Изтрий">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell fa-3x mb-3"></i>
                        <h5>Нямате известия</h5>
                        <p>Всички ваши известия ще се появят тук</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?><?= $query_string ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $query_string ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?><?= $query_string ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модал за настройки на известията -->
<div class="modal fade" id="preferencesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="preferencesForm" method="POST" action="/notifications/preferences">
                <div class="modal-header">
                    <h5 class="modal-title">Настройки за известия</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Получаване на известия за:</h6>
                    
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[document_upload]" class="form-check-input" 
                                   id="pref_document_upload" <?= ($preferences['document_upload'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_document_upload">
                                Качване на нови документи
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[document_share]" class="form-check-input" 
                                   id="pref_document_share" <?= ($preferences['document_share'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_document_share">
                                Споделяне на документи
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[signature_request]" class="form-check-input" 
                                   id="pref_signature_request" <?= ($preferences['signature_request'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_signature_request">
                                Заявки за подпис
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[document_signed]" class="form-check-input" 
                                   id="pref_document_signed" <?= ($preferences['document_signed'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_document_signed">
                                Подписване на документи
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[document_rejected]" class="form-check-input" 
                                   id="pref_document_rejected" <?= ($preferences['document_rejected'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_document_rejected">
                                Отказ от подписване
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[document_expired]" class="form-check-input" 
                                   id="pref_document_expired" <?= ($preferences['document_expired'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_document_expired">
                                Изтичане на документи
                            </label>
                        </div>
                    </div>

                    <h6 class="mb-3">Методи за известяване:</h6>
                    
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[notify_web]" class="form-check-input" 
                                   id="pref_notify_web" <?= ($preferences['notify_web'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_notify_web">
                                В системата
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="preferences[notify_email]" class="form-check-input" 
                                   id="pref_notify_email" <?= ($preferences['notify_email'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_notify_email">
                                По имейл
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Честота на имейл известията</label>
                        <select name="preferences[email_frequency]" class="form-select">
                            <option value="immediately" <?= ($preferences['email_frequency'] ?? '') === 'immediately' ? 'selected' : '' ?>>
                                Веднага
                            </option>
                            <option value="hourly" <?= ($preferences['email_frequency'] ?? '') === 'hourly' ? 'selected' : '' ?>>
                                На всеки час
                            </option>
                            <option value="daily" <?= ($preferences['email_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>
                                Веднъж дневно
                            </option>
                            <option value="weekly" <?= ($preferences['email_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>
                                Веднъж седмично
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                    <button type="submit" class="btn btn-primary">Запази</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Инициализация на модала за настройки
let preferencesModal;
document.addEventListener('DOMContentLoaded', function() {
    preferencesModal = new bootstrap.Modal(document.getElementById('preferencesModal'));
});

// Показване на настройките
function managePreferences() {
    preferencesModal.show();
}

// Маркиране на известие като прочетено
function markAsRead(id) {
    fetch(`/notifications/mark-as-read/${id}`, { method: 'POST' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert('Възникна грешка при маркирането на известието като прочетено.');
            }
        });
}

// Маркиране на всички известия като прочетени
function markAllAsRead() {
    if (confirm('Сигурни ли сте, че искате да маркирате всички известия като прочетени?')) {
        fetch('/notifications/mark-all-as-read', { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при маркирането на известията като прочетени.');
                }
            });
    }
}

// Изтриване на известие
function deleteNotification(id) {
    if (confirm('Сигурни ли сте, че искате да изтриете това известие?')) {
        fetch(`/notifications/delete/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при изтриването на известието.');
                }
            });
    }
}

// Запазване на настройките за известия
document.getElementById('preferencesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            preferencesModal.hide();
            alert('Настройките са запазени успешно!');
        } else {
            alert('Възникна грешка при запазването на настройките.');
        }
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 