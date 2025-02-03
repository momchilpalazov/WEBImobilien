<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Шаблони на договори</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Тип договор</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $type => $name): ?>
                                <tr>
                                    <td><?= htmlspecialchars($name) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/contracts/templates/edit/<?= $type ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Редактиране
                                            </a>
                                            <a href="/admin/contracts/templates/preview/<?= $type ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Преглед
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-template" data-type="<?= $type ?>">
                                                <i class="fas fa-trash"></i> Изтриване
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for delete confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Потвърждение за изтриване</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Сигурни ли сте, че искате да изтриете този шаблон?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Изтриване</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let templateToDelete = null;
    
    // Handle delete button click
    document.querySelectorAll('.delete-template').forEach(button => {
        button.addEventListener('click', function() {
            templateToDelete = this.dataset.type;
            deleteModal.show();
        });
    });
    
    // Handle delete confirmation
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!templateToDelete) return;
        
        fetch(`/admin/contracts/templates/delete/${templateToDelete}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            deleteModal.hide();
            
            if (data.success) {
                // Reload the page to show updated list
                window.location.reload();
            } else {
                alert(data.message || 'Възникна грешка при изтриването на шаблона.');
            }
        })
        .catch(() => {
            deleteModal.hide();
            alert('Възникна грешка при изтриването на шаблона.');
        });
    });
});
</script>
<?php $this->end() ?> 