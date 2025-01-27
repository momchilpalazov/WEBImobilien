<?php
session_start();

// Функция за логване
function writeLog($message) {
    $logFile = __DIR__ . '/logs/settings.log';
    $logDir = dirname($logFile);
    
    // Създаване на директорията за логове ако не съществува
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

writeLog('Започва зареждане на settings.php');

// Включване на необходимите файлове
try {
    writeLog('Опит за включване на auth_check.php');
    require_once '../includes/auth_check.php';
    writeLog('auth_check.php включен успешно');
    
    writeLog('Опит за включване на Database.php');
    require_once '../src/Database.php';
    writeLog('Database.php включен успешно');
    
    writeLog('Опит за включване на database.php');
    require_once '../config/database.php';
    writeLog('database.php включен успешно');
} catch (Exception $e) {
    writeLog('Грешка при включване на файлове: ' . $e->getMessage());
    die('Грешка при зареждане на необходимите файлове: ' . $e->getMessage());
}

// Проверка за логнат потребител
writeLog('Проверка за логнат потребител');
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    writeLog('Потребителят не е логнат. Пренасочване към login.php');
    header('Location: login.php');
    exit;
}
writeLog('Потребителят е логнат успешно');

// Инициализация на базата данни
try {
    writeLog('Опит за свързване с базата данни');
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    writeLog('Успешно свързване с базата данни');
} catch (PDOException $e) {
    writeLog('Грешка при свързване с базата данни: ' . $e->getMessage());
    die('Грешка при свързване с базата данни: ' . $e->getMessage());
}

// Обработка на формата за настройки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Обработка на логото
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/logo/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileInfo = getimagesize($_FILES['site_logo']['tmp_name']);
            if ($fileInfo === false) {
                throw new Exception('Невалиден формат на изображението');
            }

            // Максимални размери за логото
            $maxWidth = 200;
            $maxHeight = 60;

            list($width, $height) = $fileInfo;
            
            // Изчисляване на новите размери със запазване на пропорциите
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);

            // Създаване на ново изображение
            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            
            // Запазване на прозрачността за PNG
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            
            // Зареждане на оригиналното изображение
            switch ($fileInfo[2]) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($_FILES['site_logo']['tmp_name']);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($_FILES['site_logo']['tmp_name']);
                    break;
                default:
                    throw new Exception('Неподдържан формат на изображението');
            }

            // Преоразмеряване
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Генериране на уникално име
            $filename = 'logo_' . time() . '.png';
            $filepath = $uploadDir . $filename;

            // Запазване на изображението
            imagepng($thumb, $filepath, 9);
            
            // Освобождаване на паметта
            imagedestroy($thumb);
            imagedestroy($source);

            // Записване в базата данни
            $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'site_logo'");
            $stmt->execute([$filename]);
        }

        // Обработка на останалите настройки
        $settings = [
            'site_name',
            'footer_text',
            'google_maps_api_key',
            'recaptcha_site_key',
            'recaptcha_secret_key'
        ];

        foreach ($settings as $key) {
            if (isset($_POST[$key])) {
                $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$_POST[$key], $key]);
            }
        }

        // Обработка на контактната информация
        if (isset($_POST['contacts'])) {
            foreach ($_POST['contacts'] as $id => $contact) {
                $stmt = $db->prepare("
                    UPDATE contact_information 
                    SET value_bg = ?, value_en = ?, value_de = ?, value_ru = ?,
                        link = ?, icon = ?, sort_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $contact['value_bg'],
                    $contact['value_en'],
                    $contact['value_de'],
                    $contact['value_ru'],
                    $contact['link'] ?? '',
                    $contact['icon'],
                    $contact['sort_order'],
                    isset($contact['is_active']) ? 1 : 0,
                    $id
                ]);
            }
        }

        $_SESSION['success_message'] = 'Настройките са запазени успешно!';
        header('Location: settings.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Грешка: ' . $e->getMessage();
    }
}

writeLog('Зареждане на настройките от базата данни');
// Зареждане на текущите настройки
$stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
$settingsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = array();
foreach ($settingsArray as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
writeLog('Настройките са заредени успешно');

writeLog('Зареждане на контактната информация');
// Зареждане на контактната информация
$stmt = $db->query("SELECT * FROM contact_information ORDER BY sort_order");
$contactsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
$contacts = [];
foreach ($contactsArray as $contact) {
    $contacts[$contact['type']] = $contact;
}
writeLog('Контактната информация е заредена успешно');

$page_title = 'Настройки на сайта';
writeLog('Зареждане на header.php');
require_once 'includes/header.php';
writeLog('header.php зареден успешно');
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4 text-gray-800">Настройки на сайта</h1>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Основни настройки</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="update_settings.php" enctype="multipart/form-data">
                        <!-- Лого -->
                        <div class="form-group mb-4">
                            <label class="form-label">Лого</label>
                            <?php if (!empty($settings['site_logo'])): ?>
                                <div class="current-logo mb-3">
                                    <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                         alt="Текущо лого" 
                                         style="max-height: 100px;" 
                                         class="mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_logo" id="remove_logo" class="form-check-input">
                                        <label class="form-check-label" for="remove_logo">Премахни логото</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" class="form-control" accept="image/*">
                            <small class="form-text text-muted">
                                Препоръчителни размери: 200x60 пиксела. Максимален размер: 2MB.
                                Поддържани формати: JPG, PNG, GIF.
                            </small>
                        </div>

                        <!-- Име на сайта -->
                        <div class="form-group mb-4">
                            <label class="form-label">Име на сайта</label>
                            <input type="text" name="site_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                        </div>

                        <!-- Текст във футъра -->
                        <div class="form-group mb-4">
                            <label class="form-label">Текст във футъра</label>
                            <textarea name="footer_text" class="form-control" rows="3"><?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?></textarea>
                        </div>

                        <!-- API ключове -->
                        <div class="form-group mb-4">
                            <label class="form-label">Google Maps API ключ</label>
                            <input type="text" name="google_maps_api_key" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['google_maps_api_key'] ?? ''); ?>">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">reCAPTCHA Site Key</label>
                            <input type="text" name="recaptcha_site_key" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['recaptcha_site_key'] ?? ''); ?>">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">reCAPTCHA Secret Key</label>
                            <input type="text" name="recaptcha_secret_key" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['recaptcha_secret_key'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Запази настройките</button>
                    </form>
                </div>
            </div>

            <!-- Контактна информация -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Контактна информация</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="update_contacts.php">
                        <!-- Телефон -->
                        <div class="form-group mb-4">
                            <label class="form-label">Телефон</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Български</label>
                                    <input type="text" name="contacts[phone][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['value_bg'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">English</label>
                                    <input type="text" name="contacts[phone][value_en]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['value_en'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deutsch</label>
                                    <input type="text" name="contacts[phone][value_de]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['value_de'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Русский</label>
                                    <input type="text" name="contacts[phone][value_ru]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['value_ru'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Икона (Bootstrap Icons клас)</label>
                                    <input type="text" name="contacts[phone][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['icon'] ?? 'bi-telephone'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Линк (опционално)</label>
                                    <input type="text" name="contacts[phone][link]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['phone']['link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[phone][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['phone']['sort_order'] ?? 1); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[phone][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['phone']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="form-group mb-4">
                            <label class="form-label">Email</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Български</label>
                                    <input type="email" name="contacts[email][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['value_bg'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">English</label>
                                    <input type="email" name="contacts[email][value_en]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['value_en'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deutsch</label>
                                    <input type="email" name="contacts[email][value_de]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['value_de'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Русский</label>
                                    <input type="email" name="contacts[email][value_ru]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['value_ru'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Икона (Bootstrap Icons клас)</label>
                                    <input type="text" name="contacts[email][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['icon'] ?? 'bi-envelope'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Линк (опционално)</label>
                                    <input type="text" name="contacts[email][link]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['email']['link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[email][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['email']['sort_order'] ?? 2); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[email][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['email']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Адрес -->
                        <div class="form-group mb-4">
                            <label class="form-label">Адрес</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Български</label>
                                    <textarea name="contacts[address][value_bg]" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address']['value_bg'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">English</label>
                                    <textarea name="contacts[address][value_en]" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address']['value_en'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deutsch</label>
                                    <textarea name="contacts[address][value_de]" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address']['value_de'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Русский</label>
                                    <textarea name="contacts[address][value_ru]" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address']['value_ru'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Икона (Bootstrap Icons клас)</label>
                                    <input type="text" name="contacts[address][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['address']['icon'] ?? 'bi-geo-alt'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Линк (опционално)</label>
                                    <input type="text" name="contacts[address][link]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['address']['link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[address][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['address']['sort_order'] ?? 3); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[address][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['address']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Работно време -->
                        <div class="form-group mb-4">
                            <label class="form-label">Работно време</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Български</label>
                                    <input type="text" name="contacts[working_hours][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['value_bg'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">English</label>
                                    <input type="text" name="contacts[working_hours][value_en]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['value_en'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deutsch</label>
                                    <input type="text" name="contacts[working_hours][value_de]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['value_de'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Русский</label>
                                    <input type="text" name="contacts[working_hours][value_ru]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['value_ru'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Икона (Bootstrap Icons клас)</label>
                                    <input type="text" name="contacts[working_hours][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['icon'] ?? 'bi-clock'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Линк (опционално)</label>
                                    <input type="text" name="contacts[working_hours][link]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['working_hours']['link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[working_hours][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['working_hours']['sort_order'] ?? 4); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[working_hours][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['working_hours']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Социални мрежи -->
                        <div class="form-group mb-4">
                            <label class="form-label">Социални мрежи</label>
                            
                            <!-- Facebook -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Facebook</label>
                                    <input type="text" name="contacts[facebook][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['facebook']['value_bg'] ?? ''); ?>"
                                           placeholder="Facebook URL">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Икона</label>
                                    <input type="text" name="contacts[facebook][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['facebook']['icon'] ?? 'bi-facebook'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[facebook][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['facebook']['sort_order'] ?? 5); ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[facebook][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['facebook']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <!-- Instagram -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Instagram</label>
                                    <input type="text" name="contacts[instagram][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['instagram']['value_bg'] ?? ''); ?>"
                                           placeholder="Instagram URL">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Икона</label>
                                    <input type="text" name="contacts[instagram][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['instagram']['icon'] ?? 'bi-instagram'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[instagram][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['instagram']['sort_order'] ?? 6); ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[instagram][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['instagram']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <!-- LinkedIn -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="text" name="contacts[linkedin][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['linkedin']['value_bg'] ?? ''); ?>"
                                           placeholder="LinkedIn URL">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Икона</label>
                                    <input type="text" name="contacts[linkedin][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['linkedin']['icon'] ?? 'bi-linkedin'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[linkedin][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['linkedin']['sort_order'] ?? 7); ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[linkedin][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['linkedin']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <!-- Twitter -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Twitter</label>
                                    <input type="text" name="contacts[twitter][value_bg]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['twitter']['value_bg'] ?? ''); ?>"
                                           placeholder="Twitter URL">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Икона</label>
                                    <input type="text" name="contacts[twitter][icon]" class="form-control" 
                                           value="<?php echo htmlspecialchars($contacts['twitter']['icon'] ?? 'bi-twitter'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Подредба</label>
                                    <input type="number" name="contacts[twitter][sort_order]" class="form-control" 
                                           value="<?php echo (int)($contacts['twitter']['sort_order'] ?? 8); ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Активен</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="contacts[twitter][is_active]" class="form-check-input" 
                                               value="1" <?php echo ($contacts['twitter']['is_active'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Запази контактите</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 

<?php
writeLog('Зареждане на footer.php');
require_once 'includes/footer.php';
writeLog('footer.php зареден успешно');
?> 