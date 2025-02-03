<?php
require_once 'header.php';
require_once '../includes/Database.php';

use App\Database;

$page_title = "Управление на блог";
$success_message = '';
$error_message = '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Обработка на изтриване
    if (isset($_POST['delete']) && isset($_POST['post_id'])) {
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_POST['post_id']]);
        $success_message = "Публикацията беше изтрита успешно.";
    }
    
    // Вземане на всички публикации
    $stmt = $db->query("
        SELECT id, title_bg, category, status, published_at, views 
        FROM blog_posts 
        ORDER BY published_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Blog admin error: " . $e->getMessage());
    $error_message = "Възникна грешка при обработката на заявката.";
}

// Категории за филтриране
$categories = [
    'industry_articles' => 'Статии за индустриални имоти',
    'sector_news' => 'Новини от сектора',
    'investor_tips' => 'Съвети за инвеститори'
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Управление на блог публикации</h2>
        <a href="blog-edit.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Нова публикация
        </a>
    </div>
    
    <?php if ($success_message): ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Заглавие</th>
                            <th>Категория</th>
                            <th>Статус</th>
                            <th>Дата на публикуване</th>
                            <th>Преглеждания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title_bg']); ?></td>
                            <td><?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?></td>
                            <td>
                                <span class="badge <?php echo $post['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $post['status'] === 'published' ? 'Публикувана' : 'Чернова'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($post['published_at'])); ?></td>
                            <td><?php echo $post['views']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="blog-edit.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Сигурни ли сте, че искате да изтриете тази публикация?');">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Все още няма създадени публикации.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 