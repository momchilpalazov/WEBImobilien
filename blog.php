<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/language.php';
require_once 'src/Database.php';

use App\Database;

// Определяне на текущия език
$current_language = $_SESSION['language'] ?? 'bg';

// Заглавия на различни езици
$titles = [
    'bg' => 'Блог и Новини',
    'en' => 'Blog and News',
    'de' => 'Blog und Nachrichten',
    'ru' => 'Блог и Новости'
];

$title = $titles[$current_language];

$error_message = '';

// Вземане на избраната категория от URL
$selected_category = $_GET['category'] ?? 'all';

$page_title = $translations['blog']['title'] . " - Industrial Properties";

try {
    $db = Database::getInstance()->getConnection();
    
    // Проверка дали таблицата съществува
    try {
        $check_table = $db->query("SELECT 1 FROM blog_posts LIMIT 1");
    } catch (PDOException $e) {
        // Проверка дали SQL файлът съществува
        $sql_file = 'database/create_blog_table.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("SQL файлът за създаване на таблицата не съществува в: " . realpath(dirname($sql_file)));
        }
        
        // Ако таблицата не съществува, създаваме я
        $sql = file_get_contents($sql_file);
        if (!$sql) {
            throw new Exception("Грешка при четене на SQL файла: " . $sql_file);
        }
        $db->exec($sql);
        error_log("Blog table created successfully");
        
        // Проверка за наличие на публикации
        $count_check = $db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
        if ($count_check == 0) {
            // Добавяне на примерни публикации
            $default_posts_sql = file_get_contents('database/insert_default_blog_posts.sql');
            if ($default_posts_sql) {
                $db->exec($default_posts_sql);
                error_log("Default blog posts inserted successfully");
            }
        }
    }
    
    // Подготовка на SQL заявката според избраната категория
    $sql = "SELECT * FROM blog_posts WHERE status = 'published'";
    $params = [];
    
    if ($selected_category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $selected_category;
    }
    
    $sql .= " ORDER BY published_at DESC";
    
    // Вземане на публикациите
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Вземане на всички уникални категории от базата данни
    $categories_stmt = $db->query("
        SELECT DISTINCT category 
        FROM blog_posts 
        WHERE status = 'published'
        ORDER BY category
    ");
    $db_categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Създаване на масив с категориите
    $categories = [
        'all' => 'Всички'
    ];
    
    // Добавяне на преводите за категориите
    foreach ($db_categories as $category) {
        $categories[$category] = $translations['blog']['categories'][$category] ?? $category;
    }
    
} catch (PDOException $e) {
    error_log("Blog error: " . $e->getMessage());
    $error_message = "Възникна грешка при зареждане на публикациите. Моля, опитайте отново по-късно.";
    $posts = [];
} catch (Exception $e) {
    error_log("Blog error: " . $e->getMessage());
    $error_message = $e->getMessage();
    $posts = [];
}
?>

<div class="content-container">
    <div class="container-fluid px-4">
        <h1><?php echo $title; ?></h1>
        <div class="heading-divider"></div>
        
        <div class="blog-header py-3 border-bottom">
            <div class="row">
                <div class="col-12">
                    <nav class="blog-categories">
                        <ul class="nav nav-pills">
                            <?php foreach ($categories as $key => $name): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $key === $selected_category ? 'active' : ''; ?>" 
                                   href="?category=<?php echo $key; ?>">
                                    <?php echo $name; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <main class="blog-main py-5">
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card h-100">
                        <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <?php if ($post['image_path']): ?>
                                <img src="uploads/blog/<?php echo htmlspecialchars($post['image_path']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($post['title_' . $current_language]); ?>">
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">
                                            <?php echo $categories[$post['category']] ?? $post['category']; ?>
                                        </span>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y', strtotime($post['published_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <h3 class="card-title h5">
                                        <?php echo htmlspecialchars($post['title_' . $current_language]); ?>
                                    </h3>
                                    
                                    <div class="card-text text-muted">
                                        <?php 
                                        $content = strip_tags($post['content_' . $current_language]);
                                        echo mb_substr($content, 0, 150) . '...';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($posts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <?php echo $translations['blog']['no_posts']; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
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

.blog-header {
    background: #fff;
}

.blog-categories .nav-pills {
    gap: 1rem;
}

.blog-categories .nav-link {
    color: #666;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0;
}

.blog-categories .nav-link:hover {
    color: #000;
}

.blog-categories .nav-link.active {
    background: none;
    color: #000;
    position: relative;
}

.blog-categories .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #0d6efd;
}

.blog-card {
    transition: transform 0.2s;
}

.blog-card:hover {
    transform: translateY(-5px);
}

.blog-card .card-img-top {
    height: 200px;
    object-fit: cover;
}

.blog-card .card-title {
    color: #000;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.blog-card .badge {
    font-weight: 500;
    padding: 0.5em 1em;
}
</style>

<?php require_once 'includes/footer.php'; ?> 