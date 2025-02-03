<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Управление на документи</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload"></i> Качване на документ
                    </button>
                </div>
                <div class="card-body">
                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Общо документи</h6>
                                    <h4 class="card-title mb-0"><?= number_format($statistics['general']['total_documents']) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Общ размер</h6>
                                    <h4 class="card-title mb-0"><?= formatFileSize($statistics['general']['total_size']) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Документи по категории</h6>
                                    <div class="d-flex justify-content-between">
                                        <?php foreach ($statistics['by_category'] as $category => $count): ?>
                                            <div class="text-center">
                                                <small class="d-block text-muted"><?= $categories[$category] ?></small>
                                                <strong><?= $count ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Филтри -->
                    <form id="filterForm" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="title" placeholder="Търсене по заглавие"
                                   value="<?= htmlspecialchars($criteria['title']) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="category">
                                <option value="">Всички категории</option>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $criteria['category'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="entity_type">
                                <option value="">Всички типове</option>
                                <option value="property" <?= $criteria['entity_type'] === 'property' ? 'selected' : '' ?>>
                                    Имоти
                                </option>
                                <option value="client" <?= $criteria['entity_type'] === 'client' ? 'selected' : '' ?>>
                                    Клиенти
                                </option>
                                <option value="contract" <?= $criteria['entity_type'] === 'contract' ? 'selected' : '' ?>>
                                    Договори
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" placeholder="От дата"
                                   value="<?= $criteria['date_from'] ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" placeholder="До дата"
                                   value="<?= $criteria['date_to'] ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Таблица с документи -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Заглавие</th>
                                    <th>Категория</th>
                                    <th>Тип</th>
                                    <th>Размер</th>
                                    <th>Качен от</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($documents)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted mb-0">Не са намерени документи</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $document): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-<?= getFileIcon($document['file_type']) ?> fa-lg text-muted me-2"></i>
                                                    <?= htmlspecialchars($document['title']) ?>
                                                </div>
                                            </td>
                                            <td><?= $categories[$document['category']] ?? '-' ?></td>
                                            <td><?= formatEntityType($document['entity_type']) ?></td>
                                            <td><?= formatFileSize($document['file_size']) ?></td>
                                            <td><?= htmlspecialchars($document['created_by_name']) ?></td>
                                            <td><?= formatDate($document['created_at']) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/documents/download/<?= $document['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Изтегляне">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info"
                                                            onclick="shareDocument(<?= $document['id'] ?>)"
                                                            title="Споделяне">
                                                        <i class="fas fa-share-alt"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning"
                                                            onclick="editDocument(<?= $document['id'] ?>)"
                                                            title="Редактиране">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteDocument(<?= $document['id'] ?>)"
                                                            title="Изтриване">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
                    <?php if ($total > $per_page): ?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <p class="text-muted mb-0">
                                Показани <?= count($documents) ?> от <?= $total ?> документа
                            </p>
                            <?= $this->insert('partials/pagination', [
                                'total' => $total,
                                'page' => $page,
                                'per_page' => $per_page
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за качване -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Качване на документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" action="/admin/documents/upload" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="document" class="form-label">Изберете файл</label>
                        <input type="file" class="form-control" id="document" name="document" required>
                        <small class="text-muted">
                            Позволени формати: PDF, DOC, DOCX, JPG, PNG
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Заглавие</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Категория</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Изберете категория</option>
                            <?php foreach ($categories as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="entity_type" class="form-label">Свързан с</label>
                        <select class="form-select" id="entity_type" name="entity_type">
                            <option value="">Няма</option>
                            <option value="property">Имот</option>
                            <option value="client">Клиент</option>
                            <option value="contract">Договор</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="entityIdContainer" style="display: none;">
                        <label for="entity_id" class="form-label">Изберете запис</label>
                        <select class="form-select" id="entity_id" name="entity_id">
                            <option value="">Зареждане...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="uploadDocument()">Качване</button>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за споделяне -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Споделяне на документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shareForm" action="/admin/documents/share" method="POST">
                    <input type="hidden" name="document_id" id="shareDocumentId">
                    
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Изберете клиент</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="">Зареждане...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Валидност на споделянето</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                        <small class="text-muted">
                            Оставете празно за неограничен достъп
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-primary" onclick="submitShare()">Споделяне</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
// Функции за работа с документи
function uploadDocument() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Възникна грешка при качване на документа');
        }
    })
    .catch(() => {
        alert('Възникна грешка при качване на документа');
    });
}

function shareDocument(id) {
    document.getElementById('shareDocumentId').value = id;
    loadClients();
    $('#shareModal').modal('show');
}

function submitShare() {
    const form = document.getElementById('shareForm');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#shareModal').modal('hide');
            alert('Документът е споделен успешно');
        } else {
            alert(data.message || 'Възникна грешка при споделяне на документа');
        }
    })
    .catch(() => {
        alert('Възникна грешка при споделяне на документа');
    });
}

function deleteDocument(id) {
    if (!confirm('Сигурни ли сте, че искате да изтриете този документ?')) {
        return;
    }
    
    fetch(`/admin/documents/delete/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Възникна грешка при изтриване на документа');
        }
    })
    .catch(() => {
        alert('Възникна грешка при изтриване на документа');
    });
}

// Помощни функции
function loadClients() {
    fetch('/admin/clients/list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('client_id');
            select.innerHTML = '<option value="">Изберете клиент</option>';
            
            data.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.name;
                select.appendChild(option);
            });
        });
}

// Зареждане на свързани записи при промяна на типа
document.getElementById('entity_type').addEventListener('change', function() {
    const container = document.getElementById('entityIdContainer');
    const select = document.getElementById('entity_id');
    
    if (!this.value) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    select.innerHTML = '<option value="">Зареждане...</option>';
    
    fetch(`/admin/${this.value}s/list`)
        .then(response => response.json())
        .then(data => {
            select.innerHTML = `<option value="">Изберете ${this.value}</option>`;
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.title || item.name;
                select.appendChild(option);
            });
        });
});

// Филтриране
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.location.href = `/admin/documents?${params.toString()}`;
});
</script>
<?php $this->end() ?> 