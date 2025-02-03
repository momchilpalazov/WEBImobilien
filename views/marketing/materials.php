<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Маркетингови материали</h1>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Качване на материали
            </button>
        </div>
    </div>

    <div class="card my-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <a href="/properties/view/<?= $property['id'] ?>">
                            <?= htmlspecialchars($property['title']) ?>
                        </a>
                    </h5>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="row g-3">
                        <div class="col-sm-6">
                            <select name="type" class="form-select">
                                <option value="">Всички типове</option>
                                <option value="photo" <?= ($filters['type'] ?? '') === 'photo' ? 'selected' : '' ?>>
                                    Снимки
                                </option>
                                <option value="video" <?= ($filters['type'] ?? '') === 'video' ? 'selected' : '' ?>>
                                    Видео
                                </option>
                                <option value="virtual_tour" <?= ($filters['type'] ?? '') === 'virtual_tour' ? 'selected' : '' ?>>
                                    Виртуална обиколка
                                </option>
                                <option value="brochure" <?= ($filters['type'] ?? '') === 'brochure' ? 'selected' : '' ?>>
                                    Брошура
                                </option>
                                <option value="floor_plan" <?= ($filters['type'] ?? '') === 'floor_plan' ? 'selected' : '' ?>>
                                    План на имота
                                </option>
                                <option value="document" <?= ($filters['type'] ?? '') === 'document' ? 'selected' : '' ?>>
                                    Документ
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <select name="status" class="form-select">
                                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                    Активни
                                </option>
                                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                    Неактивни
                                </option>
                                <option value="">Всички</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($materials)): ?>
                <p class="text-muted text-center mb-0">Няма налични материали</p>
            <?php else: ?>
                <div class="row g-4" id="materials-grid">
                    <?php foreach ($materials as $material): ?>
                        <div class="col-md-4 col-lg-3" data-id="<?= $material['id'] ?>">
                            <div class="card h-100">
                                <?php if ($material['type'] === 'photo'): ?>
                                    <img src="<?= htmlspecialchars($material['file_path']) ?>" 
                                         class="card-img-top" alt="<?= htmlspecialchars($material['title']) ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php elseif ($material['type'] === 'video'): ?>
                                    <div class="ratio ratio-16x9">
                                        <video src="<?= htmlspecialchars($material['file_path']) ?>" 
                                               controls>
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                         style="height: 200px;">
                                        <?php
                                        $icon = [
                                            'virtual_tour' => 'fas fa-3d-rotation',
                                            'brochure' => 'fas fa-file-pdf',
                                            'floor_plan' => 'fas fa-blueprint',
                                            'document' => 'fas fa-file-alt'
                                        ][$material['type']] ?? 'fas fa-file';
                                        ?>
                                        <i class="<?= $icon ?> fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($material['title']) ?>
                                        <?php if ($material['is_featured']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php endif; ?>
                                    </h5>
                                    <?php if (!empty($material['description'])): ?>
                                        <p class="card-text">
                                            <?= htmlspecialchars($material['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100">
                                        <?php if (in_array($material['type'], ['photo', 'video', 'virtual_tour'])): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="toggleFeatured(<?= $material['id'] ?>)"
                                                    title="<?= $material['is_featured'] ? 'Премахни от избрани' : 'Добави в избрани' ?>">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= htmlspecialchars($material['file_path']) ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           target="_blank"
                                           title="Преглед">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteMaterial(<?= $material['id'] ?>)"
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Качване на материали</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Тип материал</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">-- Изберете --</option>
                            <option value="photo">Снимка</option>
                            <option value="video">Видео</option>
                            <option value="virtual_tour">Виртуална обиколка</option>
                            <option value="brochure">Брошура</option>
                            <option value="floor_plan">План на имота</option>
                            <option value="document">Документ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Заглавие</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Файл</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text" id="fileHelp"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                <button type="button" class="btn btn-primary" onclick="uploadMaterial()">
                    <i class="fas fa-upload"></i> Качи
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>

<script>
// File type validation
const allowedTypes = {
    photo: ['image/jpeg', 'image/png'],
    video: ['video/mp4', 'video/quicktime'],
    virtual_tour: ['video/mp4', 'application/x-mpegURL'],
    brochure: ['application/pdf'],
    floor_plan: ['image/jpeg', 'image/png', 'application/pdf'],
    document: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
};

document.getElementById('type').addEventListener('change', function() {
    const fileInput = document.getElementById('file');
    const fileHelp = document.getElementById('fileHelp');
    const type = this.value;

    if (type && allowedTypes[type]) {
        fileHelp.textContent = 'Позволени формати: ' + allowedTypes[type].join(', ');
        fileInput.accept = allowedTypes[type].join(',');
    } else {
        fileHelp.textContent = '';
        fileInput.accept = '';
    }
});

// Upload functionality
function uploadMaterial() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);

    fetch('/marketing/upload-material', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Грешка при качването');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Грешка при качването');
    });
}

// Delete functionality
function deleteMaterial(id) {
    if (!confirm('Сигурни ли сте?')) {
        return;
    }

    fetch(`/marketing/delete-material/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-id="${id}"]`).remove();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Toggle featured status
function toggleFeatured(id) {
    fetch(`/marketing/toggle-featured/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Initialize sortable grid
if (document.getElementById('materials-grid')) {
    new Sortable(document.getElementById('materials-grid'), {
        animation: 150,
        onEnd: function(evt) {
            const items = [...evt.to.children].map((el, index) => ({
                id: el.dataset.id,
                sort_order: index
            }));

            fetch('/marketing/update-sort-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(items)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}

// Auto-submit filters
document.querySelectorAll('select[name="type"], select[name="status"]').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 