<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Документи</h1>
        <div>
            <a href="/documents/templates" class="btn btn-outline-primary me-2">
                <i class="fas fa-file-alt"></i> Шаблони
            </a>
            <a href="/documents/upload" class="btn btn-primary">
                <i class="fas fa-upload"></i> Качи документ
            </a>
        </div>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
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
                <div class="col-md-3">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Всички</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Чернова</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Архивиран</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Търсене</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Заглавие, описание..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Търси
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Списък с документи -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <p class="text-muted text-center mb-0">Няма намерени документи</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Заглавие</th>
                                <th>Категория</th>
                                <th>Тип</th>
                                <th>Размер</th>
                                <th>Статус</th>
                                <th>Създаден на</th>
                                <th>Създаден от</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> fa-lg text-muted me-2"></i>
                                            <a href="/documents/view/<?= $document['id'] ?>">
                                                <?= htmlspecialchars($document['title']) ?>
                                            </a>
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
                                        echo $categories[$document['category']] ?? 'Неизвестно';
                                        ?>
                                    </td>
                                    <td><?= $document['file_type'] ?></td>
                                    <td><?= Format::fileSize($document['file_size']) ?></td>
                                    <td>
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
                                    </td>
                                    <td><?= Format::date($document['created_at']) ?></td>
                                    <td><?= htmlspecialchars($document['created_by_name']) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/documents/view/<?= $document['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Преглед">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/documents/download/<?= $document['id'] ?>"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Изтегли">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <?php if ($document['status'] !== 'archived'): ?>
                                                <a href="/documents/update/<?= $document['id'] ?>"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Редактирай">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info"
                                                        onclick="shareDocument(<?= $document['id'] ?>)"
                                                        title="Сподели">
                                                    <i class="fas fa-share-alt"></i>
                                                </button>
                                                <?php if ($document['status'] === 'active'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning"
                                                            onclick="archiveDocument(<?= $document['id'] ?>)"
                                                            title="Архивирай">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteDocument(<?= $document['id'] ?>)"
                                                        title="Изтрий">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
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
let activeDocumentId = null;

function shareDocument(id) {
    activeDocumentId = id;
    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
    modal.show();
}

function submitShare() {
    const form = document.getElementById('shareForm');
    const formData = new FormData(form);
    
    fetch(`/documents/share/${activeDocumentId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
            form.reset();
            // Show success message
            alert('Документът е споделен успешно');
        } else {
            alert(data.error || 'Възникна грешка при споделянето');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Възникна грешка при споделянето');
    });
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
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при изтриването');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Auto-submit filters
document.querySelectorAll('select[name="category"], select[name="status"]').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 