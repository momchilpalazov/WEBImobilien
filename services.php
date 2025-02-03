<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/language.php';

// Определяне на текущия език
$current_language = $_SESSION['language'] ?? 'bg';

// Заглавия на различни езици
$titles = [
    'bg' => 'Нашите Услуги',
    'en' => 'Our Services',
    'de' => 'Unsere Dienstleistungen',
    'ru' => 'Наши Услуги'
];

$title = $titles[$current_language];
?>

<div class="content-container">
    <div class="container-fluid px-4">
        <h1><?php echo $title; ?></h1>
        <div class="heading-divider"></div>
        
        <div class="row g-4">
            <!-- Консултации -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/consulting.jpg" class="card-img-top" alt="<?php echo $translations['services']['consulting']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['consulting']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['consulting']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Оценка -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/valuation.jpg" class="card-img-top" alt="<?php echo $translations['services']['valuation']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['valuation']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['valuation']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Управление -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/management.jpg" class="card-img-top" alt="<?php echo $translations['services']['management']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['management']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['management']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Инвестиции -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/investment.jpg" class="card-img-top" alt="<?php echo $translations['services']['investment']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['investment']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['investment']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Юридически услуги -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/legal.jpg" class="card-img-top" alt="<?php echo $translations['services']['legal']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['legal']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['legal']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Подбор на персонал -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/recruitment.jpg" class="card-img-top" alt="<?php echo $translations['services']['recruitment']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['recruitment']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['recruitment']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Езици -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 service-card" onclick="window.location.href='/contact.php'" style="cursor: pointer;">
                    <img src="images/services/languages.jpg" class="card-img-top" alt="<?php echo $translations['services']['languages']['title']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $translations['services']['languages']['title']; ?></h5>
                        <p class="card-text"><?php echo $translations['services']['languages']['description']; ?></p>
                        <div class="mt-auto">
                            <p class="contact-text"><?php echo $translations['contact_text']; ?></p>
                            <a href="/contact.php" class="btn btn-primary"><?php echo $translations['contact_button']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

.service-card {
    transition: transform 0.3s ease-in-out;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.service-card:active {
    transform: translateY(2px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

.service-card .card-img-top {
    height: 200px;
    object-fit: cover;
}

.service-card .card-body {
    padding: 1.5rem;
}

.service-card .card-title {
    font-weight: 600;
    margin-bottom: 1rem;
}

.service-card .card-text {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.5;
}

.contact-text {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.btn-primary {
    width: 100%;
    padding: 0.5rem;
    font-weight: 500;
}
</style>

<?php require_once 'includes/footer.php'; ?> 