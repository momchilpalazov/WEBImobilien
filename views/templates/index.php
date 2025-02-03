<?php
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Шаблони на документи</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
            <i class="fas fa-plus me-1"></i> Нов шаблон
        </button>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Търсене</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Заглавие или описание"
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Чернова</option>
                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Архивиран</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Сортиране</label>
                    <select name="sort" class="form-select">
                        <option value="name_asc" <?= ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Име (А-Я)</option>
                        <option value="name_desc" <?= ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Име (Я-А)</option>
                        <option value="created_desc" <?= ($filters['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Най-нови</option>
                        <option value="created_asc" <?= ($filters['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Най-стари</option>
                        <option value="usage_desc" <?= ($filters['sort'] ?? '') === 'usage_desc' ? 'selected' : '' ?>>Най-използвани</option>
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

    <!-- Списък с шаблони -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Име</th>
                            <th>Категория</th>
                            <th>Версия</th>
                            <th>Статус</th>
                            <th>Използван</th>
                            <th>Последна промяна</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-<?= Format::fileIcon($template['file_type']) ?> fa-lg text-muted me-2"></i>
                                        <div>
                                            <div><?= htmlspecialchars($template['name']) ?></div>
                                            <?php if (!empty($template['description'])): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($template['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
                                    echo $categories[$template['category']] ?? 'Неизвестно';
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">v<?= $template['version'] ?></span>
                                        <?php if ($template['has_draft']): ?>
                                            <span class="badge bg-warning">Чернова</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'active' => 'success',
                                        'draft' => 'warning',
                                        'archived' => 'secondary'
                                    ];
                                    $statusText = [
                                        'active' => 'Активен',
                                        'draft' => 'Чернова',
                                        'archived' => 'Архивиран'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClasses[$template['status']] ?>">
                                        <?= $statusText[$template['status']] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= number_format($template['usage_count']) ?> пъти
                                </td>
                                <td>
                                    <div title="<?= date('d.m.Y H:i:s', strtotime($template['updated_at'])) ?>">
                                        <?= date('d.m.Y', strtotime($template['updated_at'])) ?>
                                        <div class="small text-muted">
                                            от <?= htmlspecialchars($template['updated_by_name']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="editTemplate(<?= $template['id'] ?>)"
                                                title="Редактиране">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="showVersions(<?= $template['id'] ?>)"
                                                title="История на версиите">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="useTemplate(<?= $template['id'] ?>)"
                                                title="Използвай шаблона">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <?php if ($template['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="archiveTemplate(<?= $template['id'] ?>)"
                                                    title="Архивиране">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="activateTemplate(<?= $template['id'] ?>)"
                                                    title="Активиране">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteTemplate(<?= $template['id'] ?>)"
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

<!-- Модал за добавяне/редактиране на шаблон -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="templateForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Нов шаблон</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Име на шаблона <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Описание</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Категория <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="contract">Договор</option>
                                    <option value="deed">Нотариален акт</option>
                                    <option value="certificate">Сертификат</option>
                                    <option value="permit">Разрешително</option>
                                    <option value="tax">Данъчен документ</option>
                                    <option value="insurance">Застраховка</option>
                                    <option value="appraisal">Оценка</option>
                                    <option value="other">Друго</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Чернова</option>
                                    <option value="active">Активен</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Файл на шаблона <span class="text-danger">*</span></label>
                        <input type="file" name="template_file" class="form-control" 
                               accept=".doc,.docx,.pdf,.odt">
                        <div class="form-text">
                            Поддържани формати: DOC, DOCX, PDF, ODT
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Променливи в шаблона</label>
                        <div class="variables-container">
                            <div class="row mb-2 variable-row">
                                <div class="col-md-4">
                                    <input type="text" name="variables[key][]" class="form-control" 
                                           placeholder="Ключ (напр. client_name)">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="variables[label][]" class="form-control" 
                                           placeholder="Етикет (напр. Име на клиент)">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-variable">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="addVariable">
                            <i class="fas fa-plus me-1"></i> Добави променлива
                        </button>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="save_as_new_version" class="form-check-input" id="saveAsNewVersion">
                        <label class="form-check-label" for="saveAsNewVersion">
                            Запази като нова версия
                        </label>
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

<!-- Модал за преглед на версии -->
<div class="modal fade" id="versionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">История на версиите</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Версия</th>
                                <th>Създадена на</th>
                                <th>Създадена от</th>
                                <th>Коментар</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="versionsTableBody">
                            <!-- Тук ще се зареждат версиите динамично -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Инициализация на модала за шаблон
let templateModal;
document.addEventListener('DOMContentLoaded', function() {
    templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
});

// Добавяне на нова променлива
document.getElementById('addVariable').addEventListener('click', function() {
    const container = document.querySelector('.variables-container');
    const row = document.querySelector('.variable-row').cloneNode(true);
    row.querySelectorAll('input').forEach(input => input.value = '');
    container.appendChild(row);
    
    // Добавяне на слушател за бутона за премахване
    row.querySelector('.remove-variable').addEventListener('click', function() {
        row.remove();
    });
});

// Редактиране на шаблон
function editTemplate(id) {
    fetch(`/templates/get/${id}`)
        .then(response => response.json())
        .then(template => {
            const form = document.getElementById('templateForm');
            form.action = `/templates/edit/${id}`;
            form.querySelector('.modal-title').textContent = 'Редактиране на шаблон';
            
            // Попълване на формата
            form.querySelector('input[name="id"]').value = template.id;
            form.querySelector('input[name="name"]').value = template.name;
            form.querySelector('textarea[name="description"]').value = template.description;
            form.querySelector('select[name="category"]').value = template.category;
            form.querySelector('select[name="status"]').value = template.status;
            
            // Попълване на променливите
            const container = form.querySelector('.variables-container');
            container.innerHTML = '';
            template.variables.forEach(variable => {
                const row = document.querySelector('.variable-row').cloneNode(true);
                row.querySelector('input[name="variables[key][]"]').value = variable.key;
                row.querySelector('input[name="variables[label][]"]').value = variable.label;
                container.appendChild(row);
            });
            
            templateModal.show();
        });
}

// Показване на версии
function showVersions(id) {
    fetch(`/templates/versions/${id}`)
        .then(response => response.json())
        .then(versions => {
            const tbody = document.getElementById('versionsTableBody');
            tbody.innerHTML = '';
            
            versions.forEach(version => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>v${version.version}</td>
                    <td>${new Date(version.created_at).toLocaleString()}</td>
                    <td>${version.created_by_name}</td>
                    <td>${version.comment || ''}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="previewVersion(${version.id})"
                                title="Преглед">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="restoreVersion(${version.id})"
                                title="Възстановяване">
                            <i class="fas fa-undo"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            const modal = new bootstrap.Modal(document.getElementById('versionsModal'));
            modal.show();
        });
}

// Използване на шаблон
function useTemplate(id) {
    window.location.href = `/documents/create?template_id=${id}`;
}

// Архивиране на шаблон
function archiveTemplate(id) {
    if (confirm('Сигурни ли сте, че искате да архивирате този шаблон?')) {
        fetch(`/templates/archive/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при архивирането на шаблона.');
                }
            });
    }
}

// Активиране на шаблон
function activateTemplate(id) {
    if (confirm('Сигурни ли сте, че искате да активирате този шаблон?')) {
        fetch(`/templates/activate/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при активирането на шаблона.');
                }
            });
    }
}

// Изтриване на шаблон
function deleteTemplate(id) {
    if (confirm('Сигурни ли сте, че искате да изтриете този шаблон? Това действие е необратимо!')) {
        fetch(`/templates/delete/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при изтриването на шаблона.');
                }
            });
    }
}

// Преглед на версия
function previewVersion(versionId) {
    window.open(`/templates/preview/${versionId}`, '_blank');
}

// Възстановяване на версия
function restoreVersion(versionId) {
    if (confirm('Сигурни ли сте, че искате да възстановите тази версия?')) {
        fetch(`/templates/restore/${versionId}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Възникна грешка при възстановяването на версията.');
                }
            });
    }
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 