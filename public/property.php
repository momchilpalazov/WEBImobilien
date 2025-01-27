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

// Вземане на ID на имота
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header('Location: index.php');
    exit;
}

// Вземане на информация за имота
$property_sql = "SELECT * FROM properties WHERE id = :id AND active = 1";
$stmt = $db->prepare($property_sql);
$stmt->bindValue(':id', $property_id, PDO::PARAM_INT);
$stmt->execute();
$property = $stmt->fetch();

if (!$property) {
    header('Location: index.php');
    exit;
}

// Вземане на снимките на имота
$images_sql = "SELECT * FROM property_images WHERE property_id = :property_id ORDER BY is_main DESC";
$stmt = $db->prepare($images_sql);
$stmt->bindValue(':property_id', $property_id, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll();

// Вземане на документите на имота
$documents_sql = "SELECT * FROM property_documents WHERE property_id = :property_id";
$stmt = $db->prepare($documents_sql);
$stmt->bindValue(':property_id', $property_id, PDO::PARAM_INT);
$stmt->execute();
$documents = $stmt->fetchAll();

// Вземане на подобни имоти
$similar_sql = "SELECT p.*, 
    (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
    FROM properties p 
    WHERE p.type = :type 
    AND p.id != :id 
    AND p.active = 1 
    LIMIT 3";
$stmt = $db->prepare($similar_sql);
$stmt->bindValue(':type', $property['type'], PDO::PARAM_STR);
$stmt->bindValue(':id', $property_id, PDO::PARAM_INT);
$stmt->execute();
$similar_properties = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title_' . $current_lang]); ?> - <?php echo $lang['site_title']; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($property['description_' . $current_lang]); ?>">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="property-page">
        <!-- Галерия -->
        <section class="property-gallery">
            <div class="swiper main-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $image): ?>
                    <div class="swiper-slide">
                        <img src="uploads/properties/<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title_' . $current_lang]); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <div class="swiper thumbs-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $image): ?>
                    <div class="swiper-slide">
                        <img src="uploads/properties/thumbnails/<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="Thumbnail">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Основна информация -->
        <section class="property-main">
            <div class="container">
                <div class="property-header">
                    <div class="property-title">
                        <h1><?php echo htmlspecialchars($property['title_' . $current_lang]); ?></h1>
                        <div class="property-meta">
                            <span class="property-id">ID: <?php echo $property['id']; ?></span>
                            <span class="property-type"><?php echo $lang[$property['type']]; ?></span>
                            <span class="property-status status-<?php echo $property['status']; ?>">
                                <?php echo $lang[$property['status']]; ?>
                            </span>
                        </div>
                    </div>
                    <div class="property-price">
                        <span class="price-value"><?php echo number_format($property['price'], 0, ',', ' '); ?> €</span>
                        <?php if ($property['price_per_sqm']): ?>
                        <span class="price-per-sqm">
                            <?php echo number_format($property['price_per_sqm'], 0, ',', ' '); ?> €/m²
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="property-actions">
                    <button class="btn btn-outline save-property" data-id="<?php echo $property['id']; ?>">
                        <i class="icon-heart"></i>
                        <?php echo $lang['save_property']; ?>
                    </button>
                    <button class="btn btn-outline share-property">
                        <i class="icon-share"></i>
                        <?php echo $lang['share_property']; ?>
                    </button>
                    <button class="btn btn-outline print-property">
                        <i class="icon-print"></i>
                        <?php echo $lang['print_details']; ?>
                    </button>
                </div>

                <div class="property-content">
                    <div class="property-main-info">
                        <!-- Описание -->
                        <div class="property-section">
                            <h2><?php echo $lang['property_description']; ?></h2>
                            <div class="property-description">
                                <?php echo nl2br(htmlspecialchars($property['description_' . $current_lang])); ?>
                            </div>
                        </div>

                        <!-- Характеристики -->
                        <div class="property-section">
                            <h2><?php echo $lang['property_features']; ?></h2>
                            <div class="features-grid">
                                <?php if ($property['built_year']): ?>
                                <div class="feature-item">
                                    <span class="feature-label"><?php echo $lang['built_year']; ?></span>
                                    <span class="feature-value"><?php echo $property['built_year']; ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($property['last_renovation']): ?>
                                <div class="feature-item">
                                    <span class="feature-label"><?php echo $lang['last_renovation']; ?></span>
                                    <span class="feature-value"><?php echo $property['last_renovation']; ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Добавете останалите характеристики тук -->
                            </div>
                        </div>

                        <!-- Документи -->
                        <?php if ($documents): ?>
                        <div class="property-section">
                            <h2><?php echo $lang['property_documents']; ?></h2>
                            <div class="documents-list">
                                <?php foreach ($documents as $document): ?>
                                <a href="uploads/documents/<?php echo htmlspecialchars($document['file_path']); ?>" 
                                   class="document-item" 
                                   target="_blank">
                                    <i class="icon-document"></i>
                                    <span><?php echo htmlspecialchars($document['title_' . $current_lang]); ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Местоположение -->
                        <div class="property-section">
                            <h2><?php echo $lang['property_map']; ?></h2>
                            <div id="property-map"></div>
                        </div>
                    </div>

                    <!-- Странична информация -->
                    <aside class="property-sidebar">
                        <!-- Форма за контакт -->
                        <div class="contact-form-widget">
                            <h3><?php echo $lang['property_contact']; ?></h3>
                            <form class="contact-form" method="POST" action="send-inquiry.php">
                                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                
                                <div class="form-group">
                                    <input type="text" name="name" required 
                                           placeholder="<?php echo $lang['contact_name']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input type="email" name="email" required 
                                           placeholder="<?php echo $lang['contact_email']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input type="tel" name="phone" 
                                           placeholder="<?php echo $lang['contact_phone']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <textarea name="message" required 
                                              placeholder="<?php echo $lang['contact_message']; ?>"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $lang['contact_submit']; ?>
                                </button>
                            </form>
                        </div>

                        <!-- Виртуална обиколка -->
                        <?php if ($property['virtual_tour_url']): ?>
                        <div class="virtual-tour-widget">
                            <h3><?php echo $lang['virtual_tour']; ?></h3>
                            <a href="<?php echo htmlspecialchars($property['virtual_tour_url']); ?>" 
                               class="btn btn-outline" 
                               target="_blank">
                                <i class="icon-360"></i>
                                <?php echo $lang['virtual_tour']; ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Презентация -->
                        <?php if ($property['presentation_file']): ?>
                        <div class="presentation-widget">
                            <h3><?php echo $lang['download_presentation']; ?></h3>
                            <a href="uploads/presentations/<?php echo htmlspecialchars($property['presentation_file']); ?>" 
                               class="btn btn-outline" 
                               target="_blank">
                                <i class="icon-download"></i>
                                <?php echo $lang['download_presentation']; ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </section>

        <!-- Подобни имоти -->
        <?php if ($similar_properties): ?>
        <section class="similar-properties">
            <div class="container">
                <h2><?php echo $lang['similar_properties']; ?></h2>
                <div class="properties-grid">
                    <?php foreach ($similar_properties as $similar): ?>
                    <div class="property-card">
                        <?php if ($similar['main_image']): ?>
                        <div class="property-image">
                            <img src="uploads/properties/<?php echo htmlspecialchars($similar['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($similar['title_' . $current_lang]); ?>">
                            <span class="property-status status-<?php echo $similar['status']; ?>">
                                <?php echo $lang[$similar['status']]; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="property-content">
                            <h3><?php echo htmlspecialchars($similar['title_' . $current_lang]); ?></h3>
                            <div class="property-details">
                                <span class="price">
                                    <?php echo number_format($similar['price'], 0, ',', ' '); ?> €
                                </span>
                                <span class="area"><?php echo $similar['area']; ?> m²</span>
                            </div>
                            <a href="property.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline">
                                <?php echo $lang['view_more']; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <script src="js/main.js"></script>
</body>
</html> 