<?php
session_start();
require_once 'includes/config.php'; // За достъп до конфигурационни настройки
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Създаване на лог директория ако не съществува
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

// Функция за логване
function writeLog($message, $type = 'info') {
    $date = date('Y-m-d H:i:s');
    $logFile = 'logs/contact_form_' . date('Y-m-d') . '.log';
    $logMessage = "[$date][$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

writeLog('Започва обработка на формата');

// Проверка дали формата е изпратена
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog('Невалиден метод на заявка: ' . $_SERVER['REQUEST_METHOD'], 'error');
    $_SESSION['contact_error'] = 'Невалиден метод на заявка.';
    header('Location: contact.php');
    exit;
}

// Вземане на данните от формата
$name = trim(strip_tags($_POST['name'] ?? ''));
$email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
$phone = trim(strip_tags($_POST['phone'] ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

writeLog("Получени данни: Име=$name, Имейл=$email, Телефон=$phone, Тема=$subject");

// Валидация на входните данни
$errors = [];

if (empty($name)) {
    $errors[] = 'Моля, въведете вашето име.';
}

if (empty($email)) {
    $errors[] = 'Моля, въведете вашия имейл адрес.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Моля, въведете валиден имейл адрес.';
}

if (empty($message)) {
    $errors[] = 'Моля, въведете вашето съобщение.';
}

if (!empty($errors)) {
    writeLog('Грешки при валидация: ' . implode(', ', $errors), 'error');
}

// Проверка за reCAPTCHA
$recaptcha_secret = RECAPTCHA_SECRET_KEY;
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptcha_response)) {
    writeLog('Липсва reCAPTCHA отговор', 'error');
    $errors[] = 'Моля, потвърдете, че не сте робот.';
} else {
    writeLog('Проверка на reCAPTCHA');
    $verify_response = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . 
        '&response=' . $recaptcha_response
    );
    
    $response_data = json_decode($verify_response);
    writeLog('reCAPTCHA отговор: ' . $verify_response);
    
    if (!$response_data->success) {
        writeLog('Неуспешна reCAPTCHA проверка', 'error');
        $errors[] = 'Неуспешна проверка на reCAPTCHA. Моля, опитайте отново.';
    }
}

// Ако има грешки, връщаме към формата
if (!empty($errors)) {
    $_SESSION['contact_error'] = implode('<br>', $errors);
    $_SESSION['form_data'] = $_POST;
    header('Location: contact.php');
    exit;
}

// Превод на темите
$subject_translations = [
    'inquiry' => 'Запитване за имот',
    'service' => 'Услуги',
    'partnership' => 'Партньорство',
    'other' => 'Друго'
];

$subject_text = $subject_translations[$subject] ?? 'Запитване от контактната форма';

// Функция за изпращане на имейл чрез PHPMailer с OAuth2
function sendEmailWithPHPMailer($to, $subject, $body, $fromName, $fromEmail, $replyTo = null) {
    $mail = new PHPMailer(true);
    
    try {
        // Проверка за OAuth2 токен
        if (!isset($_SESSION['oauth2_token'])) {
            writeLog('Липсва OAuth2 токен, пренасочване към автентикация', 'info');
            header('Location: oauth_callback.php');
            exit;
        }

        // Дебъг настройки
        $mail->SMTPDebug = SMTP_DEBUG;
        $mail->Debugoutput = function($str, $level) {
            writeLog("SMTP Debug: $str", 'debug');
        };

        // Настройки на сървъра
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->AuthType = 'XOAUTH2';
        $mail->Username = SMTP_USERNAME;
        $mail->Password = base64_encode(json_encode([
            'client_id' => OAUTH2_CLIENT_ID,
            'client_secret' => OAUTH2_CLIENT_SECRET,
            'grant_type' => 'refresh_token',
            'refresh_token' => $_SESSION['oauth2_token']['refresh_token']
        ]));
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        // Настройки на имейла
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(NOREPLY_EMAIL, $fromName);
        $mail->addAddress($to);
        
        if ($replyTo) {
            $mail->addReplyTo($replyTo['email'], $replyTo['name']);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Логване на настройките преди изпращане
        writeLog('SMTP настройки: ' . json_encode([
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'secure' => SMTP_SECURE,
            'auth' => 'XOAUTH2',
            'username' => SMTP_USERNAME,
            'from' => NOREPLY_EMAIL,
            'to' => $to
        ]));

        // Изпращане
        $result = $mail->send();
        writeLog($result ? 'Имейлът е изпратен успешно' : 'Грешка при изпращане на имейла');
        return $result;
    } catch (Exception $e) {
        writeLog('PHPMailer грешка: ' . $mail->ErrorInfo, 'error');
        writeLog('Exception: ' . $e->getMessage(), 'error');
        
        // Ако токенът е изтекъл, пренасочваме към нова автентикация
        if (strpos($e->getMessage(), 'OAuth') !== false) {
            unset($_SESSION['oauth2_token']);
            header('Location: oauth_callback.php');
            exit;
        }
        
        return false;
    }
}

// Съставяне на имейл съобщението
$email_message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Ново запитване от контактната форма</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
        <h2 style='color: #0056b3;'>Ново запитване от контактната форма</h2>
        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
            <p><strong>Име:</strong> {$name}</p>
            <p><strong>Имейл:</strong> {$email}</p>
            <p><strong>Телефон:</strong> {$phone}</p>
            <p><strong>Тема:</strong> {$subject_text}</p>
            <p><strong>Съобщение:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        </div>
    </div>
</body>
</html>";

try {
    // Изпращане на основния имейл
    $mainEmailSent = sendEmailWithPHPMailer(
        ADMIN_EMAIL,
        "Ново запитване: " . $subject_text,
        $email_message,
        COMPANY_NAME,
        NOREPLY_EMAIL,
        ['email' => $email, 'name' => $name]
    );
    
    if ($mainEmailSent) {
        writeLog('Основният имейл е изпратен успешно');
        
        // Изпращане на потвърждение
        $confirmation_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Потвърждение за получено запитване</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #0056b3;'>Здравейте {$name},</h2>
                <p>Благодарим ви за вашето запитване. Ще се свържем с вас възможно най-скоро.</p>
                <p>С уважение,<br>Екипът на Industrial Properties</p>
            </div>
        </body>
        </html>";
        
        $confirmationSent = sendEmailWithPHPMailer(
            $email,
            "Потвърждение за получено запитване",
            $confirmation_message,
            COMPANY_NAME,
            NOREPLY_EMAIL
        );
        
        if ($confirmationSent) {
            writeLog('Потвърждението е изпратено успешно');
        } else {
            writeLog('Грешка при изпращане на потвърждението', 'error');
        }
        
        $_SESSION['contact_success'] = 'Благодарим ви! Вашето съобщение беше изпратено успешно.';
    } else {
        throw new Exception('Failed to send main email');
    }
} catch (Exception $e) {
    writeLog('Грешка при изпращане на имейл: ' . $e->getMessage(), 'error');
    $_SESSION['contact_error'] = 'Възникна грешка при изпращане на съобщението. Моля, опитайте отново или се свържете с нас по телефона.';
}

writeLog('Край на обработката на формата');

// Пренасочване обратно към контактната форма
header('Location: contact.php');
exit;
?> 