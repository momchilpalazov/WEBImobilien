<?php

namespace App\Services;

class TranslationService
{
    private string $currentLanguage;
    private array $translations = [];
    private array $validLanguages = ['bg', 'en', 'de', 'ru'];
    private string $defaultLanguage = 'bg';

    public function __construct()
    {
        $this->currentLanguage = $this->defaultLanguage;
        $this->loadTranslations();
    }

    public function setLanguage(string $language): void
    {
        if (!in_array($language, $this->validLanguages)) {
            $language = $this->defaultLanguage;
        }
        
        if ($language !== $this->currentLanguage) {
            $this->currentLanguage = $language;
            $this->loadTranslations();
        }
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function translate(string $key, array $params = []): string
    {
        $segments = explode('.', $key);
        $current = $this->translations;

        foreach ($segments as $segment) {
            if (!isset($current[$segment])) {
                return $key;
            }
            $current = $current[$segment];
        }

        if (!is_string($current)) {
            return $key;
        }

        return $this->replaceParameters($current, $params);
    }

    private function replaceParameters(string $text, array $params): string
    {
        foreach ($params as $param => $value) {
            $text = str_replace(':' . $param, $value, $text);
        }
        return $text;
    }

    private function loadTranslations(): void
    {
        $this->translations = [];
        
        // Зареждане на общите преводи
        $commonPath = __DIR__ . '/../../languages/' . $this->currentLanguage . '/common.php';
        if (file_exists($commonPath)) {
            $commonTranslations = require $commonPath;
            if (is_array($commonTranslations)) {
                $this->translations = array_merge($this->translations, $commonTranslations);
            }
        }

        // Зареждане на преводите за имоти
        $propertiesPath = __DIR__ . '/../../languages/' . $this->currentLanguage . '/properties.php';
        if (file_exists($propertiesPath)) {
            $propertyTranslations = require $propertiesPath;
            if (is_array($propertyTranslations)) {
                $this->translations = array_merge($this->translations, $propertyTranslations);
            }
        }
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
} 