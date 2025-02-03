<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Архивиране и възстановяване</h1>
        <div>
            <button type="button" class="btn btn-primary me-2" onclick="createBackup()">
                <i class="fas fa-save me-1"></i> Създай архив
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="configureBackup()">
                <i class="fas fa-cog me-1"></i> Настройки
            </button>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Последен архив</div>
                            <div class="h5 mb-0">
                                <?= $last_backup ? date('d.m.Y H:i', strtotime($last_backup['created_at'])) : 'Няма' ?>
                            </div>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Общ размер на архивите</div>
                            <div class="h5 mb-0"><?= Format::fileSize($total_backup_size) ?></div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Брой архиви</div>
                            <div class="h5 mb-0"><?= number_format($total_backups) ?></div>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-archive fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Следващ планиран архив</div>
                            <div class="h5 mb-0">
                                <?= $next_backup ? date('d.m.Y H:i', strtotime($next_backup)) : 'Не е планиран' ?>
                            </div>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- История на архивите -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">История на архивите</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата и час</th>
                            <th>Тип</th>
                            <th>Размер</th>
                            <th>Създаден от</th>
                            <th>Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <div><?= date('d.m.Y H:i:s', strtotime($backup['created_at'])) ?></div>
                                    <small class="text-muted">ID: <?= htmlspecialchars($backup['id']) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $typeIcons = [
                                        'auto' => 'fa-clock text-info',
                                        'manual' => 'fa-user text-primary',
                                        'pre_update' => 'fa-upload text-warning'
                                    ];
                                    $typeLabels = [
                                        'auto' => 'Автоматичен',
                                        'manual' => 'Ръчен',
                                        'pre_update' => 'Преди обновяване'
                                    ];
                                    ?>
                                    <i class="fas <?= $typeIcons[$backup['type']] ?? 'fa-question text-muted' ?> me-1"></i>
                                    <?= $typeLabels[$backup['type']] ?? 'Неизвестен' ?>
                                </td>
                                <td><?= Format::fileSize($backup['size']) ?></td>
                                <td>
                                    <?php if ($backup['type'] === 'manual'): ?>
                                        <?= htmlspecialchars($backup['created_by_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Система</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'failed' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'completed' => 'Завършен',
                                        'in_progress' => 'В процес',
                                        'failed' => 'Неуспешен'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClasses[$backup['status']] ?? 'secondary' ?>">
                                        <?= $statusLabels[$backup['status']] ?? 'Неизвестен' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if ($backup['status'] === 'completed'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="downloadBackup('<?= $backup['id'] ?>')"
                                                    title="Изтегли">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="restoreBackup('<?= $backup['id'] ?>')"
                                                    title="Възстанови">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="viewBackupDetails('<?= $backup['id'] ?>')"
                                                title="Детайли">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <?php if ($backup['status'] !== 'in_progress'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteBackup('<?= $backup['id'] ?>')"
                                                    title="Изтрий">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-archive fa-3x mb-3"></i>
                                    <h5>Няма налични архиви</h5>
                                    <p>Все още не са създадени архиви на системата</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модал за настройки на архивиране -->
<div class="modal fade" id="backupConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="backupConfigForm" method="POST" action="/backup/configure">
                <div class="modal-header">
                    <h5 class="modal-title">Настройки за архивиране</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Автоматично архивиране</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="auto_backup_enabled" class="form-check-input" 
                                   id="autoBackupEnabled" <?= ($config['auto_backup_enabled'] ?? '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="autoBackupEnabled">
                                Активирай автоматично архивиране
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Честота на архивиране</label>
                        <select name="backup_frequency" class="form-select">
                            <option value="daily" <?= ($config['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>
                                Всеки ден
                            </option>
                            <option value="weekly" <?= ($config['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>
                                Всяка седмица
                            </option>
                            <option value="monthly" <?= ($config['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>
                                Всеки месец
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Час на архивиране</label>
                        <input type="time" name="backup_time" class="form-control" 
                               value="<?= $config['backup_time'] ?? '02:00' ?>">
                        <div class="form-text">
                            Препоръчително е да изберете час с най-малко натоварване на системата
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Брой архиви за съхранение</label>
                        <input type="number" name="backup_retention" class="form-control" 
                               value="<?= $config['backup_retention'] ?? '30' ?>" min="1" max="365">
                        <div class="form-text">
                            По-старите архиви ще бъдат автоматично изтрити
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Локация за съхранение</label>
                        <select name="storage_location" class="form-select">
                            <option value="local" <?= ($config['storage_location'] ?? '') === 'local' ? 'selected' : '' ?>>
                                Локален сървър
                            </option>
                            <option value="ftp" <?= ($config['storage_location'] ?? '') === 'ftp' ? 'selected' : '' ?>>
                                FTP сървър
                            </option>
                            <option value="cloud" <?= ($config['storage_location'] ?? '') === 'cloud' ? 'selected' : '' ?>>
                                Облачно хранилище
                            </option>
                        </select>
                    </div>

                    <div id="ftpSettings" class="storage-settings mb-3" style="display: none;">
                        <h6 class="mb-3">FTP настройки</h6>
                        <div class="mb-2">
                            <label class="form-label">FTP сървър</label>
                            <input type="text" name="ftp_host" class="form-control" 
                                   value="<?= $config['ftp_host'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">FTP потребител</label>
                            <input type="text" name="ftp_user" class="form-control" 
                                   value="<?= $config['ftp_user'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">FTP парола</label>
                            <input type="password" name="ftp_password" class="form-control" 
                                   value="<?= $config['ftp_password'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">FTP директория</label>
                            <input type="text" name="ftp_directory" class="form-control" 
                                   value="<?= $config['ftp_directory'] ?? '/backups' ?>">
                        </div>
                    </div>

                    <div id="cloudSettings" class="storage-settings mb-3" style="display: none;">
                        <h6 class="mb-3">Облачни настройки</h6>
                        <div class="mb-2">
                            <label class="form-label">Доставчик</label>
                            <select name="cloud_provider" class="form-select">
                                <option value="google" <?= ($config['cloud_provider'] ?? '') === 'google' ? 'selected' : '' ?>>
                                    Google Drive
                                </option>
                                <option value="dropbox" <?= ($config['cloud_provider'] ?? '') === 'dropbox' ? 'selected' : '' ?>>
                                    Dropbox
                                </option>
                                <option value="onedrive" <?= ($config['cloud_provider'] ?? '') === 'onedrive' ? 'selected' : '' ?>>
                                    OneDrive
                                </option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">API ключ</label>
                            <input type="password" name="cloud_api_key" class="form-control" 
                                   value="<?= $config['cloud_api_key'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Директория</label>
                            <input type="text" name="cloud_directory" class="form-control" 
                                   value="<?= $config['cloud_directory'] ?? '/system-backups' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Компресиране на архивите</label>
                        <select name="compression_level" class="form-select">
                            <option value="none" <?= ($config['compression_level'] ?? '') === 'none' ? 'selected' : '' ?>>
                                Без компресия
                            </option>
                            <option value="low" <?= ($config['compression_level'] ?? '') === 'low' ? 'selected' : '' ?>>
                                Ниска компресия
                            </option>
                            <option value="medium" <?= ($config['compression_level'] ?? '') === 'medium' ? 'selected' : '' ?>>
                                Средна компресия
                            </option>
                            <option value="high" <?= ($config['compression_level'] ?? '') === 'high' ? 'selected' : '' ?>>
                                Висока компресия
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Известяване при грешки</label>
                        <input type="email" name="notification_email" class="form-control" 
                               value="<?= $config['notification_email'] ?? '' ?>"
                               placeholder="admin@example.com">
                        <div class="form-text">
                            Имейл адрес за получаване на известия при проблеми с архивирането
                        </div>
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

<!-- Модал за детайли на архив -->
<div class="modal fade" id="backupDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детайли за архива</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="backupDetails">
                    <!-- Тук ще се зареждат детайлите динамично -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Инициализация на модалите
let backupConfigModal, backupDetailsModal;
document.addEventListener('DOMContentLoaded', function() {
    backupConfigModal = new bootstrap.Modal(document.getElementById('backupConfigModal'));
    backupDetailsModal = new bootstrap.Modal(document.getElementById('backupDetailsModal'));

    // Показване/скриване на настройки според избраната локация
    document.querySelector('select[name="storage_location"]').addEventListener('change', function() {
        document.querySelectorAll('.storage-settings').forEach(el => el.style.display = 'none');
        if (this.value === 'ftp') {
            document.getElementById('ftpSettings').style.display = 'block';
        } else if (this.value === 'cloud') {
            document.getElementById('cloudSettings').style.display = 'block';
        }
    });
});

// Показване на настройките
function configureBackup() {
    backupConfigModal.show();
}

// Запазване на настройките
document.getElementById('backupConfigForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            backupConfigModal.hide();
            alert('Настройките са запазени успешно!');
            location.reload();
        } else {
            alert('Възникна грешка при запазването на настройките.');
        }
    });
});

// Създаване на нов архив
function createBackup() {
    if (confirm('Сигурни ли сте, че искате да създадете нов архив?')) {
        fetch('/backup/create', { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при създаването на архива.');
                }
            });
    }
}

// Изтегляне на архив
function downloadBackup(id) {
    window.location.href = `/backup/download/${id}`;
}

// Възстановяване от архив
function restoreBackup(id) {
    if (confirm('ВНИМАНИЕ! Това действие ще възстанови системата към състоянието от избрания архив. Всички промени след ' +
                'създаването на архива ще бъдат загубени. Сигурни ли сте, че искате да продължите?')) {
        fetch(`/backup/restore/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Възстановяването е започнато успешно. Системата ще бъде временно недостъпна.');
                    location.reload();
                } else {
                    alert('Възникна грешка при възстановяването от архива.');
                }
            });
    }
}

// Преглед на детайли за архив
function viewBackupDetails(id) {
    fetch(`/backup/details/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('backupDetails').innerHTML = `
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID на архива</dt>
                        <dd class="col-sm-8">${result.data.id}</dd>
                        
                        <dt class="col-sm-4">Създаден на</dt>
                        <dd class="col-sm-8">${new Date(result.data.created_at).toLocaleString()}</dd>
                        
                        <dt class="col-sm-4">Тип</dt>
                        <dd class="col-sm-8">${result.data.type}</dd>
                        
                        <dt class="col-sm-4">Размер</dt>
                        <dd class="col-sm-8">${result.data.size_formatted}</dd>
                        
                        <dt class="col-sm-4">Статус</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-${result.data.status_class}">${result.data.status_label}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Създаден от</dt>
                        <dd class="col-sm-8">${result.data.created_by_name || 'Система'}</dd>
                        
                        <dt class="col-sm-4">Времетраене</dt>
                        <dd class="col-sm-8">${result.data.duration || 'N/A'}</dd>
                        
                        <dt class="col-sm-4">Компресия</dt>
                        <dd class="col-sm-8">${result.data.compression_level || 'Няма'}</dd>
                        
                        <dt class="col-sm-4">Локация</dt>
                        <dd class="col-sm-8">${result.data.storage_location}</dd>
                        
                        ${result.data.error ? `
                            <dt class="col-sm-4">Грешка</dt>
                            <dd class="col-sm-8 text-danger">${result.data.error}</dd>
                        ` : ''}
                    </dl>
                `;
                backupDetailsModal.show();
            } else {
                alert('Възникна грешка при зареждането на детайлите.');
            }
        });
}

// Изтриване на архив
function deleteBackup(id) {
    if (confirm('Сигурни ли сте, че искате да изтриете този архив? Това действие е необратимо!')) {
        fetch(`/backup/delete/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при изтриването на архива.');
                }
            });
    }
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 