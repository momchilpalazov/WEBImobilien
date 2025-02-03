<?php

namespace App\Services;

use App\Interfaces\ValidatorInterface;

class Validator implements ValidatorInterface
{
    private array $errors = [];
    private array $validData = [];
    private array $translations;
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
        $this->translations = $translationService->getTranslations();
    }

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->validData = [];

        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            $isValid = true;

            foreach ($rules as $rule) {
                if (str_contains($rule, ':')) {
                    [$ruleName, $parameter] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $parameter = null;
                }

                if (!$this->validateRule($field, $value, $ruleName, $parameter)) {
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                $this->validData[$field] = $value;
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValidData(): array
    {
        return $this->validData;
    }

    private function validateRule(string $field, mixed $value, string $rule, ?string $parameter): bool
    {
        return match ($rule) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'numeric' => $this->validateNumeric($field, $value),
            'min' => $this->validateMin($field, $value, (float) $parameter),
            'in' => $this->validateIn($field, $value, explode(',', $parameter)),
            default => true
        };
    }

    private function validateRequired(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            $this->errors[$field] = sprintf(
                $this->translations['validation']['required'] ?? '%s is required',
                $this->translations['fields'][$field] ?? $field
            );
            return false;
        }

        return true;
    }

    private function validateEmail(string $field, mixed $value): bool
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = sprintf(
                $this->translations['validation']['email'] ?? '%s must be a valid email address',
                $this->translations['fields'][$field] ?? $field
            );
            return false;
        }

        return true;
    }

    private function validateNumeric(string $field, mixed $value): bool
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = sprintf(
                $this->translations['validation']['numeric'] ?? '%s must be a number',
                $this->translations['fields'][$field] ?? $field
            );
            return false;
        }

        return true;
    }

    private function validateMin(string $field, mixed $value, float $min): bool
    {
        if ($value !== null && $value !== '' && (float) $value < $min) {
            $this->errors[$field] = sprintf(
                $this->translations['validation']['min'] ?? '%s must be at least %s',
                $this->translations['fields'][$field] ?? $field,
                $min
            );
            return false;
        }

        return true;
    }

    private function validateIn(string $field, mixed $value, array $allowedValues): bool
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowedValues)) {
            $this->errors[$field] = sprintf(
                $this->translations['validation']['in'] ?? '%s must be one of the allowed values',
                $this->translations['fields'][$field] ?? $field
            );
            return false;
        }

        return true;
    }
} 