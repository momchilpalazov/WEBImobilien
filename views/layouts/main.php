<?php
/**
 * Main layout file
 * @var string $content
 */

$translations = translations();
$currentLanguage = currentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLanguage); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(__('menu.properties')); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-light container">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <?php echo htmlspecialchars(__('footer.company_name')); ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <?php
                        $menuItems = ['home', 'properties', 'services', 'about', 'contact', 'blog'];
                        foreach ($menuItems as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/<?php echo $item === 'home' ? '' : $item; ?>">
                                    <?php echo htmlspecialchars(__('menu.' . $item)); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <!-- Language Selector -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">
                            <?php echo strtoupper($currentLanguage); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach (['bg', 'en', 'de', 'ru'] as $lang): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $lang === $currentLanguage ? 'active' : ''; ?>" 
                                       href="?language=<?php echo $lang; ?>">
                                        <?php echo strtoupper($lang); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container py-4">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo htmlspecialchars(__('footer.company_name')); ?></h5>
                    <p><?php echo htmlspecialchars(__('footer.description')); ?></p>
                </div>
                <div class="col-md-4">
                    <h5><?php echo htmlspecialchars(__('footer.quick_links')); ?></h5>
                    <ul class="list-unstyled">
                        <?php foreach ($menuItems as $item): ?>
                            <li>
                                <a href="/<?php echo $item === 'home' ? '' : $item; ?>" class="text-light">
                                    <?php echo htmlspecialchars(__('menu.' . $item)); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5><?php echo htmlspecialchars(__('contact.title')); ?></h5>
                    <address>
                        <?php echo htmlspecialchars(__('contact.address')); ?><br>
                        <?php echo htmlspecialchars(__('contact.phone')); ?>: 
                        <a href="tel:<?php echo __('contact.phone_number'); ?>" class="text-light">
                            <?php echo htmlspecialchars(__('contact.phone_number')); ?>
                        </a><br>
                        <?php echo htmlspecialchars(__('contact.email')); ?>: 
                        <a href="mailto:<?php echo __('contact.email_address'); ?>" class="text-light">
                            <?php echo htmlspecialchars(__('contact.email_address')); ?>
                        </a>
                    </address>
                    <div class="mt-3">
                        <h6><?php echo htmlspecialchars(__('footer.social_media')); ?></h6>
                        <div class="social-links">
                            <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-light"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(__('footer.company_name')); ?>.
                    <?php echo htmlspecialchars(__('footer.all_rights_reserved')); ?>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/js/main.js"></script>
</body>
</html> 