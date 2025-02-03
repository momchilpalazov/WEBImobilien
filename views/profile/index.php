<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Профил и настройки</h1>

    <div class="row">
        <!-- Навигация -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-placeholder me-3 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                             style="width: 64px; height: 64px; font-size: 24px;">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                            <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>

                    <div class="nav flex-column nav-pills">
                        <button class="nav-link active mb-2" data-bs-toggle="pill" data-bs-target="#personal">
                            <i class="fas fa-user-circle me-2"></i> Лични данни
                        </button>
                        <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#password">
                            <i class="fas fa-key me-2"></i> Смяна на парола
                        </button>
                        <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#security">
                            <i class="fas fa-shield-alt me-2"></i> Сигурност
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#history">
                            <i class="fas fa-history me-2"></i> История на влизанията
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Съдържание -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Лични данни -->
                <div class="tab-pane fade show active" id="personal">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Лични данни</h5>
                        </div>
                        <div class="card-body">
                            <form id="personalForm" method="POST" action="/profile/update">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Име</label>
                                        <input type="text" name="first_name" class="form-control" 
                                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Фамилия</label>
                                        <input type="text" name="last_name" class="form-control" 
                                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Имейл адрес</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Телефон</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Език на интерфейса</label>
                                    <select name="language" class="form-select">
                                        <option value="bg" <?= ($user['language'] ?? 'bg') === 'bg' ? 'selected' : '' ?>>Български</option>
                                        <option value="en" <?= ($user['language'] ?? 'bg') === 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Часова зона</label>
                                    <select name="timezone" class="form-select">
                                        <option value="Europe/Sofia" <?= ($user['timezone'] ?? 'Europe/Sofia') === 'Europe/Sofia' ? 'selected' : '' ?>>
                                            София (UTC+2/UTC+3)
                                        </option>
                                        <option value="UTC" <?= ($user['timezone'] ?? 'Europe/Sofia') === 'UTC' ? 'selected' : '' ?>>
                                            UTC
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Запази промените
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Смяна на парола -->
                <div class="tab-pane fade" id="password">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Смяна на парола</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm" method="POST" action="/profile/change-password">
                                <div class="mb-3">
                                    <label class="form-label">Текуща парола</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Нова парола</label>
                                    <input type="password" name="new_password" class="form-control" 
                                           minlength="8" required>
                                    <div class="form-text">
                                        Паролата трябва да съдържа поне 8 символа, включително главни и малки букви, 
                                        цифри и специални символи.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Повтори новата парола</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-1"></i> Смени паролата
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Сигурност -->
                <div class="tab-pane fade" id="security">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Двуфакторна автентикация</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!$user['2fa_enabled']): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                                    <h5>Двуфакторната автентикация не е активирана</h5>
                                    <p class="text-muted mb-4">
                                        Активирайте двуфакторната автентикация за допълнителна сигурност на вашия акаунт.
                                    </p>
                                    <button type="button" class="btn btn-primary" onclick="setup2FA()">
                                        <i class="fas fa-plus me-1"></i> Активирай
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Двуфакторната автентикация е активирана
                                    </div>
                                    <p>
                                        Използвайте приложението Google Authenticator или подобно за генериране на кодове.
                                    </p>
                                </div>
                                <button type="button" class="btn btn-danger" onclick="disable2FA()">
                                    <i class="fas fa-times me-1"></i> Деактивирай
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Активни сесии</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Устройство</th>
                                            <th>IP адрес</th>
                                            <th>Последна активност</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($active_sessions as $session): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas <?= $session['device_type'] === 'mobile' ? 'fa-mobile-alt' : 'fa-desktop' ?> 
                                                                    fa-lg me-2 text-muted"></i>
                                                        <div>
                                                            <div><?= htmlspecialchars($session['browser']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($session['os']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($session['ip']) ?></td>
                                                <td>
                                                    <?= date('d.m.Y H:i', strtotime($session['last_activity'])) ?>
                                                    <?php if ($session['is_current']): ?>
                                                        <span class="badge bg-success ms-2">Текуща сесия</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php if (!$session['is_current']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="terminateSession('<?= $session['id'] ?>')">
                                                            Прекрати
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-danger" onclick="terminateAllSessions()">
                                <i class="fas fa-sign-out-alt me-1"></i> Прекрати всички други сесии
                            </button>
                        </div>
                    </div>
                </div>

                <!-- История на влизанията -->
                <div class="tab-pane fade" id="history">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">История на влизанията</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Дата и час</th>
                                            <th>IP адрес</th>
                                            <th>Устройство</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($login_history as $login): ?>
                                            <tr>
                                                <td><?= date('d.m.Y H:i:s', strtotime($login['created_at'])) ?></td>
                                                <td>
                                                    <div><?= htmlspecialchars($login['ip']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($login['location']) ?></small>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($login['browser']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($login['os']) ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($login['success']): ?>
                                                        <span class="badge bg-success">Успешно</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger" title="<?= htmlspecialchars($login['error']) ?>">
                                                            Неуспешно
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
            </div>
        </div>
    </div>
</div>

<!-- Модал за настройка на 2FA -->
<div class="modal fade" id="setup2FAModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Настройка на двуфакторна автентикация</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div id="qrCode" class="mb-3"></div>
                    <p class="mb-1">Сканирайте QR кода с приложението Google Authenticator</p>
                    <small class="text-muted">
                        или въведете този код ръчно: <strong id="secretKey"></strong>
                    </small>
                </div>

                <form id="verify2FAForm" method="POST" action="/profile/enable-2fa">
                    <div class="mb-3">
                        <label class="form-label">Въведете кода за потвърждение</label>
                        <input type="text" name="code" class="form-control" required
                               pattern="[0-9]{6}" maxlength="6">
                        <div class="form-text">
                            Въведете 6-цифрения код от приложението
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Потвърди и активирай
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Запазване на лични данни
document.getElementById('personalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Личните данни са обновени успешно!');
        } else {
            alert('Възникна грешка при обновяването на личните данни.');
        }
    });
});

// Смяна на парола
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        alert('Паролите не съвпадат!');
        return;
    }
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Паролата е сменена успешно!');
            this.reset();
        } else {
            alert(result.error || 'Възникна грешка при смяната на паролата.');
        }
    });
});

// Настройка на 2FA
let setup2FAModal;
document.addEventListener('DOMContentLoaded', function() {
    setup2FAModal = new bootstrap.Modal(document.getElementById('setup2FAModal'));
});

function setup2FA() {
    fetch('/profile/get-2fa-secret')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('qrCode').innerHTML = result.qrCode;
                document.getElementById('secretKey').textContent = result.secret;
                setup2FAModal.show();
            } else {
                alert('Възникна грешка при генерирането на 2FA ключ.');
            }
        });
}

// Потвърждаване на 2FA
document.getElementById('verify2FAForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            setup2FAModal.hide();
            location.reload();
        } else {
            alert('Невалиден код. Моля, опитайте отново.');
        }
    });
});

// Деактивиране на 2FA
function disable2FA() {
    if (confirm('Сигурни ли сте, че искате да деактивирате двуфакторната автентикация?')) {
        fetch('/profile/disable-2fa', { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при деактивирането на 2FA.');
                }
            });
    }
}

// Прекратяване на сесия
function terminateSession(sessionId) {
    if (confirm('Сигурни ли сте, че искате да прекратите тази сесия?')) {
        fetch(`/profile/terminate-session/${sessionId}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при прекратяването на сесията.');
                }
            });
    }
}

// Прекратяване на всички сесии
function terminateAllSessions() {
    if (confirm('Сигурни ли сте, че искате да прекратите всички други сесии?')) {
        fetch('/profile/terminate-all-sessions', { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при прекратяването на сесиите.');
                }
            });
    }
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 