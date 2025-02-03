<?php
session_start();

// Проверка за достъп
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../src/Database.php';
use App\Database;

// Връзка с базата данни
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die('Грешка при свързване с базата данни: ' . $e->getMessage());
}

// Настройки за пагинация
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Вземане на общия брой имоти
$total_items = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Вземане на имотите за текущата страница
$stmt = $db->prepare("
    SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image,
        (SELECT COUNT(*) FROM inquiries WHERE property_id = p.id AND status = 'new') as new_inquiries
    FROM properties p
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$properties = $stmt->fetchAll();

// Статистика
$total_properties = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$available_properties = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
$rented_properties = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'rented'")->fetchColumn();
$sold_properties = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'sold'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панел - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="../images/logo.svg" alt="Logo" style="height: 32px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Начало</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="seo.php">
                            <i class="bi bi-search me-2"></i>SEO Оптимизация
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear-fill me-2"></i>Настройки на сайта
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">
                            <i class="bi bi-envelope me-2"></i>Запитвания
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog-posts.php">
                            <i class="bi bi-file-text me-2"></i>Блог
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <?php
                        // Вземаме текущия статус на режима на поддръжка
                        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
                        $stmt->execute();
                        $maintenance_mode = $stmt->fetchColumn();
                        $is_maintenance = $maintenance_mode === 'true';
                        ?>
                        <button class="nav-link btn <?php echo $is_maintenance ? 'text-danger' : 'text-success'; ?>" 
                                onclick="toggleMaintenanceMode()" 
                                id="maintenanceBtn"
                                style="border: none; background: none;">
                            <i class="bi <?php echo $is_maintenance ? 'bi-toggle-on' : 'bi-toggle-off'; ?> me-2"></i>
                            <span id="maintenanceBtnText">
                                <?php echo $is_maintenance ? 'Изключи режим на поддръжка' : 'Включи режим на поддръжка'; ?>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Към сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Изход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Общо имоти</h5>
                        <h2><?php echo $total_properties; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Свободни</h5>
                        <h2><?php echo $available_properties; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Отдадени</h5>
                        <h2><?php echo $rented_properties; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Продадени</h5>
                        <h2><?php echo $sold_properties; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Последно добавени имоти</h5>
                <a href="edit-property.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Добави имот
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Снимка</th>
                                <th>Заглавие</th>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th>Цена</th>
                                <th>Площ</th>
                                <th>Запитвания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $property['id']; ?></td>
                                <td>
                                    <?php if ($property['main_image']): ?>
                                    <img src="../uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>" 
                                         alt="Property Image" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($property['title_bg']); ?></td>
                                <td><?php echo htmlspecialchars($property['type']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $property['status'] === 'available' ? 'success' : 
                                            ($property['status'] === 'rented' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars($property['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($property['price'], 2); ?> €</td>
                                <td><?php echo number_format($property['area'], 2); ?> м²</td>
                                <td>
                                    <?php if ($property['new_inquiries'] > 0): ?>
                                    <span class="badge bg-danger"><?php echo $property['new_inquiries']; ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_property.php?id=<?php echo $property['id']; ?>" 
                                           class="btn btn-primary" title="Редактирай">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Изтрий"
                                                onclick="if(confirm('Сигурни ли сте, че искате да изтриете този имот?')) deleteProperty(<?php echo $property['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteProperty(id) {
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
                location.reload();
            } else {
                alert('Грешка при изтриване на имота: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Възникна грешка при изтриване на имота');
        });
    }

    function toggleMaintenanceMode() {
        fetch('toggle_maintenance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById('maintenanceBtn');
                const icon = btn.querySelector('i');
                const text = document.getElementById('maintenanceBtnText');
                
                if (data.maintenance_mode === 'true') {
                    btn.classList.remove('text-success');
                    btn.classList.add('text-danger');
                    icon.classList.remove('bi-toggle-off');
                    icon.classList.add('bi-toggle-on');
                    text.textContent = 'Изключи режим на поддръжка';
                } else {
                    btn.classList.remove('text-danger');
                    btn.classList.add('text-success');
                    icon.classList.remove('bi-toggle-on');
                    icon.classList.add('bi-toggle-off');
                    text.textContent = 'Включи режим на поддръжка';
                }
                
                // Показваме съобщение
                alert(data.maintenance_mode === 'true' ? 
                    'Режимът на поддръжка е включен!' : 
                    'Режимът на поддръжка е изключен!');
            } else {
                alert('Възникна грешка при промяна на режима на поддръжка!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Възникна грешка при промяна на режима на поддръжка!');
        });
    }
    </script>
</body>
</html> 