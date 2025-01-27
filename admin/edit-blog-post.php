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

$id = $_GET['id'] ?? null;
$post = null;
$error = null;

try {
    $db = Database::getInstance()->getConnection();
    
    if ($id) {
        // Вземане на съществуваща публикация
        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            header('Location: blog-posts.php');
            exit;
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Обработка на формата при изпращане
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title_bg = $_POST['title_bg'] ?? '';
        $title_de = $_POST['title_de'] ?? '';
        $title_ru = $_POST['title_ru'] ?? '';
        $title_en = $_POST['title_en'] ?? '';
        $content_bg = $_POST['content_bg'] ?? '';
        $content_de = $_POST['content_de'] ?? '';
        $content_ru = $_POST['content_ru'] ?? '';
        $content_en = $_POST['content_en'] ?? '';
        $category = $_POST['category'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $author = $_POST['author'] ?? '';
        $slug = $_POST['slug'] ?? '';
        
        // Валидация
        if (empty($title_bg) || empty($content_bg)) {
            throw new Exception('Заглавието и съдържанието на български са задължителни');
        }
        
        // Генериране на slug, ако не е въведен
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_en)));
        }
        
        if ($id) {
            // Обновяване на съществуваща публикация
            $stmt = $db->prepare("
                UPDATE blog_posts SET 
                    title_bg = ?, title_de = ?, title_ru = ?, title_en = ?,
                    content_bg = ?, content_de = ?, content_ru = ?, content_en = ?,
                    category = ?, status = ?, author = ?, slug = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title_bg, $title_de, $title_ru, $title_en,
                $content_bg, $content_de, $content_ru, $content_en,
                $category, $status, $author, $slug, $id
            ]);
        } else {
            // Създаване на нова публикация
            $stmt = $db->prepare("
                INSERT INTO blog_posts (
                    title_bg, title_de, title_ru, title_en,
                    content_bg, content_de, content_ru, content_en,
                    category, status, author, slug
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title_bg, $title_de, $title_ru, $title_en,
                $content_bg, $content_de, $content_ru, $content_en,
                $category, $status, $author, $slug
            ]);
            
            $id = $db->lastInsertId();
        }
        
        // Обработка на изображението
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/blog/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $stmt = $db->prepare("UPDATE blog_posts SET image_path = ? WHERE id = ?");
                $stmt->execute([$new_filename, $id]);
            }
        }
        
        header('Location: blog-posts.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Редактиране на публикация' : 'Нова публикация'; ?> - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/rl33op7p1ovbmtd2ewd4q42187w17ttu70cufk3qwe146ufe/tinymce/6/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea.editor',
            height: 300,
            plugins: 'link image code table lists',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
        });
    </script>
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
                        <a class="nav-link" href="blog-posts.php">
                            <i class="bi bi-file-text me-2"></i>Блог
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo $id ? 'Редактиране на публикация' : 'Нова публикация'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Заглавие (BG) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title_bg" 
                                           value="<?php echo htmlspecialchars($post['title_bg'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Заглавие (DE)</label>
                                    <input type="text" class="form-control" name="title_de" 
                                           value="<?php echo htmlspecialchars($post['title_de'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Заглавие (RU)</label>
                                    <input type="text" class="form-control" name="title_ru" 
                                           value="<?php echo htmlspecialchars($post['title_ru'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Заглавие (EN)</label>
                                    <input type="text" class="form-control" name="title_en" 
                                           value="<?php echo htmlspecialchars($post['title_en'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Съдържание (BG) <span class="text-danger">*</span></label>
                                <textarea class="form-control editor" name="content_bg" rows="10"><?php echo htmlspecialchars($post['content_bg'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Съдържание (DE)</label>
                                <textarea class="form-control editor" name="content_de" rows="10"><?php echo htmlspecialchars($post['content_de'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Съдържание (RU)</label>
                                <textarea class="form-control editor" name="content_ru" rows="10"><?php echo htmlspecialchars($post['content_ru'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Съдържание (EN)</label>
                                <textarea class="form-control editor" name="content_en" rows="10"><?php echo htmlspecialchars($post['content_en'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Категория</label>
                                    <select class="form-select" name="category">
                                        <option value="industry_articles" <?php echo ($post['category'] ?? '') === 'industry_articles' ? 'selected' : ''; ?>>
                                            Статии за индустриални имоти
                                        </option>
                                        <option value="sector_news" <?php echo ($post['category'] ?? '') === 'sector_news' ? 'selected' : ''; ?>>
                                            Новини от сектора
                                        </option>
                                        <option value="investor_tips" <?php echo ($post['category'] ?? '') === 'investor_tips' ? 'selected' : ''; ?>>
                                            Съвети за инвеститори
                                        </option>
                                        <option value="reports" <?php echo ($post['category'] ?? '') === 'reports' ? 'selected' : ''; ?>>
                                            Доклади
                                        </option>
                                        <option value="podcast" <?php echo ($post['category'] ?? '') === 'podcast' ? 'selected' : ''; ?>>
                                            Подкаст
                                        </option>
                                        <option value="markets" <?php echo ($post['category'] ?? '') === 'markets' ? 'selected' : ''; ?>>
                                            Пазари
                                        </option>
                                        <option value="success_stories" <?php echo ($post['category'] ?? '') === 'success_stories' ? 'selected' : ''; ?>>
                                            Успешни истории
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" name="status">
                                        <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                                            Чернова
                                        </option>
                                        <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                            Публикувана
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Автор</label>
                                    <input type="text" class="form-control" name="author" 
                                           value="<?php echo htmlspecialchars($post['author'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Slug</label>
                                <input type="text" class="form-control" name="slug" 
                                       value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>"
                                       placeholder="Ще бъде генериран автоматично, ако е празен">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Изображение</label>
                                <?php if (!empty($post['image_path'])): ?>
                                <div class="mb-2">
                                    <img src="../uploads/blog/<?php echo htmlspecialchars($post['image_path']); ?>" 
                                         alt="Current image" style="max-height: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            
                            <div class="text-end">
                                <a href="blog-posts.php" class="btn btn-secondary me-2">Отказ</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $id ? 'Запази промените' : 'Създай публикация'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 