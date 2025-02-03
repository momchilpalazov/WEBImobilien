<?php
class Security {
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
} 