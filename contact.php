<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/language.php';
require_once 'includes/config.php';
require_once 'includes/contact_translations.php';

// Вземаме текущия език
$current_lang = $_SESSION['language'] ?? 'bg';
$contact_translations = $contact_translations[$current_lang];

// Вземане на запазените данни от формата, ако има такива
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Изчистваме запазените данни
?>

<!-- Добавяме reCAPTCHA скрипт -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="content-container">
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-lg-12">
                <h1><?php echo $contact_translations['title']; ?></h1>
                <div class="heading-divider"></div>
                
                <div class="row fade-in">
                    <div class="col-lg-6 mb-4">
                        <div class="contact-info">
                            <h2><?php echo $contact_translations['subtitle']; ?></h2>
                            <p class="lead mb-4">
                                <?php echo $contact_translations['description']; ?>
                            </p>
                            
                            <div class="contact-details">
                                <?php
                                // Зареждане на контактната информация от базата данни
                                $db = new PDO(
                                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                                    DB_USER,
                                    DB_PASS,
                                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                                );
                                
                                // Зареждане на основната контактна информация
                                $stmt = $db->query("SELECT * FROM contact_information WHERE type IN ('address', 'phone', 'email', 'working_hours') AND is_active = 1 ORDER BY sort_order");
                                $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Организиране на контактите по тип
                                $contactsByType = [];
                                foreach ($contacts as $contact) {
                                    $contactsByType[$contact['type']] = $contact;
                                }
                                
                                // Показване на адрес
                                if (isset($contactsByType['address'])): 
                                    $address = $contactsByType['address'];
                                ?>
                                <div class="mb-4">
                                    <h5><?php echo $contact_translations['address']['title']; ?></h5>
                                    <p class="mb-0">
                                        <i class="<?php echo htmlspecialchars($address['icon']); ?> me-2"></i>
                                        <?php if ($address['link']): ?>
                                            <a href="<?php echo htmlspecialchars($address['link']); ?>" class="text-decoration-none">
                                                <?php echo $contact_translations['address']['street']; ?><br>
                                                <?php echo $contact_translations['address']['city']; ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo $contact_translations['address']['street']; ?><br>
                                            <?php echo $contact_translations['address']['city']; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Телефон -->
                                <?php if (isset($contactsByType['phone'])): 
                                    $phone = $contactsByType['phone'];
                                ?>
                                <div class="mb-4">
                                    <h5><?php echo $contact_translations['contact_info']['phone']; ?></h5>
                                    <p class="mb-0">
                                        <i class="<?php echo htmlspecialchars($phone['icon']); ?> me-2"></i>
                                        <?php if ($phone['link']): ?>
                                            <a href="<?php echo htmlspecialchars($phone['link']); ?>" class="text-decoration-none">
                                                <?php echo $contact_translations['contact_info']['phone_number']; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="tel:<?php echo $contact_translations['contact_info']['phone_number']; ?>" class="text-decoration-none">
                                                <?php echo $contact_translations['contact_info']['phone_number']; ?>
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Имейл -->
                                <?php if (isset($contactsByType['email'])): 
                                    $email = $contactsByType['email'];
                                ?>
                                <div class="mb-4">
                                    <h5><?php echo $contact_translations['contact_info']['email']; ?></h5>
                                    <p class="mb-0">
                                        <i class="<?php echo htmlspecialchars($email['icon']); ?> me-2"></i>
                                        <?php if ($email['link']): ?>
                                            <a href="<?php echo htmlspecialchars($email['link']); ?>" class="text-decoration-none">
                                                <?php echo $contact_translations['contact_info']['email_address']; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="mailto:<?php echo $contact_translations['contact_info']['email_address']; ?>" class="text-decoration-none">
                                                <?php echo $contact_translations['contact_info']['email_address']; ?>
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Работно време -->
                                <?php if (isset($contactsByType['working_hours'])): 
                                    $hours = $contactsByType['working_hours'];
                                ?>
                                <div class="mb-4">
                                    <h5><?php echo $contact_translations['contact_info']['working_hours']; ?></h5>
                                    <p class="mb-0">
                                        <i class="<?php echo htmlspecialchars($hours['icon']); ?> me-2"></i>
                                        <?php echo $contact_translations['contact_info']['working_hours_text']; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Социални мрежи -->
                            <div class="social-links mt-4">
                                <h5>Последвайте ни</h5>
                                <div class="d-flex gap-3">
                                    <?php
                                    // Зареждане на социалните мрежи
                                    $stmt = $db->query("SELECT * FROM contact_information WHERE type IN ('facebook', 'instagram', 'linkedin', 'twitter') AND is_active = 1 ORDER BY sort_order");
                                    $socialLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($socialLinks as $social): 
                                        $iconClass = empty($social['value_bg']) ? 'text-muted' : '';
                                    ?>
                                        <?php if (empty($social['value_bg'])): ?>
                                            <i class="<?php echo htmlspecialchars($social['icon']); ?> fs-4 <?php echo $iconClass; ?>" 
                                               title="<?php echo ucfirst($social['type']); ?> (неактивен)"></i>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($social['value_bg']); ?>" 
                                               class="text-decoration-none"
                                               target="_blank" 
                                               title="<?php echo ucfirst($social['type']); ?>">
                                                <i class="<?php echo htmlspecialchars($social['icon']); ?> fs-4"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="contact-form card">
                            <div class="card-body p-4">
                                <h2><?php echo $contact_translations['form']['title']; ?></h2>
                                
                                <?php if (isset($_SESSION['contact_success'])): ?>
                                    <div class="alert alert-success">
                                        <?php 
                                        echo $contact_translations['form']['success'];
                                        unset($_SESSION['contact_success']);
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['contact_error'])): ?>
                                    <div class="alert alert-danger">
                                        <?php 
                                        echo $contact_translations['form']['error'];
                                        unset($_SESSION['contact_error']);
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form action="process_contact.php" method="post" class="mt-4">
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo $contact_translations['form']['name']; ?></label>
                                        <input type="text" class="form-control" name="name" required 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo $contact_translations['form']['email']; ?></label>
                                        <input type="email" class="form-control" name="email" required
                                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo $contact_translations['form']['phone']; ?></label>
                                        <input type="tel" class="form-control" name="phone"
                                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo $contact_translations['form']['subject']; ?></label>
                                        <select class="form-select" name="subject" required>
                                            <option value=""><?php echo $contact_translations['form']['subject']; ?></option>
                                            <option value="general"><?php echo $contact_translations['subjects']['general']; ?></option>
                                            <option value="property"><?php echo $contact_translations['subjects']['property']; ?></option>
                                            <option value="service"><?php echo $contact_translations['subjects']['service']; ?></option>
                                            <option value="partnership"><?php echo $contact_translations['subjects']['partnership']; ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label"><?php echo $contact_translations['form']['message']; ?></label>
                                        <textarea class="form-control" name="message" rows="5" required><?php 
                                            echo htmlspecialchars($form_data['message'] ?? ''); 
                                        ?></textarea>
                                    </div>

                                    <!-- reCAPTCHA -->
                                    <div class="mb-4">
                                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <?php echo $contact_translations['form']['submit']; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="map-container">
                            <h2><?php echo $contact_translations['office_title']; ?></h2>
                            <div id="contactMap" style="height: 450px; width: 100%; border-radius: 8px; position: relative;" class="mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Here Maps API -->
<link rel="stylesheet" type="text/css" href="https://js.api.here.com/v3/3.1/mapsjs-ui.css" />
<script src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-ui.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>

<script>
function initContactMap() {
    // Инициализация на Here Maps платформата
    const platform = new H.service.Platform({
        'apikey': 'lpQVOFFyys9adQhUqk5e6VQ_WBqcsKv4DdfTZTIipTs'
    });

    // Настройки по подразбиране за картата
    const defaultLayers = platform.createDefaultLayers();

    // Създаване на картата
    const map = new H.Map(
        document.getElementById('contactMap'),
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

    // Адрес на офиса
    const officeAddress = '<?php echo $contact_translations['address']['street'] . ', ' . $contact_translations['address']['city']; ?>';

    // Търсене на координатите по адрес
    geocodingService.geocode({
        q: officeAddress
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
                            '<h6 style="margin: 0 0 5px 0;">' + <?php echo json_encode($contact_translations['office_title']); ?> + '</h6>' +
                            '<p style="margin: 0;">' + officeAddress + '</p>' +
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
window.addEventListener('load', initContactMap);
</script>

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


</style>

<?php require_once 'includes/footer.php'; ?> 