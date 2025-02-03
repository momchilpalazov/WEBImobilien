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

$db = Database::getInstance()->getConnection();

// Вземане на всички активни услуги
$stmt = $db->query("SELECT * FROM services WHERE active = 1 ORDER BY created_at DESC");
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['menu_services']; ?> - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="services-page">
        <!-- Hero секция -->
        <section class="page-hero">
            <div class="container">
                <h1><?php echo $lang['services_title']; ?></h1>
                <p><?php echo $lang['services_subtitle']; ?></p>
            </div>
        </section>

        <!-- Списък с услуги -->
        <section class="services-list">
            <div class="container">
                <?php foreach ($services as $service): ?>
                <div class="service-item">
                    <div class="service-content">
                        <h2><?php echo htmlspecialchars($service['title_' . $current_lang]); ?></h2>
                        <div class="service-description">
                            <?php echo $service['description_' . $current_lang]; ?>
                        </div>
                        <div class="service-features">
                            <?php
                            $features = json_decode($service['features_' . $current_lang], true);
                            if ($features): ?>
                            <ul>
                                <?php foreach ($features as $feature): ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($service['image']): ?>
                    <div class="service-image">
                        <img src="uploads/services/<?php echo htmlspecialchars($service['image']); ?>" 
                             alt="<?php echo htmlspecialchars($service['title_' . $current_lang]); ?>">
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Форма за запитване -->
        <section class="contact-section">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info">
                        <h2><?php echo $lang['contact_us']; ?></h2>
                        <p><?php echo $lang['contact_services_text']; ?></p>
                        <ul class="contact-details">
                            <li>
                                <i class="icon-phone"></i>
                                <span>+359 888 123 456</span>
                            </li>
                            <li>
                                <i class="icon-email"></i>
                                <span>info@industrial-properties.bg</span>
                            </li>
                            <li>
                                <i class="icon-location"></i>
                                <span>София, България</span>
                            </li>
                        </ul>
                    </div>

                    <div class="contact-form-wrapper">
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
                                <label for="service">Услуга</label>
                                <select name="service_id" id="service">
                                    <option value="">Изберете услуга</option>
                                    <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>">
                                        <?php echo htmlspecialchars($service['title_' . $current_lang]); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
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
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html> 