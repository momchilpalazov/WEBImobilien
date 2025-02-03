<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Преглед на шаблон</h3>
                    <div class="card-tools">
                        <a href="/admin/contracts/templates" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <a href="/admin/contracts/templates/edit/<?= $type ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Редактиране
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="border p-3" style="min-height: 600px;">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('styles') ?>
<style>
@media print {
    .card-header, .card-tools {
        display: none;
    }
    .card {
        border: none;
    }
    .card-body {
        padding: 0;
    }
    .border {
        border: none !important;
        padding: 0 !important;
    }
}
</style>
<?php $this->end() ?> 