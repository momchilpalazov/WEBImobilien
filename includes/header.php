<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/language.php';
require_once 'includes/seo_functions.php';

$current_language = getCurrentLanguage();
$translations = require_once __DIR__ . "/../languages/{$current_language}.php";

// Get current page for active menu item
$current_page = basename($_SERVER['PHP_SELF']);

$seo_manager = new SEOManager($current_language);

// Определяне на типа страница и ID
$page_type = isset($property) ? 'property' : (isset($category) ? 'category' : 'page');
$page_id = isset($property) ? $property['id'] : (isset($category) ? $category['id'] : null);

// Зареждане на SEO мета данни
$seo_meta = $seo_manager->getSEOMeta($page_type, $page_id);

// Генериране на Schema.org markup за имоти
$schema_markup = isset($property) ? $seo_manager->generatePropertySchema($property) : null;

// Определяне на класа за текущата страница
$page_class = '';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
switch ($current_page) {
    case 'contact':
        $page_class = 'contact-page';
        break;
    case 'about':
        $page_class = 'about-page';
        break;
    case 'blog':
        $page_class = 'blog-page';
        break;
    case 'properties':
        $page_class = 'properties-page';
        break;
    case 'services':
        $page_class = 'services-page';
        break;
    default:
        $page_class = 'home-page';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_meta['title'] ?? $page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_meta['meta_description'] ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_meta['meta_keywords'] ?? ''); ?>">
    <?php if (!empty($seo_meta['robots_meta'])): ?>
    <meta name="robots" content="<?php echo htmlspecialchars($seo_meta['robots_meta']); ?>">
    <?php endif; ?>
    
    <!-- Canonical URL -->
    <?php if (!empty($seo_meta['canonical_url'])): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_meta['canonical_url']); ?>">
    <?php endif; ?>
    
    <!-- Alternate Language URLs -->
    <?php echo $seo_manager->generateHrefLangTags($page_type, $page_id); ?>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($seo_meta['og_title'] ?? $seo_meta['title'] ?? $page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_meta['og_description'] ?? $seo_meta['meta_description'] ?? ''); ?>">
    <?php if (!empty($seo_meta['og_image'])): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($seo_meta['og_image']); ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?php echo str_replace('_', '-', $current_language); ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($seo_meta['og_title'] ?? $seo_meta['title'] ?? $page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_meta['og_description'] ?? $seo_meta['meta_description'] ?? ''); ?>">
    <?php if (!empty($seo_meta['og_image'])): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($seo_meta['og_image']); ?>">
    <?php endif; ?>
    
    <!-- Schema.org Markup -->
    <?php if (!empty($schema_markup)): ?>
    <script type="application/ld+json">
        <?php echo $schema_markup; ?>
    </script>
    <?php endif; ?>
    
    <!-- Други мета тагове -->
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="<?php echo $page_class; ?>">
    <!-- Header -->
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid px-4">
                <a class="navbar-brand" href="/">
                    <img src="/images/logo.svg" alt="Industrial Properties" height="45">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" 
                               href="/index.php"><?php echo $translations['menu']['home']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'properties.php' ? 'active' : ''; ?>" 
                               href="/properties.php"><?php echo $translations['menu']['properties']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" 
                               href="/services.php"><?php echo $translations['menu']['services']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" 
                               href="/about.php"><?php echo $translations['menu']['about']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" 
                               href="/contact.php"><?php echo $translations['menu']['contact']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'active' : ''; ?>" 
                               href="/blog.php">
                                <?php echo $translations['menu']['blog']; ?>
                            </a>
                        </li>
                        <!-- Language Switcher -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                                <img src="/images/flags/<?php echo $current_language; ?>.svg" alt="<?php echo strtoupper($current_language); ?>" width="24" class="me-2">
                                <span class="d-none d-lg-inline"><?php echo strtoupper($current_language); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php
                                // Вземаме текущите GET параметри
                                $params = $_GET;
                                
                                // Функция за генериране на URL с параметри
                                function buildUrl($lang) {
                                    global $params;
                                    $params['lang'] = $lang;
                                    return '?' . http_build_query($params);
                                }
                                ?>
                                <li><a class="dropdown-item d-flex align-items-center" href="<?php echo buildUrl('bg'); ?>">
                                    <img src="/images/flags/bg.svg" alt="BG" width="24" class="me-2"> Български</a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="<?php echo buildUrl('de'); ?>">
                                    <img src="/images/flags/de.svg" alt="DE" width="24" class="me-2"> Deutsch</a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="<?php echo buildUrl('ru'); ?>">
                                    <img src="/images/flags/ru.svg" alt="RU" width="24" class="me-2"> Русский</a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="<?php echo buildUrl('en'); ?>">
                                    <img src="/images/flags/en.svg" alt="EN" width="24" class="me-2"> English</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main>

<style>
.site-header {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar {
    padding: 1rem 0;
}

.navbar-brand img {
    transition: transform 0.3s ease;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

.nav-link {
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    color: #333;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    color: #007bff;
}

.nav-link.active {
    color: #007bff;
}

.nav-link.active:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 2px;
    background: #007bff;
    border-radius: 2px;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 0.5rem;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

@media (max-width: 991.98px) {
    .navbar-collapse {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-top: 1rem;
    }
    
    .nav-link.active:after {
        display: none;
    }
    
    .nav-link.active {
        background: #f8f9fa;
        border-radius: 6px;
    }
}
</style> 