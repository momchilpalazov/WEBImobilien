<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Редактиране на шаблон</h3>
                    <div class="card-tools">
                        <a href="/admin/contracts/templates" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="templateForm" action="/admin/contracts/templates/edit/<?= $type ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Налични променливи:</label>
                            <div class="alert alert-info">
                                <?php foreach ($variables as $variable): ?>
                                    <code>{{<?= htmlspecialchars($variable) ?>}}</code>
                                <?php endforeach ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Съдържание на шаблона:</label>
                            <textarea id="content" name="content" class="form-control" rows="20"><?= htmlspecialchars($template) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Запазване
                            </button>
                            <button type="button" class="btn btn-info" id="previewBtn">
                                <i class="fas fa-eye"></i> Преглед
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Преглед на шаблона</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затваряне</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#content'))
        .catch(error => {
            console.error(error);
        });
    
    let previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Handle form submission
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Шаблонът е запазен успешно.');
            } else {
                alert(data.message || 'Възникна грешка при запазването на шаблона.');
            }
        })
        .catch(() => {
            alert('Възникна грешка при запазването на шаблона.');
        });
    });
    
    // Handle preview button click
    document.getElementById('previewBtn').addEventListener('click', function() {
        fetch(`/admin/contracts/templates/preview/<?= $type ?>`, {
            method: 'POST',
            body: new FormData(document.getElementById('templateForm')),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let frame = document.getElementById('previewFrame');
                frame.contentWindow.document.open();
                frame.contentWindow.document.write(data.content);
                frame.contentWindow.document.close();
                previewModal.show();
            } else {
                alert(data.message || 'Възникна грешка при генерирането на преглед.');
            }
        })
        .catch(() => {
            alert('Възникна грешка при генерирането на преглед.');
        });
    });
});
</script>
<?php $this->end() ?> 