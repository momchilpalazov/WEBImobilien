<?php
session_start();

require_once "../config/database.php";
use App\Database;
require_once "../includes/functions.php";

// Зареждане на езиковите файлове
$default_lang = 'bg';
$allowed_languages = ['bg', 'de', 'ru'];
$current_lang = isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages) ? $_GET['lang'] : $default_lang;

require_once "../languages/{$current_lang}.php";

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO inquiries (name, email, phone, message)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'] ?? null,
            $_POST['message']
        ]);
        
        $success = $lang['contact_success'];
    } catch (Exception $e) {
        $error = $lang['contact_error'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['menu_contact']; ?> - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="contact-page">
        <!-- Hero секция -->
        <section class="page-hero">
            <div class="container">
                <h1><?php echo $lang['menu_contact']; ?></h1>
                <p><?php echo $lang['contact_subtitle']; ?></p>
            </div>
        </section>

        <!-- Контактна информация и форма -->
        <section class="contact-main">
            <div class="container">
                <div class="contact-grid">
                    <!-- Контактна информация -->
                    <div class="contact-info">
                        <div class="info-card">
                            <h2><?php echo $lang['contact_office']; ?></h2>
                            <ul class="contact-details">
                                <li>
                                    <i class="icon-location"></i>
                                    <div>
                                        <strong>Industrial Properties Ltd.</strong><br>
                                        ул. "Примерна" 123<br>
                                        1000 София, България
                                    </div>
                                </li>
                                <li>
                                    <i class="icon-phone"></i>
                                    <div>
                                        <strong><?php echo $lang['contact_phone']; ?></strong><br>
                                        +359 888 123 456
                                    </div>
                                </li>
                                <li>
                                    <i class="icon-email"></i>
                                    <div>
                                        <strong>Email</strong><br>
                                        info@industrial-properties.bg
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="info-card">
                            <h2><?php echo $lang['contact_hours']; ?></h2>
                            <ul class="working-hours">
                                <li>
                                    <span><?php echo $lang['weekdays']; ?></span>
                                    <span>09:00 - 18:00</span>
                                </li>
                                <li>
                                    <span><?php echo $lang['saturday']; ?></span>
                                    <span>10:00 - 14:00</span>
                                </li>
                                <li>
                                    <span><?php echo $lang['sunday']; ?></span>
                                    <span><?php echo $lang['closed']; ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="social-links">
                            <h2><?php echo $lang['footer_social']; ?></h2>
                            <div class="social-icons">
                                <a href="#" class="social-icon"><i class="icon-facebook"></i></a>
                                <a href="#" class="social-icon"><i class="icon-linkedin"></i></a>
                                <a href="#" class="social-icon"><i class="icon-instagram"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Контактна форма -->
                    <div class="contact-form-wrapper">
                        <h2><?php echo $lang['contact_form_title']; ?></h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="name"><?php echo $lang['contact_name']; ?></label>
                                <input type="text" id="name" name="name" required>
                            </div>

                            <div class="form-group">
                                <label for="email"><?php echo $lang['contact_email']; ?></label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="phone"><?php echo $lang['contact_phone']; ?></label>
                                <input type="tel" id="phone" name="phone">
                            </div>

                            <div class="form-group">
                                <label for="message"><?php echo $lang['contact_message']; ?></label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?php echo $lang['contact_submit']; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Google Maps -->
        <section class="map-section">
            <div id="map"></div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <script>
        function initMap() {
            const office = { lat: 42.6977, lng: 23.3219 }; // София
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: office
            });
            new google.maps.Marker({
                position: office,
                map: map,
                title: 'Industrial Properties Ltd.'
            });
        }
        initMap();
    </script>
    <script src="js/main.js"></script>
</body>
</html> 