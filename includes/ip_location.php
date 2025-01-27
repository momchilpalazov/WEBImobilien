<?php

function getClientIP() {
    $ip = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getCountryCodeByIP() {
    $ip = getClientIP();
    
    // За локално тестване
    if ($ip == '127.0.0.1' || $ip == '::1') {
        return 'BG';
    }
    
    // Използваме безплатно API за определяне на държавата
    $api_url = "http://ip-api.com/json/" . $ip;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['countryCode'])) {
            return strtoupper($data['countryCode']);
        }
    }
    
    // Ако има проблем с API-то, връщаме GB за английски по подразбиране
    return 'GB';
}

function getLanguageByCountryCode($country_code) {
    $country_language_map = [
        'BG' => 'bg',
        'DE' => 'de',
        'AT' => 'de',
        'CH' => 'de',
        'RU' => 'ru',
        'BY' => 'ru',
        'KZ' => 'ru'
    ];
    
    return isset($country_language_map[$country_code]) 
        ? $country_language_map[$country_code] 
        : 'en';
} 