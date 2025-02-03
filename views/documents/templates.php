<?php
use App\Utils\Format;
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Шаблони</h1>
        <div>
            <a href="/documents/upload?template=1" class="btn btn-primary">
                <i class="fas fa-upload"></i> Качи шаблон
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

    <?php if (empty($templates)): ?>
        <div class="card">
            <div class="card-body text-center text-muted">
                <p class="mb-0">Няма намерени шаблони</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($templates as $template): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-file-<?= Format::fileIcon($template['file_type']) ?> fa-2x text-muted me-3"></i>
                                <div>
                                    <h5 class="card-title mb-0">
                                        <?= htmlspecialchars($template['title']) ?>
                                    </h5>
                                    <small class="text-muted">
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
                                    </small>
                                </div>
                            </div>

                            <?php if (!empty($template['description'])): ?>
                                <p class="card-text">
                                    <?= nl2br(htmlspecialchars($template['description'])) ?>
                                </p>
                            <?php endif; ?>

                            <div class="text-muted small mb-3">
                                <div>Размер: <?= Format::fileSize($template['file_size']) ?></div>
                                <div>Създаден на: <?= Format::date($template['created_at']) ?></div>
                                <div>Създаден от: <?= htmlspecialchars($template['created_by_name']) ?></div>
                                <div>Използван: <?= $template['usage_count'] ?> пъти</div>
                            </div>

                            <div class="btn-group w-100">
                                <button type="button" 
                                        class="btn btn-primary"
                                        onclick="useTemplate(<?= $template['id'] ?>)">
                                    <i class="fas fa-plus"></i> Използвай
                                </button>
                                <a href="/documents/download/<?= $template['id'] ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="/documents/update/<?= $template['id'] ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-danger"
                                        onclick="deleteTemplate(<?= $template['id'] ?>)">
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

<!-- Use Template Modal -->
<div class="modal fade" id="useTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Използване на шаблон</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="useTemplateForm" method="POST">
                    <div class="mb-3">
                        <label for="new_title" class="form-label">Заглавие на новия документ *</label>
                        <input type="text" class="form-control" id="new_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="new_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="relation_type" class="form-label">Свързан с</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-select" name="relation_type" id="relation_type">
                                    <option value="">-- Изберете тип --</option>
                                    <option value="property">Имот</option>
                                    <option value="deal">Сделка</option>
                                    <option value="client">Клиент</option>
                                    <option value="agent">Агент</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <select class="form-select" name="relation_id" id="relation_id" disabled>
                                    <option value="">-- Изберете --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="submitUseTemplate()">
                    <i class="fas fa-plus"></i> Създай документ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let activeTemplateId = null;

// Auto-submit filters
document.querySelector('select[name="category"]').addEventListener('change', function() {
    this.form.submit();
});

// Relation type handling
document.getElementById('relation_type').addEventListener('change', function() {
    const relationId = document.getElementById('relation_id');
    relationId.disabled = !this.value;
    relationId.innerHTML = '<option value="">-- Изберете --</option>';
    
    if (this.value) {
        fetch(`/documents/get-relations/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    relationId.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
});

function useTemplate(id) {
    activeTemplateId = id;
    const modal = new bootstrap.Modal(document.getElementById('useTemplateModal'));
    modal.show();
}

function submitUseTemplate() {
    const form = document.getElementById('useTemplateForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const formData = new FormData(form);
    
    fetch(`/documents/use-template/${activeTemplateId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/documents/view/${data.document_id}`;
        } else {
            alert(data.error || 'Възникна грешка при създаването на документа');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Възникна грешка при създаването на документа');
    });
}

function deleteTemplate(id) {
    if (!confirm('Сигурни ли сте, че искате да изтриете този шаблон? Това действие е необратимо!')) {
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
</script>

<?php require_once 'views/layout/footer.php'; ?> 