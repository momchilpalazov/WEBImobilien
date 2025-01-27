<?php
session_start();

// Проверка за достъп
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../src/Database.php';
require_once '../includes/seo_functions.php';

use App\Database;

$current_language = $_SESSION['language'] ?? 'bg';

// Дефолтни SEO настройки за всички езици
$default_seo_settings = [
    'bg' => [
        'title' => 'Индустриални имоти в България и Германия | Industrial Properties',
        'meta_description' => 'Професионални брокерски услуги за индустриални имоти в България и Германия. Продажба и отдаване под наем на складове, производствени бази и логистични центрове.',
        'meta_keywords' => 'индустриални имоти, складове, логистични центрове, производствени бази, индустриални терени, България, Германия',
        'og_title' => 'Индустриални имоти в България и Германия',
        'og_description' => 'Вашият надежден партньор в сделките с индустриални имоти. Професионално управление и консултации за индустриални проекти в България и Германия.',
        'robots_meta' => 'index, follow',
        'schema_markup' => json_encode([
            "@context" => "https://schema.org",
            "@type" => "RealEstateAgent",
            "name" => "Industrial Properties",
            "description" => "Професионални брокерски услуги за индустриални имоти в България и Германия",
            "url" => "https://" . $_SERVER['HTTP_HOST'] . "/bg",
            "logo" => "https://" . $_SERVER['HTTP_HOST'] . "/images/logo.png",
            "areaServed" => ["Bulgaria", "Germany"],
            "serviceType" => ["Industrial Property Sales", "Industrial Property Leasing"],
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "Bulgaria",
                "addressLocality" => "София"
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+359-888-888-888",
                "contactType" => "sales",
                "availableLanguage" => ["Bulgarian", "English", "German", "Russian"]
            ]
        ])
    ],
    'en' => [
        'title' => 'Industrial Properties in Bulgaria and Germany | Industrial Properties',
        'meta_description' => 'Professional brokerage services for industrial properties in Bulgaria and Germany. Sale and lease of warehouses, production facilities, and logistics centers.',
        'meta_keywords' => 'industrial properties, warehouses, logistics centers, production facilities, industrial land, Bulgaria, Germany',
        'og_title' => 'Industrial Properties in Bulgaria and Germany',
        'og_description' => 'Your reliable partner in industrial real estate. Professional management and consulting for industrial projects in Bulgaria and Germany.',
        'robots_meta' => 'index, follow',
        'schema_markup' => json_encode([
            "@context" => "https://schema.org",
            "@type" => "RealEstateAgent",
            "name" => "Industrial Properties",
            "description" => "Professional brokerage services for industrial properties in Bulgaria and Germany",
            "url" => "https://" . $_SERVER['HTTP_HOST'] . "/en",
            "logo" => "https://" . $_SERVER['HTTP_HOST'] . "/images/logo.png",
            "areaServed" => ["Bulgaria", "Germany"],
            "serviceType" => ["Industrial Property Sales", "Industrial Property Leasing"],
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "Bulgaria",
                "addressLocality" => "Sofia"
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+359-888-888-888",
                "contactType" => "sales",
                "availableLanguage" => ["Bulgarian", "English", "German", "Russian"]
            ]
        ])
    ],
    'de' => [
        'title' => 'Industrieimmobilien in Bulgarien und Deutschland | Industrial Properties',
        'meta_description' => 'Professionelle Maklerdienste für Industrieimmobilien in Bulgarien und Deutschland. Verkauf und Vermietung von Lagerhallen, Produktionsstätten und Logistikzentren.',
        'meta_keywords' => 'Industrieimmobilien, Lagerhallen, Logistikzentren, Produktionsstätten, Industriegrundstücke, Bulgarien, Deutschland',
        'og_title' => 'Industrieimmobilien in Bulgarien und Deutschland',
        'og_description' => 'Ihr zuverlässiger Partner für Industrieimmobilien. Professionelles Management und Beratung für Industrieprojekte in Bulgarien und Deutschland.',
        'robots_meta' => 'index, follow',
        'schema_markup' => json_encode([
            "@context" => "https://schema.org",
            "@type" => "RealEstateAgent",
            "name" => "Industrial Properties",
            "description" => "Professionelle Maklerdienste für Industrieimmobilien in Bulgarien und Deutschland",
            "url" => "https://" . $_SERVER['HTTP_HOST'] . "/de",
            "logo" => "https://" . $_SERVER['HTTP_HOST'] . "/images/logo.png",
            "areaServed" => ["Bulgarien", "Deutschland"],
            "serviceType" => ["Verkauf von Industrieimmobilien", "Vermietung von Industrieimmobilien"],
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "Bulgarien",
                "addressLocality" => "Sofia"
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+359-888-888-888",
                "contactType" => "sales",
                "availableLanguage" => ["Bulgarisch", "Englisch", "Deutsch", "Russisch"]
            ]
        ])
    ],
    'ru' => [
        'title' => 'Промышленная недвижимость в Болгарии и Германии | Industrial Properties',
        'meta_description' => 'Профессиональные брокерские услуги по промышленной недвижимости в Болгарии и Германии. Продажа и аренда складов, производственных помещений и логистических центров.',
        'meta_keywords' => 'промышленная недвижимость, склады, логистические центры, производственные помещения, промышленные участки, Болгария, Германия',
        'og_title' => 'Промышленная недвижимость в Болгарии и Германии',
        'og_description' => 'Ваш надежный партнер в сфере промышленной недвижимости. Профессиональное управление и консультации по промышленным проектам в Болгарии и Германии.',
        'robots_meta' => 'index, follow',
        'schema_markup' => json_encode([
            "@context" => "https://schema.org",
            "@type" => "RealEstateAgent",
            "name" => "Industrial Properties",
            "description" => "Профессиональные брокерские услуги по промышленной недвижимости в Болгарии и Германии",
            "url" => "https://" . $_SERVER['HTTP_HOST'] . "/ru",
            "logo" => "https://" . $_SERVER['HTTP_HOST'] . "/images/logo.png",
            "areaServed" => ["Болгария", "Германия"],
            "serviceType" => ["Продажа промышленной недвижимости", "Аренда промышленной недвижимости"],
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "Болгария",
                "addressLocality" => "София"
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+359-888-888-888",
                "contactType" => "sales",
                "availableLanguage" => ["Болгарский", "Английский", "Немецкий", "Русский"]
            ]
        ])
    ]
];

$db = Database::getInstance()->getConnection();

// Запазване на дефолтните настройки в базата данни
foreach ($default_seo_settings as $lang => $settings) {
    $seo_data = [
        'page_type' => 'home',
        'page_id' => null,
        'language' => $lang,
        'title' => $settings['title'],
        'meta_description' => $settings['meta_description'],
        'meta_keywords' => $settings['meta_keywords'],
        'canonical_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/' . $lang,
        'og_title' => $settings['og_title'],
        'og_description' => $settings['og_description'],
        'og_image' => 'https://' . $_SERVER['HTTP_HOST'] . '/images/og-image.jpg',
        'schema_markup' => $settings['schema_markup'],
        'robots_meta' => $settings['robots_meta']
    ];
    
    $seo_manager = new SEOManager($lang);
    try {
        $seo_manager->saveSEOMeta($seo_data);
    } catch (Exception $e) {
        error_log("Грешка при запазване на SEO настройки за език $lang: " . $e->getMessage());
    }
}

// Продължаваме с останалата част от кода
$seo_manager = new SEOManager($current_language);
$success_message = '';
$error_message = '';

$page_title = "SEO Оптимизация";
require_once 'header.php';

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_ai'])) {
        // AI генериране на метаданни
        $content = $_POST['content'] ?? '';
        $page_type = $_POST['page_type'] ?? 'property';
        
        $ai_result = $seo_manager->generateAIMetadata($content, $page_type);
        
        if ($ai_result['success']) {
            $success_message = "AI метаданните са генерирани успешно!";
            $_POST['title'] = $ai_result['title'];
            $_POST['meta_description'] = $ai_result['meta_description'];
            $_POST['meta_keywords'] = $ai_result['meta_keywords'];
        } else {
            $error_message = "Грешка при генериране на AI метаданни: " . ($ai_result['error'] ?? 'Неизвестна грешка');
        }
    } else {
        // Запазване на SEO настройки
        try {
            $data = [
                'page_type' => $_POST['page_type'] ?? '',
                'page_id' => $_POST['page_id'] ?? null,
                'language' => $_POST['language'] ?? 'bg',
                'title' => $_POST['title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'meta_keywords' => $_POST['meta_keywords'] ?? '',
                'canonical_url' => $_POST['canonical_url'] ?? '',
                'og_title' => $_POST['og_title'] ?? '',
                'og_description' => $_POST['og_description'] ?? '',
                'og_image' => $_POST['og_image'] ?? '',
                'schema_markup' => $_POST['schema_markup'] ?? '',
                'robots_meta' => $_POST['robots_meta'] ?? ''
            ];
            
            $seo_manager->saveSEOMeta($data);
            $success_message = "SEO настройките са запазени успешно!";
        } catch (Exception $e) {
            $error_message = "Грешка при запазване на SEO настройките: " . $e->getMessage();
        }
    }
}

// Зареждане на текущите SEO данни
$page_type = $_GET['page_type'] ?? 'property';
$page_id = $_GET['page_id'] ?? null;
$language = $_GET['language'] ?? $current_language;
$seo_data = $seo_manager->getSEOMeta($page_type, $page_id);

// Зареждане на списък с имоти
$properties = [];
$stmt = $db->query("SELECT id, title_bg, title_en FROM properties ORDER BY id DESC");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">SEO Оптимизация</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SEO Настройки</h3>
                </div>
                <div class="card-body">
                    <!-- Филтри -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label>Тип страница</label>
                            <select class="form-control" id="filterPageType">
                                <option value="property" <?php echo $page_type === 'property' ? 'selected' : ''; ?>>Имоти</option>
                                <option value="category" <?php echo $page_type === 'category' ? 'selected' : ''; ?>>Категории</option>
                                <option value="page" <?php echo $page_type === 'page' ? 'selected' : ''; ?>>Страници</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Език</label>
                            <select class="form-control" id="filterLanguage">
                                <option value="bg" <?php echo $language === 'bg' ? 'selected' : ''; ?>>Български</option>
                                <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="de" <?php echo $language === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                                <option value="ru" <?php echo $language === 'ru' ? 'selected' : ''; ?>>Русский</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Имот</label>
                            <select class="form-control" id="filterProperty">
                                <option value="">Всички имоти</option>
                                <?php foreach ($properties as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>" <?php echo $page_id == $prop['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prop['title_' . $language]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" id="applyFilters">
                                Приложи филтрите
                            </button>
                        </div>
                    </div>

                    <!-- SEO Форма -->
                    <form method="post" id="seoForm">
                        <input type="hidden" name="page_type" value="<?php echo htmlspecialchars($page_type); ?>">
                        <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($page_id); ?>">
                        <input type="hidden" name="language" value="<?php echo htmlspecialchars($language); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Meta Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($seo_data['title'] ?? ''); ?>">
                                    <small class="form-text text-muted">Препоръчителна дължина: 50-60 символа</small>
                                </div>

                                <div class="form-group">
                                    <label>Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($seo_data['meta_description'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Препоръчителна дължина: 150-160 символа</small>
                                </div>

                                <div class="form-group">
                                    <label>Meta Keywords</label>
                                    <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($seo_data['meta_keywords'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Canonical URL</label>
                                    <input type="url" name="canonical_url" class="form-control" value="<?php echo htmlspecialchars($seo_data['canonical_url'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Open Graph Title</label>
                                    <input type="text" name="og_title" class="form-control" value="<?php echo htmlspecialchars($seo_data['og_title'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Open Graph Description</label>
                                    <textarea name="og_description" class="form-control" rows="3"><?php echo htmlspecialchars($seo_data['og_description'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Open Graph Image URL</label>
                                    <input type="url" name="og_image" class="form-control" value="<?php echo htmlspecialchars($seo_data['og_image'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Robots Meta</label>
                                    <input type="text" name="robots_meta" class="form-control" value="<?php echo htmlspecialchars($seo_data['robots_meta'] ?? ''); ?>">
                                    <small class="form-text text-muted">Например: index, follow</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Schema.org Markup</label>
                            <textarea name="schema_markup" class="form-control" rows="5"><?php echo htmlspecialchars($seo_data['schema_markup'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">JSON-LD формат</small>
                        </div>

                        <!-- AI Оптимизация -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">AI Оптимизация</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Съдържание за анализ</label>
                                    <textarea name="content" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit" name="generate_ai" class="btn btn-secondary">
                                    Генерирай AI метаданни
                                </button>

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
                        </div>

                        <div class="text-right mt-4">
                            <button type="submit" name="save_seo" class="btn btn-primary">
                                Запази SEO настройките
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('applyFilters').addEventListener('click', function() {
    const pageType = document.getElementById('filterPageType').value;
    const language = document.getElementById('filterLanguage').value;
    const propertyId = document.getElementById('filterProperty').value;
    
    let url = 'seo.php?page_type=' + pageType + '&language=' + language;
    if (propertyId) {
        url += '&page_id=' + propertyId;
    }
    
    window.location.href = url;
});
</script>

<?php require_once 'includes/footer.php'; ?> 