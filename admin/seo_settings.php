<?php
require_once '../includes/admin_header.php';
require_once '../includes/seo_functions.php';

// Инициализация на SEO мениджъра
$seo_manager = new SEOManager($current_language);

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seo_data = [
        'page_type' => $_POST['page_type'],
        'page_id' => $_POST['page_id'] ?: null,
        'language' => $_POST['language'],
        'title' => $_POST['title'],
        'meta_description' => $_POST['meta_description'],
        'meta_keywords' => $_POST['meta_keywords'],
        'canonical_url' => $_POST['canonical_url'],
        'og_title' => $_POST['og_title'],
        'og_description' => $_POST['og_description'],
        'og_image' => $_POST['og_image'],
        'schema_markup' => $_POST['schema_markup'],
        'robots_meta' => $_POST['robots_meta']
    ];

    if ($seo_manager->saveSEOMeta($seo_data)) {
        $success_message = "SEO настройките са запазени успешно!";
    } else {
        $error_message = "Възникна грешка при запазване на SEO настройките.";
    }
}

// Зареждане на текущите SEO данни
$page_type = $_GET['page_type'] ?? 'property';
$page_id = $_GET['page_id'] ?? null;
$seo_data = $seo_manager->getSEOMeta($page_type, $page_id);

// Генериране на AI метаданни ако е поискано
if (isset($_POST['generate_ai'])) {
    $content = $_POST['content'];
    $ai_metadata = $seo_manager->generateAIMetadata($content);
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">SEO Настройки</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Тип страница</label>
                                    <select name="page_type" class="form-select" required>
                                        <option value="property" <?php echo $page_type === 'property' ? 'selected' : ''; ?>>Имот</option>
                                        <option value="category" <?php echo $page_type === 'category' ? 'selected' : ''; ?>>Категория</option>
                                        <option value="page" <?php echo $page_type === 'page' ? 'selected' : ''; ?>>Страница</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ID на страницата (незадължително)</label>
                                    <input type="number" name="page_id" class="form-control" value="<?php echo htmlspecialchars($page_id ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Език</label>
                                    <select name="language" class="form-select" required>
                                        <option value="bg" <?php echo $current_language === 'bg' ? 'selected' : ''; ?>>Български</option>
                                        <option value="en" <?php echo $current_language === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="de" <?php echo $current_language === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                                        <option value="ru" <?php echo $current_language === 'ru' ? 'selected' : ''; ?>>Русский</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($seo_data['title'] ?? ''); ?>" required>
                                    <div class="form-text">Препоръчителна дължина: 50-60 символа</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3" required><?php echo htmlspecialchars($seo_data['meta_description'] ?? ''); ?></textarea>
                                    <div class="form-text">Препоръчителна дължина: 150-160 символа</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($seo_data['meta_keywords'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Canonical URL</label>
                                    <input type="url" name="canonical_url" class="form-control" value="<?php echo htmlspecialchars($seo_data['canonical_url'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Open Graph Title</label>
                                    <input type="text" name="og_title" class="form-control" value="<?php echo htmlspecialchars($seo_data['og_title'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Open Graph Description</label>
                                    <textarea name="og_description" class="form-control" rows="3"><?php echo htmlspecialchars($seo_data['og_description'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Open Graph Image URL</label>
                                    <input type="url" name="og_image" class="form-control" value="<?php echo htmlspecialchars($seo_data['og_image'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Schema.org Markup</label>
                                    <textarea name="schema_markup" class="form-control" rows="5"><?php echo htmlspecialchars($seo_data['schema_markup'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Robots Meta</label>
                                    <input type="text" name="robots_meta" class="form-control" value="<?php echo htmlspecialchars($seo_data['robots_meta'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <h6>AI Оптимизация</h6>
                            <div class="mb-3">
                                <label class="form-label">Съдържание за анализ</label>
                                <textarea name="content" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="generate_ai" class="btn btn-secondary">Генерирай AI метаданни</button>
                            
                            <?php if (isset($ai_metadata)): ?>
                            <div class="mt-3">
                                <h6>AI Генерирани метаданни:</h6>
                                <div class="mb-2">
                                    <strong>Описание:</strong>
                                    <p><?php echo htmlspecialchars($ai_metadata['ai_description']); ?></p>
                                </div>
                                <div class="mb-2">
                                    <strong>Ключови думи:</strong>
                                    <p><?php echo htmlspecialchars($ai_metadata['ai_keywords']); ?></p>
                                </div>
                                <div class="mb-2">
                                    <strong>Извлечени единици:</strong>
                                    <p><?php echo htmlspecialchars(implode(', ', $ai_metadata['ai_entities'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Запази SEO настройките</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 