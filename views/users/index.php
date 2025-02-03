<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Управление на потребители</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus"></i> Нов потребител
        </button>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Търсене</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Име, имейл или телефон"
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Роля</label>
                    <select name="role" class="form-select">
                        <option value="">Всички</option>
                        <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Администратор</option>
                        <option value="manager" <?= ($filters['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Мениджър</option>
                        <option value="agent" <?= ($filters['role'] ?? '') === 'agent' ? 'selected' : '' ?>>Агент</option>
                        <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>Потребител</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
                        <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Блокиран</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Сортиране</label>
                    <select name="sort" class="form-select">
                        <option value="name_asc" <?= ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Име (А-Я)</option>
                        <option value="name_desc" <?= ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Име (Я-А)</option>
                        <option value="created_desc" <?= ($filters['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Най-нови</option>
                        <option value="created_asc" <?= ($filters['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Най-стари</option>
                        <option value="last_login_desc" <?= ($filters['sort'] ?? '') === 'last_login_desc' ? 'selected' : '' ?>>Последно влизане</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Търси
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с потребители -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Име</th>
                            <th>Имейл</th>
                            <th>Роля</th>
                            <th>Статус</th>
                            <th>Последно влизане</th>
                            <th>Регистриран на</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($user['avatar']) ?>" 
                                                     alt="<?= htmlspecialchars($user['name']) ?>"
                                                     class="rounded-circle"
                                                     width="32" height="32">
                                            <?php else: ?>
                                                <div class="avatar-placeholder rounded-circle bg-secondary text-white">
                                                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?= htmlspecialchars($user['name']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php
                                    $roleClasses = [
                                        'admin' => 'danger',
                                        'manager' => 'warning',
                                        'agent' => 'info',
                                        'user' => 'secondary'
                                    ];
                                    $roleText = [
                                        'admin' => 'Администратор',
                                        'manager' => 'Мениджър',
                                        'agent' => 'Агент',
                                        'user' => 'Потребител'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $roleClasses[$user['role']] ?>">
                                        <?= $roleText[$user['role']] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'active' => 'success',
                                        'inactive' => 'warning',
                                        'blocked' => 'danger'
                                    ];
                                    $statusText = [
                                        'active' => 'Активен',
                                        'inactive' => 'Неактивен',
                                        'blocked' => 'Блокиран'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClasses[$user['status']] ?>">
                                        <?= $statusText[$user['status']] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <span title="<?= date('d.m.Y H:i:s', strtotime($user['last_login'])) ?>">
                                            <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Никога</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span title="<?= date('d.m.Y H:i:s', strtotime($user['created_at'])) ?>">
                                        <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="editUser(<?= $user['id'] ?>)"
                                                title="Редактиране">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="resetPassword(<?= $user['id'] ?>)"
                                                title="Нулиране на парола">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="blockUser(<?= $user['id'] ?>)"
                                                    title="Блокиране">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="activateUser(<?= $user['id'] ?>)"
                                                    title="Активиране">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteUser(<?= $user['id'] ?>)"
                                                title="Изтриване">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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

<!-- Модал за добавяне на потребител -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUserForm" method="POST" action="/users/add">
                <div class="modal-header">
                    <h5 class="modal-title">Нов потребител</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Име <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имейл <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роля <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user">Потребител</option>
                            <option value="agent">Агент</option>
                            <option value="manager">Мениджър</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Парола <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                <i class="fas fa-dice"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="send_credentials" class="form-check-input" id="sendCredentials">
                        <label class="form-check-label" for="sendCredentials">
                            Изпрати данните за вход по имейл
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                    <button type="submit" class="btn btn-primary">Добави</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модал за редактиране на потребител -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Редактиране на потребител</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Име <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имейл <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роля <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user">Потребител</option>
                            <option value="agent">Агент</option>
                            <option value="manager">Мениджър</option>
                            <option value="admin">Администратор</option>
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
// Генериране на случайна парола
function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.querySelector('input[name="password"]').value = password;
}

// Редактиране на потребител
function editUser(id) {
    fetch(`/users/get/${id}`)
        .then(response => response.json())
        .then(user => {
            const form = document.getElementById('editUserForm');
            form.action = `/users/edit/${id}`;
            form.querySelector('input[name="id"]').value = user.id;
            form.querySelector('input[name="name"]').value = user.name;
            form.querySelector('input[name="email"]').value = user.email;
            form.querySelector('input[name="phone"]').value = user.phone || '';
            form.querySelector('select[name="role"]').value = user.role;
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        });
}

// Нулиране на парола
function resetPassword(id) {
    if (confirm('Сигурни ли сте, че искате да нулирате паролата на този потребител?')) {
        fetch(`/users/reset-password/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Паролата е нулирана успешно. Новата парола е изпратена на имейла на потребителя.');
                } else {
                    alert('Възникна грешка при нулирането на паролата.');
                }
            });
    }
}

// Блокиране на потребител
function blockUser(id) {
    if (confirm('Сигурни ли сте, че искате да блокирате този потребител?')) {
        fetch(`/users/block/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при блокирането на потребителя.');
                }
            });
    }
}

// Активиране на потребител
function activateUser(id) {
    if (confirm('Сигурни ли сте, че искате да активирате този потребител?')) {
        fetch(`/users/activate/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при активирането на потребителя.');
                }
            });
    }
}

// Изтриване на потребител
function deleteUser(id) {
    if (confirm('Сигурни ли сте, че искате да изтриете този потребител? Това действие е необратимо!')) {
        fetch(`/users/delete/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при изтриването на потребителя.');
                }
            });
    }
}

// Валидация на формата за добавяне на потребител
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = this.querySelector('input[name="password"]').value;
    if (password.length < 8) {
        alert('Паролата трябва да бъде поне 8 символа.');
        return;
    }
    
    this.submit();
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 