<?php
require_once 'src/Database.php';
require_once 'config/database.php';
require_once 'includes/header.php';

use App\Database;

// Вземане на ID на имота
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header('Location: index.php');
    exit;
}

// Вземане на данните за имота
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT * FROM properties 
    WHERE id = :id
");
$stmt->execute([':id' => $property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: index.php');
    exit;
}

// Вземане на снимките на имота
$stmt = $db->prepare("
    SELECT * FROM property_images 
    WHERE property_id = :property_id 
    ORDER BY is_main DESC
");
$stmt->execute([':property_id' => $property_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Вземане на основната снимка
$main_image = array_filter($images, function($img) {
    return $img['is_main'] == 1;
});
$main_image = reset($main_image) ?: reset($images);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $property['title_' . $current_language]; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
    <style>
    /* Стилове за принтиране */
    @media print {
        /* Скриваме елементите, които не трябва да се принтират */
        header, 
        footer,
        .site-header,
        .site-footer,
        .btn,
        .inquiry-form,
        .print-button {
            display: none !important;
        }
        
        /* Стилове за принтируемото съдържание */
        .printable-content {
            display: block !important;
        }
        
        /* Премахваме сенките и други визуални ефекти */
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Оправяме размерите на снимките */
        img {
            max-width: 100% !important;
            height: auto !important;
        }
        
        /* Оправяме цветовете за по-добро принтиране */
        body {
            color: #000 !important;
            background: #fff !important;
        }
        
        /* Показваме пълните URL адреси на линковете */
        a[href]:after {
            content: " (" attr(href) ")";
        }
    }
    .property-gallery {
        position: relative;
        overflow: hidden;
    }
    .gallery-thumbnail {
        cursor: pointer;
        transition: opacity 0.3s ease;
        height: 200px; /* Фиксирана височина за миниатюрите */
        object-fit: cover; /* Запазва пропорциите и запълва пространството */
        width: 100%;
    }
    .gallery-thumbnail:hover {
        opacity: 0.9;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 15px;
    }
    .gallery-item {
        aspect-ratio: 4/3;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
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
    </style>
</head>
<body>
    <main class="flex-grow-1">
        <div class="container-xxl py-5">
            <div class="row g-5">
                <!-- Заглавие -->
                <div class="col-12">
                    <h2><?php echo htmlspecialchars($property['title_' . $current_language]); ?></h2>
                    <div class="heading-divider"></div>
                </div>
                <!-- Галерия със снимки -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <?php if ($main_image): ?>
                            <a href="uploads/properties/<?php echo $main_image['image_path']; ?>" 
                               data-fancybox="property-gallery"
                               data-caption="<?php echo htmlspecialchars($property['title_' . $current_language]); ?>">
                                <img src="uploads/properties/<?php echo $main_image['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($property['title_' . $current_language]); ?>"
                                     class="img-fluid w-100 main-image">
                            </a>
                            <?php endif; ?>
                            
                            <?php if (count($images) > 1): ?>
                            <div class="gallery-grid">
                                <?php foreach ($images as $index => $image): ?>
                                <?php if ($index === 0) continue; // Пропускаме главната снимка ?>
                                <div class="gallery-item">
                                    <a href="uploads/properties/<?php echo $image['image_path']; ?>"
                                       data-fancybox="property-gallery"
                                       data-caption="<?php echo htmlspecialchars($property['title_' . $current_language]); ?>">
                                        <img src="uploads/properties/<?php echo $image['image_path']; ?>" 
                                             alt="<?php echo htmlspecialchars($property['title_' . $current_language]); ?>"
                                             class="gallery-thumbnail">
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Информация за имота -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h1 class="h2 mb-0"><?php echo htmlspecialchars($property['title_' . $current_language]); ?></h1>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="printProperty()" title="<?php echo $translations['property']['print']; ?>">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="shareProperty()" title="<?php echo $translations['property']['share']; ?>">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="printable-content">
                                <div class="row g-4">
                                    <div class="col-sm-6 col-lg-3 mb-3">
                                        <div class="property-feature d-flex align-items-center">
                                            <i class="bi bi-rulers fs-4 text-primary me-2"></i>
                                            <div class="feature-content">
                                                <small class="text-muted d-block"><?php echo $translations['property']['area']; ?></small>
                                                <strong><?php echo number_format($property['area'], 0, '.', ' '); ?> м²</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-3 mb-3">
                                        <div class="property-feature d-flex align-items-center">
                                            <i class="bi bi-geo-alt fs-4 text-primary me-2"></i>
                                            <div class="feature-content">
                                                <small class="text-muted d-block"><?php echo $translations['property']['location']; ?></small>
                                                <strong><?php echo htmlspecialchars($property['location_' . $current_language]); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-3 mb-3">
                                        <div class="property-feature d-flex align-items-center">
                                            <i class="bi bi-building fs-4 text-primary me-2"></i>
                                            <div class="feature-content">
                                                <small class="text-muted d-block"><?php echo $translations['property']['filter']['type']; ?></small>
                                                <strong><?php echo $translations['property']['type'][$property['type']]; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-3 mb-3">
                                        <div class="property-feature d-flex align-items-center">
                                            <i class="bi bi-currency-euro fs-4 text-primary me-2"></i>
                                            <div class="feature-content">
                                                <small class="text-muted d-block"><?php echo $translations['property']['price']; ?></small>
                                                <strong><?php echo number_format($property['price'], 0, '.', ' '); ?> €</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h5 class="mb-3"><?php echo $translations['property']['description']; ?></h5>
                                    <div class="property-description">
                                        <?php echo $property['description_' . $current_language]; ?>
                                    </div>
                                </div>

                                <!-- Технически детайли -->
                                <div class="mb-4">
                                    <h5 class="mb-3"><?php echo $translations['property']['technical_details']['title']; ?></h5>
                                    <div class="row g-4">
                                        <?php if ($property['built_year']): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                                <div>
                                                    <small class="text-muted d-block"><?php echo $translations['property']['features_list']['built_year']; ?></small>
                                                    <strong><?php echo $property['built_year']; ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($property['ceiling_height']): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-arrows-expand me-2 text-primary"></i>
                                                <div>
                                                    <small class="text-muted d-block"><?php echo $translations['property']['technical_details']['ceiling_height']; ?></small>
                                                    <strong><?php printf($translations['property']['features_list']['ceiling_height_value'], $property['ceiling_height']); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($property['loading_docks']): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-truck me-2 text-primary"></i>
                                                <div>
                                                    <small class="text-muted d-block"><?php echo $translations['property']['technical_details']['loading_docks']; ?></small>
                                                    <strong><?php printf($translations['property']['features_list']['loading_docks_value'], $property['loading_docks']); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($property['parking_spots']): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-p-square me-2 text-primary"></i>
                                                <div>
                                                    <small class="text-muted d-block"><?php echo $translations['property']['technical_details']['parking']; ?></small>
                                                    <strong><?php printf($translations['property']['features_list']['parking_spots_value'], $property['parking_spots']); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Карта с локацията -->
                                <div class="mb-4">
                                    <h5 class="mb-3"><?php echo $translations['property']['location']; ?></h5>
                                    <div id="propertyMap" style="height: 400px; width: 100%; border-radius: 8px; position: relative;"></div>
                                </div>

                                <!-- Here Maps API -->
                                <link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.1/mapsjs-ui.css" />
                                <script src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
                                <script src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
                                <script src="https://js.api.here.com/v3/3.1/mapsjs-ui.js"></script>
                                <script src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>
                                
                                <script>
                                function initMap() {
                                    // Инициализация на Here Maps платформата
                                    const platform = new H.service.Platform({
                                        'apikey': 'lpQVOFFyys9adQhUqk5e6VQ_WBqcsKv4DdfTZTIipTs'
                                    });

                                    // Настройки по подразбиране за картата
                                    const defaultLayers = platform.createDefaultLayers();

                                    // Създаване на картата
                                    const map = new H.Map(
                                        document.getElementById('propertyMap'),
                                        defaultLayers.vector.normal.map,
                                        {
                                            zoom: 15,
                                            pixelRatio: window.devicePixelRatio || 1
                                        }
                                    );

                                    // Добавяне на контроли за взаимодействие
                                    const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
                                    const ui = H.ui.UI.createDefault(map, defaultLayers);

                                    // Geocoding service
                                    const geocodingService = platform.getSearchService();

                                    // Адрес на имота
                                    const propertyAddress = '<?php echo addslashes($property['location_' . $current_language]); ?>';

                                    // Търсене на координатите по адрес
                                    geocodingService.geocode({
                                        q: propertyAddress
                                    }, (result) => {
                                        if (result.items.length > 0) {
                                            const coordinates = result.items[0].position;
                                            
                                            // Центриране на картата
                                            map.setCenter({lat: coordinates.lat, lng: coordinates.lng});

                                            // Създаване на маркер
                                            const marker = new H.map.Marker({lat: coordinates.lat, lng: coordinates.lng});
                                            
                                            // Добавяне на информационен прозорец
                                            const bubbleContent = new H.ui.InfoBubble(
                                                {lat: coordinates.lat, lng: coordinates.lng},
                                                {
                                                    content: '<div style="padding: 10px;">' +
                                                            '<h6 style="margin: 0 0 5px 0;"><?php echo addslashes($property['title_' . $current_language]); ?></h6>' +
                                                            '<p style="margin: 0;"><?php echo addslashes($property['location_' . $current_language]); ?></p>' +
                                                            '</div>'
                                                }
                                            );
                                            
                                            // Показване на информационния прозорец при кликване върху маркера
                                            marker.addEventListener('tap', function() {
                                                ui.addBubble(bubbleContent);
                                            });

                                            // Добавяне на маркера към картата
                                            map.addObject(marker);
                                        }
                                    }, alert);

                                    // Адаптиране на размера при промяна на прозореца
                                    window.addEventListener('resize', () => map.getViewPort().resize());
                                }

                                // Инициализиране на картата при зареждане
                                window.addEventListener('load', initMap);
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Форма за запитване -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $translations['property']['inquiry_form']['title']; ?></h5>
                            <p class="text-muted"><?php echo $translations['property']['inquiry_form']['subtitle']; ?></p>
                            
                            <form id="inquiryForm" class="mt-4">
                                <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['inquiry_form']['name']; ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['inquiry_form']['email']; ?> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['inquiry_form']['phone']; ?></label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['inquiry_form']['message']; ?> <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                </div>
                                <!-- Google reCAPTCHA -->
                                <div class="mb-3">
                                    <div class="g-recaptcha" data-sitekey="6LdtwroqAAAAAO_EqjtD8ZPwWQWtuuxh6MHqND4m"></div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><?php echo $translations['property']['inquiry_form']['submit']; ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Google reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Custom Scripts -->
    <script src="js/property.js"></script>

    <!-- Add Fancybox JS before the closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
    function printProperty() {
        // Скриваме временно всички елементи, които не искаме да се принтират
        const inquiryForm = document.querySelector('.col-lg-4');
        const originalDisplay = inquiryForm.style.display;
        inquiryForm.style.display = 'none';
        
        // Запазваме само техническите детайли и основната информация
        window.print();
        
        // Възстановяваме оригиналното състояние
        inquiryForm.style.display = originalDisplay;
    }

    function shareProperty() {
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: window.location.href
            })
            .catch((error) => console.log('Error sharing:', error));
        } else {
            // Fallback - копиране на URL в клипборда
            navigator.clipboard.writeText(window.location.href)
                .then(() => alert('<?php echo $translations['property']['link_copied']; ?>'))
                .catch(err => console.log('Error copying link:', err));
        }
    }

    Fancybox.bind("[data-fancybox]", {
        // Custom options
        Carousel: {
            infinite: false,
        },
        Thumbs: {
            autoStart: true,
        },
        Toolbar: {
            display: {
                left: ["infobar"],
                middle: [
                    "zoomIn",
                    "zoomOut",
                    "toggle1to1",
                    "rotateCCW",
                    "rotateCW",
                    "flipX",
                    "flipY",
                ],
                right: ["slideshow", "thumbs", "close"],
            },
        },
    });
    </script>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>