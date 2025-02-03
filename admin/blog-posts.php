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

// Вземане на общия брой публикации
$total_items = $db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Вземане на публикациите за текущата страница
$stmt = $db->prepare("
    SELECT * FROM blog_posts 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление на блог - Industrial Properties</title>
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
                        <a class="nav-link" href="index.php">Начало</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="seo.php">
                            <i class="bi bi-search me-2"></i>SEO Оптимизация
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">Запитвания</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="blog-posts.php">
                            <i class="bi bi-file-text me-2"></i>Блог
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Управление на блог публикации</h5>
                <a href="edit-blog-post.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Добави публикация
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Заглавие</th>
                                <th>Категория</th>
                                <th>Статус</th>
                                <th>Преглеждания</th>
                                <th>Дата на публикуване</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td><?php echo htmlspecialchars($post['title_bg']); ?></td>
                                <td><?php echo htmlspecialchars($post['category']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $post['status'] === 'published' ? 'Публикувана' : 'Чернова'; ?>
                                    </span>
                                </td>
                                <td><?php echo $post['views']; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($post['published_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit-blog-post.php?id=<?php echo $post['id']; ?>" 
                                           class="btn btn-primary" title="Редактирай">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Изтрий"
                                                onclick="if(confirm('Сигурни ли сте, че искате да изтриете тази публикация?')) deleteBlogPost(<?php echo $post['id']; ?>)">
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
    function deleteBlogPost(id) {
        fetch('ajax/delete-blog-post.php', {
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
                alert('Грешка при изтриване на публикацията: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Възникна грешка при изтриване на публикацията');
        });
    }
    </script>
</body>
</html> 