<?php
session_start();
require_once 'includes/header.php';
require_once 'src/Database.php';
require_once 'includes/language.php';
require_once 'includes/blog_translations.php';

use App\Database;

try {
    // Вземане на slug от URL параметъра
    $slug = $_GET['slug'] ?? '';
    
    if (empty($slug)) {
        throw new Exception('Невалиден идентификатор на публикация');
    }
    
    // Връзка с базата данни
    $db = Database::getInstance()->getConnection();
    
    // Заявка за взимане на публикацията
    $stmt = $db->prepare("
        SELECT id, title_bg, title_de, title_en, title_ru,
               content_bg, content_de, content_en, content_ru,
               category, image_path, created_at, views 
        FROM blog_posts 
        WHERE slug = ? AND status = 'published'
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        throw new Exception('Публикацията не е намерена');
    }
    
    // Увеличаване на броя преглеждания
    $updateViews = $db->prepare("
        UPDATE blog_posts 
        SET views = views + 1 
        WHERE id = ?
    ");
    $updateViews->execute([$post['id']]);
    
    // Определяне на текущия език
    $lang = $_SESSION['language'] ?? 'bg';
    
    // Вземане на заглавието и съдържанието според езика
    $title = $post["title_$lang"];
    $content = $post["content_$lang"];
    
    // Форматиране на датата
    $date = new DateTime($post['created_at']);
    $formattedDate = $date->format('d.m.Y');
    
    // Вземаме текущия език
    $current_lang = $_SESSION['language'] ?? 'bg';
    $translations = $blog_translations[$current_lang];
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="content-container">
    <div class="container-fluid px-4 py-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
                <br>
                <a href="/blog.php" class="btn btn-primary mt-3">Към всички публикации</a>
            </div>
        <?php else: ?>
            <main class="flex-grow-1">
                <div class="container-xxl py-5">
                    <div class="row">
                        <!-- Заглавие -->
                        <div class="col-12">
                            <h2><?php echo htmlspecialchars($post['title_' . $current_lang]); ?></h2>
                            <div class="heading-divider"></div>
                        </div>
                        <!-- Основно съдържание -->
                        <div class="col-lg-8">
                            <article class="blog-post">
                                <header class="mb-4">
                                    <h1 class="display-4 mb-3"><?php echo htmlspecialchars($title); ?></h1>
                                    <div class="meta text-muted mb-3">
                                        <span class="date">
                                            <i class="bi bi-calendar3"></i> 
                                            <?php echo $formattedDate; ?>
                                        </span>
                                        <span class="category ms-3">
                                            <i class="bi bi-folder"></i> 
                                            <?php echo htmlspecialchars($post['category']); ?>
                                        </span>
                                        <span class="views ms-3">
                                            <i class="bi bi-eye"></i> 
                                            <?php echo number_format($post['views']); ?> прегледа
                                        </span>
                                    </div>
                                </header>
                                
                                <?php if (!empty($post['image_path'])): ?>
                                    <div class="featured-image mb-4">
                                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($title); ?>"
                                             class="img-fluid rounded">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="content">
                                    <?php echo $content; ?>
                                </div>
                                
                                <footer class="mt-5">
                                    <a href="/blog.php" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        <?php echo $translations['back_to_posts']; ?>
                                    </a>
                                </footer>
                            </article>
                        </div>
                    </div>
                </div>
            </main>
        <?php endif; ?>
    </div>
</div>

<style>
/* Override Bootstrap Container Styles */
.container,
.container-sm,
.container-md,
.container-lg,
.container-xl,
.container-xxl,
.container-fluid {
    max-width: 100% !important;
    width: 100% !important;
    padding-right: 1.5rem !important;
    padding-left: 1.5rem !important;
}

/* Content Container */
.content-container {
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
}
</style>

<?php require_once 'includes/footer.php'; ?> 