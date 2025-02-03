<?php
/**
 * Properties list view
 * @var array $translations
 * @var array $properties
 * @var int $currentPage
 * @var int $totalPages
 * @var array $types
 * @var array $statuses
 * @var array $filters
 * @var array $sortOptions
 */
$translations = $translations ?? [];
$properties = $properties ?? [];
$pagination = $pagination ?? [];
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Управление на имоти</h1>
    
    <div class="mb-4 d-flex justify-content-between">
        <a href="/admin/properties/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Добави нов имот
        </a>
        <a href="/admin/properties/export<?= !empty($filters) ? '?' . http_build_query($filters) : '' ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Експорт в Excel
        </a>
    </div>

    <?php require __DIR__ . '/filters.php'; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Списък с имоти
            </div>
            <div id="bulkActions" class="d-none">
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        Промяна на статус
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($statuses as $value => $label): ?>
                            <li>
                                <a class="dropdown-item bulk-status-change" href="#" data-status="<?= $value ?>">
                                    <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button type="button" class="btn btn-danger" id="bulkDelete">
                    <i class="fas fa-trash me-1"></i> Изтрий избраните
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($properties)): ?>
                <div class="alert alert-info">
                    Няма намерени имоти<?= !empty($filters) ? ' с избраните филтри' : '' ?>.
                </div>
            <?php else: ?>
                <form id="propertiesForm" method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Заглавие</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th>Цена</th>
                                    <th>Площ</th>
                                    <th>Локация</th>
                                    <th>Създаден</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input property-checkbox" 
                                                       type="checkbox" 
                                                       name="selected[]" 
                                                       value="<?= $property->id ?>">
                                            </div>
                                        </td>
                                        <td><?= $property->id ?></td>
                                        <td><?= htmlspecialchars($property->title_bg) ?></td>
                                        <td><?= $types[$property->type] ?? $property->type ?></td>
                                        <td>
                                            <span class="badge bg-<?= $property->status === 'available' ? 'success' : 
                                                ($property->status === 'reserved' ? 'warning' : 'secondary') ?>">
                                                <?= $statuses[$property->status] ?? $property->status ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($property->price, 2) ?> €</td>
                                        <td><?= number_format($property->area, 2) ?> m²</td>
                                        <td><?= htmlspecialchars($property->location) ?></td>
                                        <td><?= date('d.m.Y', strtotime($property->created_at)) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/properties/edit/<?= $property->id ?>" 
                                                   class="btn btn-sm btn-primary" title="Редактирай">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal<?= $property->id ?>"
                                                        title="Изтрий">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Модален прозорец за масово изтриване -->
                <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Потвърждение за изтриване</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                Сигурни ли сте, че искате да изтриете избраните имоти?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                                <button type="button" class="btn btn-danger" id="confirmBulkDelete">Изтрий</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('propertiesForm');
                        const checkboxes = document.querySelectorAll('.property-checkbox');
                        const selectAll = document.getElementById('selectAll');
                        const bulkActions = document.getElementById('bulkActions');
                        const bulkDelete = document.getElementById('bulkDelete');
                        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
                        const confirmBulkDelete = document.getElementById('confirmBulkDelete');
                        const statusLinks = document.querySelectorAll('.bulk-status-change');

                        // Показване/скриване на бутоните за масови действия
                        function updateBulkActions() {
                            const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');
                            bulkActions.classList.toggle('d-none', checkedBoxes.length === 0);
                        }

                        // Избиране/премахване на всички
                        selectAll.addEventListener('change', function() {
                            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                            updateBulkActions();
                        });

                        // Обновяване при промяна на единичен чекбокс
                        checkboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', updateBulkActions);
                        });

                        // Масово изтриване
                        bulkDelete.addEventListener('click', () => bulkDeleteModal.show());

                        confirmBulkDelete.addEventListener('click', function() {
                            form.action = '/admin/properties/bulk-delete';
                            form.submit();
                        });

                        // Масова промяна на статус
                        statusLinks.forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                const status = this.dataset.status;
                                form.action = `/admin/properties/bulk-status-change?status=${status}`;
                                form.submit();
                            });
                        });
                    });
                </script>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($filters) ? '&' . http_build_query($filters) : '' ?>">
                                        Предишна
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($filters) ? '&' . http_build_query($filters) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= !empty($filters) ? '&' . http_build_query($filters) : '' ?>">
                                        Следваща
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
function getStatusBadgeClass(string $status): string
{
    return match ($status) {
        'available' => 'success',
        'reserved' => 'warning',
        'rented' => 'info',
        'sold' => 'secondary',
        default => 'light'
    };
} 