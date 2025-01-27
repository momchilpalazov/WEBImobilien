<?php
require_once 'header.php';
require_once '../includes/Database.php';

use App\Database;

$page_title = "Редактиране на публикация";
$success_message = '';
$error_message = '';

// Инициализиране на празен пост
$post = [
    'id' => null,
    'title_bg' => '',
    'title_de' => '',
    'title_ru' => '',
    'title_en' => '',
    'content_bg' => '',
    'content_de' => '',
    'content_ru' => '',
    'content_en' => '',
    'category' => '',
    'status' => 'draft',
    'image_path' => '',
    'author' => '',
    'slug' => ''
];

try {
    $db = Database::getInstance()->getConnection();
    
    // Ако имаме ID, зареждаме съществуващ пост
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $loaded_post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($loaded_post) {
            $post = array_merge($post, $loaded_post);
            $page_title = "Редактиране на публикация";
        }
    } else {
        $page_title = "Нова публикация";
    }
    
    // Обработка на формата
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Валидация
        if (empty($_POST['title_bg']) || empty($_POST['content_bg'])) {
            $error_message = "Моля попълнете заглавие и съдържание на български език.";
        } else {
            // Подготовка на данните
            $data = [
                'title_bg' => $_POST['title_bg'],
                'title_de' => $_POST['title_de'],
                'title_ru' => $_POST['title_ru'],
                'title_en' => $_POST['title_en'],
                'content_bg' => $_POST['content_bg'],
                'content_de' => $_POST['content_de'],
                'content_ru' => $_POST['content_ru'],
                'content_en' => $_POST['content_en'],
                'category' => $_POST['category'],
                'status' => $_POST['status'],
                'author' => $_POST['author'],
                'slug' => empty($_POST['slug']) ? 
                    strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title_en']))) : 
                    $_POST['slug']
            ];
            
            // Обработка на изображението
            if (!empty($_FILES['image']['name'])) {
                $upload_dir = '../uploads/blog/';
                $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (!in_array($file_ext, $allowed_types)) {
                    $error_message = "Невалиден формат на изображението. Разрешени формати: " . implode(', ', $allowed_types);
                } else {
                    $new_filename = uniqid() . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        $data['image_path'] = $new_filename;
                        
                        // Изтриване на старото изображение
                        if (!empty($post['image_path']) && file_exists($upload_dir . $post['image_path'])) {
                            unlink($upload_dir . $post['image_path']);
                        }
                    }
                }
            }
            
            if (empty($error_message)) {
                if ($post['id']) {
                    // Обновяване
                    $sql = "UPDATE blog_posts SET 
                            title_bg = :title_bg, title_de = :title_de, title_ru = :title_ru, title_en = :title_en,
                            content_bg = :content_bg, content_de = :content_de, content_ru = :content_ru, content_en = :content_en,
                            category = :category, status = :status, author = :author, slug = :slug";
                    
                    if (isset($data['image_path'])) {
                        $sql .= ", image_path = :image_path";
                    }
                    
                    $sql .= " WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':id', $post['id']);
                    
                } else {
                    // Създаване
                    $sql = "INSERT INTO blog_posts 
                            (title_bg, title_de, title_ru, title_en, content_bg, content_de, content_ru, content_en,
                             category, status, author, slug" . (isset($data['image_path']) ? ", image_path" : "") . ") 
                            VALUES 
                            (:title_bg, :title_de, :title_ru, :title_en, :content_bg, :content_de, :content_ru, :content_en,
                             :category, :status, :author, :slug" . (isset($data['image_path']) ? ", :image_path" : "") . ")";
                    $stmt = $db->prepare($sql);
                }
                
                // Изпълнение на заявката
                foreach ($data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Публикацията беше " . ($post['id'] ? "обновена" : "създадена") . " успешно.";
                    if (!$post['id']) {
                        // Пренасочване към редакция на новосъздадения пост
                        $post['id'] = $db->lastInsertId();
                        header("Location: blog-edit.php?id=" . $post['id'] . "&success=1");
                        exit;
                    }
                } else {
                    $error_message = "Възникна грешка при запазване на публикацията.";
                }
            }
        }
    }
    
} catch (PDOException $e) {
    error_log("Blog edit error: " . $e->getMessage());
    $error_message = "Възникна грешка при обработката на заявката.";
}

// Категории
$categories = [
    'industry_articles' => 'Статии за индустриални имоти',
    'sector_news' => 'Новини от сектора',
    'investor_tips' => 'Съвети за инвеститори'
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0"><?php echo $page_title; ?></h2>
        <a href="blog.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад
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
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Tabs за езици -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#bg">
                                    Български
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#de">
                                    Deutsch
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ru">
                                    Русский
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#en">
                                    English
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Съдържание на табовете -->
                        <div class="tab-content">
                            <!-- Български -->
                            <div class="tab-pane fade show active" id="bg">
                                <div class="mb-3">
                                    <label class="form-label">Заглавие (BG) <span class="text-danger">*</span></label>
                                    <input type="text" name="title_bg" class="form-control" required
                                           value="<?php echo htmlspecialchars($post['title_bg']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Съдържание (BG) <span class="text-danger">*</span></label>
                                    <textarea name="content_bg" class="form-control editor" rows="10" required><?php 
                                        echo htmlspecialchars($post['content_bg']); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Немски -->
                            <div class="tab-pane fade" id="de">
                                <div class="mb-3">
                                    <label class="form-label">Заглавие (DE)</label>
                                    <input type="text" name="title_de" class="form-control"
                                           value="<?php echo htmlspecialchars($post['title_de']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Съдържание (DE)</label>
                                    <textarea name="content_de" class="form-control editor" rows="10"><?php 
                                        echo htmlspecialchars($post['content_de']); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Руски -->
                            <div class="tab-pane fade" id="ru">
                                <div class="mb-3">
                                    <label class="form-label">Заглавие (RU)</label>
                                    <input type="text" name="title_ru" class="form-control"
                                           value="<?php echo htmlspecialchars($post['title_ru']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Съдържание (RU)</label>
                                    <textarea name="content_ru" class="form-control editor" rows="10"><?php 
                                        echo htmlspecialchars($post['content_ru']); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Английски -->
                            <div class="tab-pane fade" id="en">
                                <div class="mb-3">
                                    <label class="form-label">Заглавие (EN)</label>
                                    <input type="text" name="title_en" class="form-control"
                                           value="<?php echo htmlspecialchars($post['title_en']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Съдържание (EN)</label>
                                    <textarea name="content_en" class="form-control editor" rows="10"><?php 
                                        echo htmlspecialchars($post['content_en']); 
                                    ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Настройки на публикацията -->
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Категория <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Изберете категория</option>
                                        <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php 
                                            echo $post['category'] === $value ? 'selected' : ''; 
                                        ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Статус</label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>
                                            Чернова
                                        </option>
                                        <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>
                                            Публикувана
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Автор</label>
                                    <input type="text" name="author" class="form-control"
                                           value="<?php echo htmlspecialchars($post['author']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">URL Slug</label>
                                    <input type="text" name="slug" class="form-control"
                                           value="<?php echo htmlspecialchars($post['slug']); ?>"
                                           placeholder="Ще бъде генериран автоматично">
                                    <div class="form-text">
                                        Оставете празно за автоматично генериране от английското заглавие
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Изображение</label>
                                    <?php if ($post['image_path']): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/blog/<?php echo htmlspecialchars($post['image_path']); ?>" 
                                             class="img-thumbnail" alt="Current image">
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">
                                        Препоръчителен размер: 1200x630 пиксела
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-save me-2"></i>Запази
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: 'textarea.editor',
    height: 400,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
             alignleft aligncenter alignright alignjustify | \
             bullist numlist outdent indent | removeformat | help'
});
</script>

<?php require_once 'footer.php'; ?> 