<?php
require_once '../config/database.php';
require_once '../src/Database.php';

use App\Database;

header('Content-Type: application/json');

// Проверка за POST заявка
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Проверка на reCAPTCHA
$recaptcha_secret = "6LdtwroqAAAAAKV8RF0Ad0urxMW2rp9T2ooghhfN"; // Тук сложете вашия таен ключ
$recaptcha_response = $_POST['g-recaptcha-response'];

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$recaptcha_options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($recaptcha_data)
    ]
];

$recaptcha_context = stream_context_create($recaptcha_options);
$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
$recaptcha_json = json_decode($recaptcha_result, true);

if (!$recaptcha_json['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'reCAPTCHA verification failed'
    ]);
    exit;
}

// Валидация на входните данни
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8');

if (!$property_id || !$name || !$email || !$message) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Запис на запитването в базата данни
    $stmt = $db->prepare("
        INSERT INTO inquiries (property_id, name, email, phone, message, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$property_id, $name, $email, $phone, $message]);
    
    // Изпращане на имейл до администратора
    $to = "your@email.com"; // Сменете с вашия имейл
    $subject = "New Property Inquiry";
    $email_message = "
        New inquiry received:\n
        Property ID: {$property_id}\n
        Name: {$name}\n
        Email: {$email}\n
        Phone: {$phone}\n
        Message: {$message}
    ";
    
    $headers = "From: {$email}\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    mail($to, $subject, $email_message, $headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Your inquiry has been sent successfully'
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while sending your inquiry'
    ]);
} 