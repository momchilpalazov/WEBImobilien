<?php
use App\Helpers\Format;

$this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Документи - <?= Format::entityType($entity_type) ?>
                        <?php if (isset($entity['title'])): ?>
                            : <?= htmlspecialchars($entity['title']) ?>
                        <?php elseif (isset($entity['name'])): ?>
                            : <?= htmlspecialchars($entity['name']) ?>
                        <?php endif; ?>
                    </h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload"></i> Качване на документ
                    </button>
                </div>
                <div class="card-body">
                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Общо документи</h6>
                                    <h4 class="card-title mb-0"><?= number_format($statistics['general']['total_documents']) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Общ размер</h6>
                                    <h4 class="card-title mb-0"><?= Format::fileSize($statistics['general']['total_size']) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">По категории</h6>
                                    <div class="d-flex flex-wrap gap-3">
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
                    
                    <!-- Списък с документи -->
                    <?php if (empty($documents)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Няма качени документи
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($documents as $document): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> fa-2x text-muted me-3"></i>
                                                <div>
                                                    <h5 class="card-title mb-0">
                                                        <?= htmlspecialchars($document['title']) ?>
                                                    </h5>
                                                    <small class="text-muted">
                                                        <?= Format::fileSize($document['file_size']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4">Категория</dt>
                                                <dd class="col-sm-8"><?= $categories[$document['category']] ?? '-' ?></dd>
                                                
                                                <dt class="col-sm-4">Качен от</dt>
                                                <dd class="col-sm-8"><?= htmlspecialchars($document['created_by_name']) ?></dd>
                                                
                                                <dt class="col-sm-4">Дата</dt>
                                                <dd class="col-sm-8"><?= Format::date($document['created_at']) ?></dd>
                                            </dl>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="btn-group w-100">
                                                <a href="/documents/download/<?= $document['id'] ?>" 
                                                   class="btn btn-outline-primary"
                                                   title="Изтегляне">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-info"
                                                        onclick="shareDocument(<?= $document['id'] ?>)"
                                                        title="Споделяне">
                                                    <i class="fas fa-share-alt"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-warning"
                                                        onclick="editDocument(<?= $document['id'] ?>)"
                                                        title="Редактиране">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger"
                                                        onclick="deleteDocument(<?= $document['id'] ?>)"
                                                        title="Изтриване">
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
                    <input type="hidden" name="entity_type" value="<?= $entity_type ?>">
                    <input type="hidden" name="entity_id" value="<?= $entity_id ?>">
                    
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

// Зареждане на клиенти
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
</script>
<?php $this->end() ?> 