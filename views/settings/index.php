<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Настройки на системата</h1>

    <!-- Навигация между секциите -->
    <ul class="nav nav-tabs mt-4" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                <i class="fas fa-cog me-2"></i>Общи настройки
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="email-tab" data-bs-toggle="tab" href="#email" role="tab">
                <i class="fas fa-envelope me-2"></i>Имейл известия
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="signature-tab" data-bs-toggle="tab" href="#signature" role="tab">
                <i class="fas fa-signature me-2"></i>Настройки за подписване
            </a>
        </li>
    </ul>

    <!-- Съдържание на секциите -->
    <div class="tab-content mt-4" id="settingsTabContent">
        <!-- Общи настройки -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <form id="generalSettingsForm" method="POST" action="/settings/save-general">
                        <h5 class="card-title mb-4">Основни настройки</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Име на системата</label>
                                    <input type="text" name="system_name" class="form-control" 
                                           value="<?= htmlspecialchars($settings['system_name'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">URL адрес на системата</label>
                                    <input type="url" name="system_url" class="form-control" 
                                           value="<?= htmlspecialchars($settings['system_url'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Имейл адрес за контакт</label>
                                    <input type="email" name="contact_email" class="form-control" 
                                           value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Часова зона</label>
                                    <select name="timezone" class="form-select">
                                        <?php foreach ($timezones as $tz): ?>
                                            <option value="<?= $tz ?>" 
                                                    <?= ($settings['timezone'] ?? '') === $tz ? 'selected' : '' ?>>
                                                <?= $tz ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Формат на датата</label>
                                    <select name="date_format" class="form-select">
                                        <option value="d.m.Y" <?= ($settings['date_format'] ?? '') === 'd.m.Y' ? 'selected' : '' ?>>31.12.2023</option>
                                        <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>2023-12-31</option>
                                        <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>31/12/2023</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Формат на часа</label>
                                    <select name="time_format" class="form-select">
                                        <option value="H:i" <?= ($settings['time_format'] ?? '') === 'H:i' ? 'selected' : '' ?>>24-часов (15:30)</option>
                                        <option value="h:i A" <?= ($settings['time_format'] ?? '') === 'h:i A' ? 'selected' : '' ?>>12-часов (3:30 PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h5 class="card-title mb-4">Настройки за файлове</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Максимален размер на файл (MB)</label>
                                    <input type="number" name="max_file_size" class="form-control" 
                                           value="<?= htmlspecialchars($settings['max_file_size'] ?? '10') ?>" 
                                           min="1" max="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Позволени типове файлове</label>
                                    <input type="text" name="allowed_file_types" class="form-control" 
                                           value="<?= htmlspecialchars($settings['allowed_file_types'] ?? 'pdf,doc,docx,xls,xlsx') ?>"
                                           placeholder="pdf,doc,docx,xls,xlsx">
                                    <div class="form-text">Въведете разширенията разделени със запетая</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Период на съхранение (дни)</label>
                                    <input type="number" name="storage_period" class="form-control" 
                                           value="<?= htmlspecialchars($settings['storage_period'] ?? '365') ?>" 
                                           min="30" required>
                                    <div class="form-text">0 = безсрочно</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Компресиране на файлове</label>
                                    <select name="file_compression" class="form-select">
                                        <option value="none" <?= ($settings['file_compression'] ?? '') === 'none' ? 'selected' : '' ?>>Без компресия</option>
                                        <option value="low" <?= ($settings['file_compression'] ?? '') === 'low' ? 'selected' : '' ?>>Ниска компресия</option>
                                        <option value="medium" <?= ($settings['file_compression'] ?? '') === 'medium' ? 'selected' : '' ?>>Средна компресия</option>
                                        <option value="high" <?= ($settings['file_compression'] ?? '') === 'high' ? 'selected' : '' ?>>Висока компресия</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Запази промените
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Настройки за имейл известия -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <form id="emailSettingsForm" method="POST" action="/settings/save-email">
                        <h5 class="card-title mb-4">SMTP настройки</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">SMTP сървър</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP порт</label>
                                    <input type="number" name="smtp_port" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Криптиране</label>
                                    <select name="smtp_encryption" class="form-select">
                                        <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Без криптиране</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">SMTP потребител</label>
                                    <input type="text" name="smtp_username" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP парола</label>
                                    <input type="password" name="smtp_password" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Имейл подател</label>
                                    <input type="email" name="smtp_from_email" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <h5 class="card-title mb-4">Настройки за известия</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_document_upload" class="form-check-input" 
                                           <?= ($settings['notify_document_upload'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при качване на документ</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_document_share" class="form-check-input" 
                                           <?= ($settings['notify_document_share'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при споделяне на документ</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_signature_request" class="form-check-input" 
                                           <?= ($settings['notify_signature_request'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при заявка за подпис</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_document_signed" class="form-check-input" 
                                           <?= ($settings['notify_document_signed'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при подписване на документ</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_document_rejected" class="form-check-input" 
                                           <?= ($settings['notify_document_rejected'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при отказ от подписване</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="notify_document_expired" class="form-check-input" 
                                           <?= ($settings['notify_document_expired'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">Известие при изтичане на документ</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-info me-2" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane me-1"></i> Тествай настройките
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Запази промените
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Настройки за подписване -->
        <div class="tab-pane fade" id="signature" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <form id="signatureSettingsForm" method="POST" action="/settings/save-signature">
                        <h5 class="card-title mb-4">Настройки за подписване</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Метод за подписване</label>
                                    <select name="signature_method" class="form-select">
                                        <option value="pin" <?= ($settings['signature_method'] ?? '') === 'pin' ? 'selected' : '' ?>>PIN код</option>
                                        <option value="certificate" <?= ($settings['signature_method'] ?? '') === 'certificate' ? 'selected' : '' ?>>Електронен сертификат</option>
                                        <option value="both" <?= ($settings['signature_method'] ?? '') === 'both' ? 'selected' : '' ?>>PIN код и сертификат</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Дължина на PIN кода</label>
                                    <select name="pin_length" class="form-select">
                                        <option value="4" <?= ($settings['pin_length'] ?? '') === '4' ? 'selected' : '' ?>>4 цифри</option>
                                        <option value="6" <?= ($settings['pin_length'] ?? '') === '6' ? 'selected' : '' ?>>6 цифри</option>
                                        <option value="8" <?= ($settings['pin_length'] ?? '') === '8' ? 'selected' : '' ?>>8 цифри</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Валидност на PIN кода (минути)</label>
                                    <input type="number" name="pin_validity" class="form-control" 
                                           value="<?= htmlspecialchars($settings['pin_validity'] ?? '15') ?>" 
                                           min="5" max="60" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Срок за подписване (дни)</label>
                                    <input type="number" name="signature_deadline" class="form-control" 
                                           value="<?= htmlspecialchars($settings['signature_deadline'] ?? '7') ?>" 
                                           min="1" max="30" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Напомняне преди изтичане (дни)</label>
                                    <input type="number" name="signature_reminder" class="form-control" 
                                           value="<?= htmlspecialchars($settings['signature_reminder'] ?? '2') ?>" 
                                           min="1" max="7" required>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="require_reason" class="form-check-input" 
                                           <?= ($settings['require_reason'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        Изисквай причина при отказ от подписване
                                    </label>
                                </div>
                            </div>
                        </div>

                        <h5 class="card-title mb-4">Визуализация на подписа</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Позиция на подписа</label>
                                    <select name="signature_position" class="form-select">
                                        <option value="bottom-right" <?= ($settings['signature_position'] ?? '') === 'bottom-right' ? 'selected' : '' ?>>Долу вдясно</option>
                                        <option value="bottom-left" <?= ($settings['signature_position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Долу вляво</option>
                                        <option value="top-right" <?= ($settings['signature_position'] ?? '') === 'top-right' ? 'selected' : '' ?>>Горе вдясно</option>
                                        <option value="top-left" <?= ($settings['signature_position'] ?? '') === 'top-left' ? 'selected' : '' ?>>Горе вляво</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Размер на подписа</label>
                                    <select name="signature_size" class="form-select">
                                        <option value="small" <?= ($settings['signature_size'] ?? '') === 'small' ? 'selected' : '' ?>>Малък</option>
                                        <option value="medium" <?= ($settings['signature_size'] ?? '') === 'medium' ? 'selected' : '' ?>>Среден</option>
                                        <option value="large" <?= ($settings['signature_size'] ?? '') === 'large' ? 'selected' : '' ?>>Голям</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Информация в подписа</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="show_name" class="form-check-input" 
                                               <?= ($settings['show_name'] ?? '') ? 'checked' : '' ?>>
                                        <label class="form-check-label">Име на подписващия</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="show_date" class="form-check-input" 
                                               <?= ($settings['show_date'] ?? '') ? 'checked' : '' ?>>
                                        <label class="form-check-label">Дата и час на подписване</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="show_reason" class="form-check-input" 
                                               <?= ($settings['show_reason'] ?? '') ? 'checked' : '' ?>>
                                        <label class="form-check-label">Причина за подписване</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Запази промените
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Тестване на имейл настройките
function testEmailSettings() {
    const form = document.getElementById('emailSettingsForm');
    const formData = new FormData(form);
    
    fetch('/settings/test-email', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Тестовият имейл е изпратен успешно!');
        } else {
            alert('Грешка при изпращане на тестов имейл: ' + result.error);
        }
    });
}

// Запазване на настройките с AJAX
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Настройките са запазени успешно!');
            } else {
                alert('Грешка при запазване на настройките: ' + result.error);
            }
        });
    });
});

// Запомняне на активния таб
document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function(e) {
        localStorage.setItem('activeSettingsTab', e.target.id);
    });
});

// Възстановяване на последния активен таб
const activeTab = localStorage.getItem('activeSettingsTab');
if (activeTab) {
    const tab = new bootstrap.Tab(document.querySelector('#' + activeTab));
    tab.show();
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 