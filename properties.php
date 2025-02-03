<?php
session_start();
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'src/Database.php';
require_once 'includes/language.php';

use App\Database;

// Определяне на текущия език
$current_language = $_SESSION['language'] ?? 'bg';

// Заглавия на различни езици
$titles = [
    'bg' => 'Индустриални Имоти',
    'en' => 'Industrial Properties',
    'de' => 'Industrieimmobilien',
    'ru' => 'Промышленная Недвижимость'
];

$title = $titles[$current_language];

try {
    // Инициализираме връзката с базата данни
    $db = Database::getInstance()->getConnection();

// Филтри
    $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$min_area = filter_input(INPUT_GET, 'min_area', FILTER_VALIDATE_FLOAT);
$max_area = filter_input(INPUT_GET, 'max_area', FILTER_VALIDATE_FLOAT);
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'date_desc';
    $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $per_page = 9;
$offset = ($page - 1) * $per_page;

    // Основна заявка за имотите
    $base_sql = "FROM properties p WHERE 1=1";
    $params = [];
    
    // Добавяме филтрите
    if (!empty($type)) {
        $base_sql .= " AND p.type = ?";
        $params[] = $type;
    }

    // Филтър за статус само ако е избран от потребителя
    if (!empty($_GET['status'])) {
        $base_sql .= " AND p.status = ?";
        $params[] = $status;
    }

    if (!empty($min_area)) {
        $base_sql .= " AND p.area >= ?";
        $params[] = $min_area;
    }

    if (!empty($max_area)) {
        $base_sql .= " AND p.area <= ?";
        $params[] = $max_area;
    }

    if (!empty($min_price)) {
        $base_sql .= " AND p.price >= ?";
        $params[] = $min_price;
    }

    if (!empty($max_price)) {
        $base_sql .= " AND p.price <= ?";
        $params[] = $max_price;
    }

    // Основна SQL заявка
    $sql = "SELECT p.*, 
            COALESCE(
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1),
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1)
            ) as image_path,
            p.pdf_flyer " . $base_sql;

    // Добавяме сортиране и лимит
    $sql .= " ORDER BY p.created_at DESC LIMIT " . $per_page . " OFFSET " . $offset;

    // SQL заявка за броя на имотите
    $count_sql = "SELECT COUNT(*) " . $base_sql;

    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();

    // Изпълняваме заявката
$stmt = $db->prepare($sql);
$stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug информация за PDF файловете
foreach ($properties as $property) {
    error_log("Property ID: " . $property['id'] . ", PDF Flyer: " . ($property['pdf_flyer'] ? $property['pdf_flyer'] : 'No PDF'));
}

// Изчисляваме общия брой страници
$total_pages = ceil($total / $per_page);

    // Debug информация
    error_log("Total properties: " . $total);
    error_log("Total pages: " . $total_pages);
    error_log("Current page: " . $page);
    error_log("Properties found: " . count($properties));

} catch (PDOException $e) {
    error_log("Database PDO error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("SQL State: " . $e->errorInfo[0]);
    die("Възникна грешка при достъп до базата данни: " . $e->getMessage());
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    die("Възникна неочаквана грешка: " . $e->getMessage());
}

// ... existing code ...
?>

<div class="content-container">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo $title; ?></h1>
            <button type="button" class="btn btn-outline-primary" id="showMapBtn">
                <i class="bi bi-map"></i> 
                <?php 
                    $showMapText = [
                        'bg' => 'Покажи на картата',
                        'en' => 'Show on Map',
                        'de' => 'Auf Karte anzeigen',
                        'ru' => 'Показать на карте'
                    ];
                    echo $showMapText[$current_language] ?? $showMapText['bg'];
                ?>
            </button>
        </div>
        <div class="heading-divider"></div>

        <!-- Add custom styles for tooltips -->
        <style>
        .tooltip-inner {
            max-width: 300px;
            padding: 10px 15px;
            background-color: rgba(33, 37, 41, 0.95);
            font-size: 14px;
            line-height: 1.5;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .property-type-info {
            color: #6c757d;
            cursor: help;
            transition: color 0.2s;
        }

        .property-type-info:hover {
            color: #0d6efd;
        }

        .form-check {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }

        .form-check-input[type="radio"] {
            margin-right: 8px;
            margin-top: 0;
        }

        .form-check-label {
            margin-bottom: 0;
            line-height: 1;
        }

        .property-content {
            position: relative;
        }

        .pdf-flyer-link {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 2;
        }

        .pdf-flyer-link:hover {
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
            color: #333;
            text-decoration: none;
        }

        .pdf-flyer-link i {
            color: #dc3545;
            font-size: 1.1rem;
        }

        /* Стилове за филтъра */
        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-section h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        /* Стилове за радио бутоните */
        .filter-section input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #3498db;
            border-radius: 3px;
            margin-right: 8px;
            position: relative;
            top: 3px;
            cursor: pointer;
        }

        .filter-section input[type="radio"]:checked {
            background-color: #3498db;
        }

        .filter-section input[type="radio"]:checked::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
        }

        /* Стилове за етикетите */
        .filter-section label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #34495e;
            cursor: pointer;
        }

        .filter-section label:hover {
            color: #3498db;
        }

        .filter-section label input[type="radio"] {
            margin-right: 8px;
        }

        /* Стилове за групата с филтри */
        .filter-group {
            margin-bottom: 20px;
            padding-left: 0;
        }

        .filter-group:last-child {
            margin-bottom: 0;
        }

        /* Стилове за заглавието на филтъра */
        .filter-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            display: block;
        }

        .property-types .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .property-types .form-check-input[type="radio"] { 
            margin-top: 0;
            position: relative;
            top: -2px;
            margin-left: -30px;
        }

        .property-types .form-check-label {
            margin-bottom: 0;
            line-height: 1;
            padding-top: 2px;
        }

        .property-types .property-type-info {
            margin-top: -2px;
        }
        </style>

        <!-- Main Content -->
        <main class="flex-grow-1">
            <div class="container-fluid px-4 py-5">
                <div class="row g-4">
                    <!-- Filters Column -->
                    <div class="col-lg-3">
                        <div class="filter-section bg-light p-4 rounded">
                            <h5 class="card-title mb-4"><?php echo $translations['property']['filter']['title']; ?></h5>
                            <form action="" method="get" id="filterForm">
                                <!-- Property Type -->
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['filter']['type']; ?></label>
                                    <div class="property-types">
                                        <?php 
                                        $property_descriptions = [
                                            'manufacturing' => [
                                                'bg' => [
                                                    'title' => 'Производствени сгради',
                                                    'description' => 'Модерни производствени помещения, подходящи за различни индустрии. Включва сгради за леко и тежко производство с възможност за персонализация според нуждите.'
                                                ],
                                                'en' => [
                                                    'title' => 'Manufacturing Buildings',
                                                    'description' => 'Modern manufacturing facilities suitable for various industries. Includes light and heavy manufacturing buildings with customization options according to needs.'
                                                ],
                                                'de' => [
                                                    'title' => 'Produktionsgebäude',
                                                    'description' => 'Moderne Produktionsanlagen für verschiedene Branchen. Umfasst Leicht- und Schwerfertigung mit Anpassungsmöglichkeiten nach Bedarf.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Производственные здания',
                                                    'description' => 'Современные производственные помещения для различных отраслей. Включает здания для легкой и тяжелой промышленности с возможностью персонализации.'
                                                ]
                                            ],
                                            'logistics' => [
                                                'bg' => [
                                                    'title' => 'Логистични центрове',
                                                    'description' => 'Стратегически разположени логистични центрове с отлична свързаност. Оборудвани с модерни системи за съхранение и обработка на стоки.'
                                                ],
                                                'en' => [
                                                    'title' => 'Logistics Centers',
                                                    'description' => 'Strategically located logistics centers with excellent connectivity. Equipped with modern storage and goods handling systems.'
                                                ],
                                                'de' => [
                                                    'title' => 'Logistikzentren',
                                                    'description' => 'Strategisch günstig gelegene Logistikzentren mit ausgezeichneter Anbindung. Ausgestattet mit modernen Lager- und Warenwirtschaftssystemen.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Логистические центры',
                                                    'description' => 'Стратегически расположенные логистические центры с отличной связью. Оборудованы современными системами хранения и обработки товаров.'
                                                ]
                                            ],
                                            'office' => [
                                                'bg' => [
                                                    'title' => 'Офис сгради',
                                                    'description' => 'Съвременни офис пространства в индустриални зони. Включва самостоятелни офис сгради и комбинирани пространства с производствени помещения.'
                                                ],
                                                'en' => [
                                                    'title' => 'Office Buildings',
                                                    'description' => 'Modern office spaces in industrial zones. Includes standalone office buildings and combined spaces with manufacturing facilities.'
                                                ],
                                                'de' => [
                                                    'title' => 'Bürogebäude',
                                                    'description' => 'Moderne Büroflächen in Industriegebieten. Umfasst eigenständige Bürogebäude und kombinierte Räume mit Produktionsanlagen.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Офисные здания',
                                                    'description' => 'Современные офисные помещения в промышленных зонах. Включает отдельные офисные здания и комбинированные пространства с производственными помещениями.'
                                                ]
                                            ],
                                            'logistics_park' => [
                                                'bg' => [
                                                    'title' => 'Логистични паркове',
                                                    'description' => 'Мащабни логистични комплекси с пълна инфраструктура. Предлагат разнообразни складови и дистрибуционни решения под един покрив.'
                                                ],
                                                'en' => [
                                                    'title' => 'Logistics Parks',
                                                    'description' => 'Large-scale logistics complexes with complete infrastructure. Offering various warehousing and distribution solutions under one roof.'
                                                ],
                                                'de' => [
                                                    'title' => 'Logistikparks',
                                                    'description' => 'Großflächige Logistikkomplexe mit vollständiger Infrastruktur. Bieten verschiedene Lager- und Vertriebslösungen unter einem Dach.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Логистические парки',
                                                    'description' => 'Масштабные логистические комплексы с полной инфраструктурой. Предлагают различные складские и дистрибьюторские решения под одной крышей.'
                                                ]
                                            ],
                                            'specialized' => [
                                                'bg' => [
                                                    'title' => 'Специализирани имоти',
                                                    'description' => 'Имоти със специално предназначение, включително хладилни складове, чисти помещения и специализирани производствени facility.'
                                                ],
                                                'en' => [
                                                    'title' => 'Specialized Properties',
                                                    'description' => 'Special purpose properties including cold storage facilities, clean rooms, and specialized manufacturing facilities.'
                                                ],
                                                'de' => [
                                                    'title' => 'Spezialimmobilien',
                                                    'description' => 'Immobilien für spezielle Zwecke, einschließlich Kühlhäuser, Reinräume und spezialisierte Produktionsanlagen.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Специализированная недвижимость',
                                                    'description' => 'Объекты специального назначения, включая холодильные склады, чистые помещения и специализированные производственные объекты.'
                                                ]
                                            ],
                                            'logistics_terminal' => [
                                                'bg' => [
                                                    'title' => 'Логистични терминали',
                                                    'description' => 'Мултимодални логистични терминали с достъп до различни видове транспорт. Включва ЖП терминали, контейнерни терминали и разпределителни центрове.'
                                                ],
                                                'en' => [
                                                    'title' => 'Logistics Terminals',
                                                    'description' => 'Multimodal logistics terminals with access to various types of transport. Includes railway terminals, container terminals, and distribution centers.'
                                                ],
                                                'de' => [
                                                    'title' => 'Logistikterminals',
                                                    'description' => 'Multimodale Logistikterminals mit Zugang zu verschiedenen Verkehrsträgern. Umfasst Bahnterminals, Containerterminals und Verteilzentren.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Логистические терминалы',
                                                    'description' => 'Мультимодальные логистические терминалы с доступом к различным видам транспорта. Включает железнодорожные терминалы, контейнерные терминалы и распределительные центры.'
                                                ]
                                            ],
                                            'land' => [
                                                'bg' => [
                                                    'title' => 'Земя за строеж',
                                                    'description' => 'Парцели за индустриално строителство с всички необходими комуникации. Подходящи за изграждане на производствени и логистични бази.'
                                                ],
                                                'en' => [
                                                    'title' => 'Land for Construction',
                                                    'description' => 'Industrial construction plots with all necessary utilities. Suitable for building manufacturing and logistics facilities.'
                                                ],
                                                'de' => [
                                                    'title' => 'Bauland',
                                                    'description' => 'Industriebaugrundstücke mit allen notwendigen Versorgungseinrichtungen. Geeignet für den Bau von Produktions- und Logistikanlagen.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Земля под застройку',
                                                    'description' => 'Участки под промышленное строительство со всеми необходимыми коммуникациями. Подходят для строительства производственных и логистических объектов.'
                                                ]
                                            ],
                                            'food_industry' => [
                                                'bg' => [
                                                    'title' => 'Хранителна индустрия',
                                                    'description' => 'Специализирани помещения за хранително-вкусовата промишленост. Отговарят на всички хигиенни изисквания и стандарти за безопасност.'
                                                ],
                                                'en' => [
                                                    'title' => 'Food Industry',
                                                    'description' => 'Specialized facilities for the food and beverage industry. Meeting all hygiene requirements and safety standards.'
                                                ],
                                                'de' => [
                                                    'title' => 'Lebensmittelindustrie',
                                                    'description' => 'Spezialisierte Einrichtungen für die Lebensmittel- und Getränkeindustrie. Erfüllen alle Hygieneanforderungen und Sicherheitsstandards.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Пищевая промышленность',
                                                    'description' => 'Специализированные помещения для пищевой промышленности. Соответствуют всем гигиеническим требованиям и стандартам безопасности.'
                                                ]
                                            ],
                                            'heavy_industry' => [
                                                'bg' => [
                                                    'title' => 'Тежка индустрия',
                                                    'description' => 'Индустриални комплекси за тежката промишленост. Включва съоръжения за металургия, машиностроене и преработвателна промишленост.'
                                                ],
                                                'en' => [
                                                    'title' => 'Heavy Industry',
                                                    'description' => 'Industrial complexes for heavy industry. Includes facilities for metallurgy, mechanical engineering, and processing industry.'
                                                ],
                                                'de' => [
                                                    'title' => 'Schwerindustrie',
                                                    'description' => 'Industriekomplexe für die Schwerindustrie. Umfasst Anlagen für Metallurgie, Maschinenbau und verarbeitende Industrie.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Тяжелая промышленность',
                                                    'description' => 'Промышленные комплексы для тяжелой промышленности. Включает объекты металлургии, машиностроения и обрабатывающей промышленности.'
                                                ]
                                            ],
                                            'tech_industry' => [
                                                'bg' => [
                                                    'title' => 'Технологични индустрии',
                                                    'description' => 'Високотехнологични производствени бази и центрове за данни. Оборудвани с най-съвременна инфраструктура за технологични компании.'
                                                ],
                                                'en' => [
                                                    'title' => 'Technology Industries',
                                                    'description' => 'High-tech manufacturing facilities and data centers. Equipped with state-of-the-art infrastructure for technology companies.'
                                                ],
                                                'de' => [
                                                    'title' => 'Technologieindustrien',
                                                    'description' => 'Hochmoderne Produktionsanlagen und Datenzentren. Ausgestattet mit modernster Infrastruktur für Technologieunternehmen.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Технологические индустрии',
                                                    'description' => 'Высокотехнологичные производственные базы и центры обработки данных. Оборудованы современной инфраструктурой для технологических компаний.'
                                                ]
                                            ],
                                            'hotels' => [
                                                'bg' => [
                                                    'title' => 'Хотели',
                                                    'description' => 'Хотелски имоти в индустриални и бизнес зони. Идеални за настаняване на бизнес гости и дългосрочни корпоративни клиенти.'
                                                ],
                                                'en' => [
                                                    'title' => 'Hotels',
                                                    'description' => 'Hotel properties in industrial and business zones. Ideal for accommodating business guests and long-term corporate clients.'
                                                ],
                                                'de' => [
                                                    'title' => 'Hotels',
                                                    'description' => 'Hotelimmobilien in Industrie- und Geschäftszonen. Ideal für die Unterbringung von Geschäftsreisenden und langfristigen Firmenkunden.'
                                                ],
                                                'ru' => [
                                                    'title' => 'Отели',
                                                    'description' => 'Гостиничная недвижимость в промышленных и деловых зонах. Идеально подходит для размещения бизнес-гостей и долгосрочных корпоративных клиентов.'
                                                ]
                                            ]
                                        ];
                                        ?>
                                        <?php foreach ($translations['property']['type'] as $key => $value): ?>
                                            <div class="form-check" style="display: flex; align-items: center; margin-bottom: 0.75rem;">
                                                <input type="radio" name="type" value="<?php echo $key; ?>" 
                                                       class="form-check-input" id="type_<?php echo $key; ?>"
                                                       style="margin-top: 0; position: relative; top: -1px;"
                                                       <?php echo $type === $key ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_<?php echo $key; ?>" 
                                                       style="margin-bottom: 0; line-height: normal; display: flex; align-items: center;">
                                                    <?php echo $value; ?>
                                                </label>
                                                <?php if (isset($property_descriptions[$key][$current_language])): ?>
                                                    <i class="bi bi-info-circle ms-2 property-type-info" 
                                                       style="margin-top: -1px;"
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="right"
                                                       data-bs-html="true"
                                                       title="<strong><?php echo htmlspecialchars($property_descriptions[$key][$current_language]['title']); ?></strong><br><br><?php echo htmlspecialchars($property_descriptions[$key][$current_language]['description']); ?>">
                                                    </i>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Property Status -->
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['filter']['status']; ?></label>
                                    <select name="status" class="form-select">
                                        <option value=""><?php echo $translations['property']['status']['all']; ?></option>
                                        <?php foreach ($translations['property']['status'] as $key => $value): ?>
                                            <?php if ($key !== 'all'): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $status === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Area Range -->
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['filter']['area']; ?></label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" name="min_area" class="form-control" 
                                                   placeholder="<?php echo $translations['property']['filter']['min']; ?>"
                                                   value="<?php echo $min_area; ?>">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" name="max_area" class="form-control" 
                                                   placeholder="<?php echo $translations['property']['filter']['max']; ?>"
                                                   value="<?php echo $max_area; ?>">
                                        </div>
                                </div>
                                </div>

                                <!-- Price Range -->
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $translations['property']['filter']['price']; ?></label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" name="min_price" class="form-control" 
                                                   placeholder="<?php echo $translations['property']['filter']['min']; ?>"
                                                   value="<?php echo $min_price; ?>">
                                </div>
                                        <div class="col-6">
                                            <input type="number" name="max_price" class="form-control" 
                                                   placeholder="<?php echo $translations['property']['filter']['max']; ?>"
                                                   value="<?php echo $max_price; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort -->
                                <div class="mb-4">
                                    <label class="form-label"><?php echo $translations['property']['sort']['title']; ?></label>
                                    <select name="sort" class="form-select">
                                        <?php
                                        $sort_options = [
                                            'date_desc' => $translations['property']['sort']['date_desc'],
                                            'date_asc' => $translations['property']['sort']['date_asc'],
                                            'price_asc' => $translations['property']['sort']['price_asc'],
                                            'price_desc' => $translations['property']['sort']['price_desc'],
                                            'area_asc' => $translations['property']['sort']['area_asc'],
                                            'area_desc' => $translations['property']['sort']['area_desc']
                                        ];
                                        foreach ($sort_options as $key => $value):
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo $sort === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo $translations['property']['filter']['apply']; ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                                        <?php echo $translations['property']['filter']['clear']; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Properties Grid Column -->
                    <div class="col-lg-9">
                        <!-- Results Info -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <p class="mb-0">
                                <?php printf($translations['property']['showing_results'], count($properties), $total); ?>
                            </p>
                        </div>

                        <?php if (empty($properties)): ?>
                            <div class="alert alert-info">
                                <?php echo $translations['property']['no_results']; ?>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($properties as $property): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card property-card h-100">
                                            <?php if ($property['status']): ?>
                                                <div class="property-status <?php echo $property['status']; ?>">
                                                    <?php echo $translations['property']['status'][$property['status']]; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="position-relative">
                                                <img src="<?php echo $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'images/no-image.jpg'; ?>" 
                                                     class="card-img-top" alt="<?php echo $property["title_{$current_language}"]; ?>">
                                                <?php if (isset($property['pdf_flyer']) && !empty(trim($property['pdf_flyer']))): ?>
                                                    <a href="uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" target="_blank" class="pdf-flyer-link">
                                                        <i class="fas fa-file-pdf"></i> 
                                                        <?php 
                                                        $pdf_text = [
                                                            'bg' => 'Виж експозе',
                                                            'en' => 'View brochure',
                                                            'de' => 'Exposé ansehen',
                                                            'ru' => 'Смотреть брошюру'
                                                        ];
                                                        echo $pdf_text[$current_language] ?? $pdf_text['en'];
                                                        ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $property["title_{$current_language}"]; ?></h5>
                                                <div class="property-features">
                                                    <div class="feature">
                                                        <i class="bi bi-rulers me-2"></i>
                                                        <?php echo number_format($property['area']); ?> m²
                                                    </div>
                                                    <div class="feature">
                                                        <i class="bi bi-geo-alt me-2"></i>
                                                        <?php echo $property["location_{$current_language}"]; ?>
                                                    </div>
                                                    <div class="feature">
                                                        <i class="bi bi-building me-2"></i>
                                                        <?php echo $translations['property']['type'][$property['type']]; ?>
                                                    </div>
                                                    <div class="feature">
                                                        <i class="bi bi-currency-euro me-2"></i>
                                                        <?php echo number_format($property['price']); ?>
                                                    </div>
                                                </div>
                                                <a href="/property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-primary mt-3 w-100">
                                                    <?php echo $translations['property']['details']; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                                <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                    <?php echo $translations['pagination']['previous']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                    <?php echo $translations['pagination']['next']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 

<script>
document.getElementById('clearFilters').addEventListener('click', function() {
    window.location.href = 'properties.php';
});

// Initialize tooltips with custom options
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            animation: true,
            delay: { show: 100, hide: 100 }
        });
    });
});
</script> 

<!-- Here Maps Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">
                    <?php 
                        $mapLocationsText = [
                            'bg' => 'Локации на имотите',
                            'en' => 'Property Locations',
                            'de' => 'Immobilien Standorte',
                            'ru' => 'Расположение объектов'
                        ];
                        echo $mapLocationsText[$current_language] ?? $mapLocationsText['bg'];
                    ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="propertiesMap" style="height: 100%; width: 100%;"></div>
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
// Масив с всички имоти
const properties = <?php echo json_encode($properties); ?>;

// Функция за инициализация на картата
function initPropertiesMap() {
    const platform = new H.service.Platform({
        'apikey': 'lpQVOFFyys9adQhUqk5e6VQ_WBqcsKv4DdfTZTIipTs'
    });

    const defaultLayers = platform.createDefaultLayers();
    const map = new H.Map(
        document.getElementById('propertiesMap'),
        defaultLayers.vector.normal.map,
        {
            zoom: 7,
            center: { lat: 42.7339, lng: 25.4858 }, // Center of Bulgaria
            pixelRatio: window.devicePixelRatio || 1
        }
    );

    // Добавяне на контроли
    const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
    const ui = H.ui.UI.createDefault(map, defaultLayers);

    // Geocoding service
    const geocodingService = platform.getSearchService();
    const group = new H.map.Group();
    map.addObject(group);

    // Брояч за имотите
    let propertyCounter = 1;

    // Добавяне на маркери за всеки имот
    properties.forEach((property) => {
        const address = property['location_<?php echo $current_language; ?>'] || property['location_bg'];
        
        geocodingService.geocode({
            q: address + ', България' // Добавяме България за по-точно геокодиране
        }, (result) => {
            if (result.items.length > 0) {
                const coordinates = result.items[0].position;
                
                // Създаване на HTML елемент за маркера
                const markerElement = document.createElement('div');
                markerElement.className = 'custom-marker';
                markerElement.innerHTML = `
                    <div style="background-color: #0d6efd; color: white; border-radius: 50%; width: 30px; height: 30px; 
                               display: flex; align-items: center; justify-content: center; font-weight: bold; 
                               border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        ${propertyCounter}
                    </div>`;
                
                // Създаване на DOM маркер
                const marker = new H.map.DomMarker(coordinates, {
                    element: markerElement
                });
                
                // Информационен прозорец
                const bubbleContent = new H.ui.InfoBubble(
                    coordinates,
                    {
                        content: `
                            <div style="padding: 10px; max-width: 250px;">
                                <div style="background-color: #0d6efd; color: white; border-radius: 50%; width: 24px; height: 24px; 
                                          display: flex; align-items: center; justify-content: center; font-weight: bold; 
                                          margin-bottom: 8px;">
                                    ${propertyCounter}
                                </div>
                                <h6 style="margin: 0 0 5px 0;">
                                    <a href="property.php?id=${property.id}" style="text-decoration: none; color: inherit;">
                                        ${property['title_<?php echo $current_language; ?>'] || property['title_bg']}
                                    </a>
                                </h6>
                                <p style="margin: 0 0 5px 0; font-size: 0.9em; color: #666;">
                                    ${address}
                                </p>
                                <p style="margin: 0; font-weight: bold;">
                                    ${number_format(property.price, 0, '.', ' ')} €
                                </p>
                                <p style="margin: 0; color: #666;">
                                    ${number_format(property.area, 0, '.', ' ')} м²
                                </p>
                            </div>
                        `
                    }
                );
                
                marker.addEventListener('tap', () => {
                    ui.addBubble(bubbleContent);
                });

                group.addObject(marker);
                propertyCounter++;

                // Ако това е последният маркер, центрираме картата
                if (propertyCounter > properties.length) {
                    map.getViewModel().setLookAtData({
                        bounds: group.getBoundingBox()
                    });
                }
            }
        }, alert);
    });

    // Адаптиране на размера при промяна на прозореца
    window.addEventListener('resize', () => map.getViewPort().resize());
}

// Показване на модалния прозорец при клик на бутона
document.getElementById('showMapBtn').addEventListener('click', function() {
    const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
    mapModal.show();
    
    // Инициализираме картата след показване на модала
    document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
        if (!window.mapInitialized) {
            initPropertiesMap();
            window.mapInitialized = true;
        }
    });
});

// Помощна функция за форматиране на числа
function number_format(number, decimals, dec_point, thousands_sep) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
}
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

/* Properties Grid */
.properties-grid {
    width: 100% !important;
    max-width: 100% !important;
}

/* Remove Bootstrap Container Max-Width */
@media (min-width: 0) {
    .container,
    .container-sm,
    .container-md,
    .container-lg,
    .container-xl,
    .container-xxl {
        max-width: 100% !important;
    }
}

.filter-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.properties-grid .row {
    margin: 0 -12px;
}

.property-card {
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    background: #fff;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-features {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 15px;
}

.feature {
    display: flex;
    align-items: center;
    color: #666;
    font-size: 0.9rem;
}

.property-status {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    z-index: 1;
}

@media (max-width: 991px) {
    .filter-section {
        margin-bottom: 30px;
    }
}
</style> 