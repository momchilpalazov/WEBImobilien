<?php
session_start();
require_once 'includes/config.php';
require 'vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

// Създаване на OAuth2 provider
$provider = new Azure([
    'clientId'     => OAUTH2_CLIENT_ID,
    'clientSecret' => OAUTH2_CLIENT_SECRET,
    'redirectUri'  => OAUTH2_REDIRECT_URI,
    'tenant'       => OAUTH2_TENANT_ID,
]);

try {
    // Проверка за грешки
    if (isset($_GET['error'])) {
        throw new Exception('Got error: ' . $_GET['error'] . ' - ' . $_GET['error_description']);
    }
    
    // Ако имаме код, получаваме access token
    if (isset($_GET['code'])) {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
            'resource' => 'https://outlook.office.com'
        ]);
        
        // Запазваме токена в сесията
        $_SESSION['oauth2_token'] = [
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires' => $token->getExpires(),
            'email' => SMTP_USERNAME
        ];
        
        // Пренасочване към контактната форма
        header('Location: contact.php?auth=success');
        exit;
    }
    
    // Ако нямаме код, пренасочваме към Microsoft за автентикация
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => OAUTH2_SCOPES,
        'resource' => 'https://outlook.office.com'
    ]);
    
    $_SESSION['oauth2_state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
    
} catch (Exception $e) {
    error_log('OAuth2 Error: ' . $e->getMessage());
    header('Location: contact.php?auth=error&message=' . urlencode($e->getMessage()));
    exit;
} 