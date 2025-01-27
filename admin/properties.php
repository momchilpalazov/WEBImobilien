<?php
require_once 'includes/auth.php';
require_once "../config/database.php";
use App\Database;

checkAuth();
checkPermission('manage_properties');

$page_title = 'Имоти';
$db = Database::getInstance()->getConnection();

// Вземане на всички имоти
$properties = $db->query("
    SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image,
        (SELECT COUNT(*) FROM inquiries WHERE property_id = p.id AND status = 'new') as inquiries_count
    FROM properties p 
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панел - <?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-xxl">
            <a class="navbar-brand" href="index.php">
                <img src="../images/logo.svg" alt="Industrial Properties" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2 me-2"></i>Табло
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="properties.php">
                            <i class="bi bi-building me-2"></i>Имоти
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">
                            <i class="bi bi-envelope me-2"></i>Запитвания
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Към сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Изход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid flex-grow-1 py-4">
        <div class="container-xxl">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление на имоти</h1>
                <a href="edit-property.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Добави имот
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th width="80">Снимка</th>
                                <th>Заглавие</th>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th>Цена</th>
                                <th>Площ</th>
                                <th>Локация</th>
                                <th>Запитвания</th>
                                <th width="120">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <?php if ($property['main_image']): ?>
                                    <img src="../uploads/properties/thumbnails/<?php echo htmlspecialchars($property['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($property['title_bg']); ?>"
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($property['title_bg']); ?></div>
                                    <div class="small text-muted">
                                        ID: <?php echo $property['id']; ?> | 
                                        <?php echo date('d.m.Y', strtotime($property['created_at'])); ?>
                                        <?php if ($property['featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($property['type']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $property['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'available' => 'Свободен',
                                            'reserved' => 'Резервиран',
                                            'rented' => 'Отдаден',
                                            'sold' => 'Продаден'
                                        ];
                                        echo $status_labels[$property['status']] ?? $property['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($property['price'], 0, ',', ' '); ?> €</td>
                                <td><?php echo number_format($property['area'], 0, ',', ' '); ?> м²</td>
                                <td><?php echo htmlspecialchars($property['location_bg']); ?></td>
                                <td>
                                    <?php if ($property['inquiries_count'] > 0): ?>
                                    <span class="badge bg-info">
                                        <?php echo $property['inquiries_count']; ?> нови
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-property.php?id=<?php echo $property['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Редактирай">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Изтрий"
                                                onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <a href="../property.php?id=<?php echo $property['id']; ?>" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Преглед"
                                           target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
    function deleteProperty(id) {
        if (confirm('Сигурни ли сте, че искате да изтриете този имот?')) {
            fetch('ajax/delete-property.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Имотът беше изтрит успешно');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Възникна грешка при изтриването', 'error');
            });
        }
    }
    </script>
</body>
</html> 