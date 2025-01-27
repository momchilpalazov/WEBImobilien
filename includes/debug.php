<?php

function debug_translations($translations) {
    // Проверка за липсващи преводи
    $missing_translations = [];
    $languages = array_keys($translations);
    $reference_lang = 'bg'; // Използваме българския като референтен език
    
    if (!isset($translations[$reference_lang])) {
        error_log("ERROR: Reference language '$reference_lang' not found in translations!");
        return;
    }
    
    // Рекурсивна функция за проверка на преводите
    function check_translations($ref_array, $lang_array, $path, &$missing) {
        foreach ($ref_array as $key => $value) {
            if (!isset($lang_array[$key])) {
                $missing[] = $path . $key;
            } elseif (is_array($value) && is_array($lang_array[$key])) {
                check_translations($value, $lang_array[$key], $path . $key . '.', $missing);
            }
        }
    }
    
    // Проверяваме всеки език спрямо референтния
    foreach ($languages as $lang) {
        if ($lang === $reference_lang) continue;
        
        $missing_translations[$lang] = [];
        check_translations(
            $translations[$reference_lang],
            $translations[$lang],
            '',
            $missing_translations[$lang]
        );
        
        if (!empty($missing_translations[$lang])) {
            error_log("Missing translations for language '$lang':");
            foreach ($missing_translations[$lang] as $path) {
                error_log("  - $path");
            }
        }
    }
    
    return $missing_translations;
}

// Функция за проверка на дублирани преводи
function check_duplicate_translations($translations) {
    $duplicates = [];
    
    foreach ($translations as $lang => $trans) {
        $flat_trans = [];
        flatten_array($trans, '', $flat_trans);
        
        $values = array_values($flat_trans);
        $duplicates[$lang] = array_filter(
            array_count_values($values),
            function($count) { return $count > 1; }
        );
    }
    
    foreach ($duplicates as $lang => $dupes) {
        if (!empty($dupes)) {
            error_log("Duplicate translations found in language '$lang':");
            foreach ($dupes as $value => $count) {
                error_log("  - '$value' appears $count times");
            }
        }
    }
    
    return $duplicates;
}

// Помощна функция за изглаждане на масив
function flatten_array($array, $prefix = '', &$result = []) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            flatten_array($value, $prefix . $key . '.', $result);
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
}

// Функция за зареждане на преводи
function load_translations() {
    global $translations;
    
    // Зареждаме основните преводи
    require_once 'translations.php';
    
    // Проверяваме за проблеми
    $missing = debug_translations($translations);
    $duplicates = check_duplicate_translations($translations);
    
    // Логваме резултатите
    if (empty($missing) && empty($duplicates)) {
        error_log("All translations are complete and unique!");
    }
    
    return $translations;
} 