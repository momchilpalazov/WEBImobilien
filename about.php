<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/about_translations.php';

// Вземаме текущия език
$current_lang = $_SESSION['language'] ?? 'bg';
$about_translations = $about_translations[$current_lang];

// Логване на текущия език и преводите за дебъг
error_log("Current language in about.php: " . $current_lang);
error_log("Available translations: " . print_r(array_keys($about_translations), true));
error_log("Current translations: " . print_r($about_translations, true));
?>

<div class="content-container">
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-lg-12">
                <h1><?php echo $about_translations['title']; ?></h1>
                <div class="heading-divider"></div>
                
                <div class="about-content fade-in">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <h2><?php echo $about_translations['mission_title']; ?></h2>
                            <p><?php echo $about_translations['mission_text']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <img src="images/about/mission.jpg" alt="<?php echo $about_translations['mission_title']; ?>" class="img-fluid img-feature">
                        </div>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="col-md-6 order-md-2">
                            <h2><?php echo $about_translations['why_us_title']; ?></h2>
                            <p><?php echo $about_translations['why_us_text']; ?></p>
                            <ul class="custom-list">
                                <li><?php echo $about_translations['service_1']['title']; ?></li>
                                <li><?php echo $about_translations['service_2']['title']; ?></li>
                                <li><?php echo $about_translations['service_3']['title']; ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6 order-md-1">
                            <img src="images/about/experience.jpg" alt="<?php echo $about_translations['why_us_title']; ?>" class="img-fluid img-feature">
                        </div>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <h2><?php echo $about_translations['services_title']; ?></h2>
                            <div class="row mt-4">
                                <!-- Услуга 1 -->
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $about_translations['service_1']['title']; ?></h5>
                                            <p class="card-text"><?php echo $about_translations['service_1']['text']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Услуга 2 -->
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $about_translations['service_2']['title']; ?></h5>
                                            <p class="card-text"><?php echo $about_translations['service_2']['text']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Услуга 3 -->
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $about_translations['service_3']['title']; ?></h5>
                                            <p class="card-text"><?php echo $about_translations['service_3']['text']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h2><?php echo $about_translations['contact_title']; ?></h2>
                            <p><?php echo $about_translations['contact_text']; ?></p>
                            <a href="contact.php" class="btn btn-primary">
                                <?php echo $about_translations['contact_button']; ?>
                                <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <style>
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

.about-content {
    padding: 30px 0;
}

/* .heading-divider {
    height: 3px;
    width: 60px;
    background-color: #3498db;
    margin: 20px 0 30px;
} */

.img-feature {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.custom-list {
    list-style: none;
    padding-left: 0;
}

.custom-list li {
    padding: 8px 0;
    position: relative;
    padding-left: 25px;
}

.custom-list li:before {
    content: "✓";
    color: #3498db;
    position: absolute;
    left: 0;
}

/* .btn-primary {
    background-color: #3498db;
    border: none;
    padding: 12px 30px;
} */

.btn-primary:hover {
    background-color: #2980b9;
}

.fade-in {
    animation: fadeIn 1s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style> -->

<?php require_once 'includes/footer.php'; ?> 
