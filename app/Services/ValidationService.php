<?php

namespace App\Services;

class ValidationService
{
    private array $errors = [];
    private array $translations;

    public function __construct(TranslationService $translationService)
    {
        $this->translations = $translationService->getTranslations(CURRENT_LANGUAGE);
    }

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            
            foreach ($fieldRules as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $parameter] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $parameter = null;
                }

                if (!$this->validateRule($data[$field] ?? null, $ruleName, $parameter, $field)) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isNumeric($value): bool
    {
        return is_numeric($value);
    }

    private function validateRule($value, string $rule, ?string $parameter, string $field): bool
    {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field] = sprintf(
                        $this->translations['validation']['required'],
                        $this->translations['fields'][$field] ?? $field
                    );
                    return false;
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = sprintf(
                        $this->translations['validation']['email'],
                        $this->translations['fields'][$field] ?? $field
                    );
                    return false;
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = sprintf(
                        $this->translations['validation']['numeric'],
                        $this->translations['fields'][$field] ?? $field
                    );
                    return false;
                }
                break;

            case 'min':
                if (!empty($value) && $value < $parameter) {
                    $this->errors[$field] = sprintf(
                        $this->translations['validation']['min'],
                        $this->translations['fields'][$field] ?? $field,
                        $parameter
                    );
                    return false;
                }
                break;

            case 'in':
                $allowedValues = explode(',', $parameter);
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->errors[$field] = sprintf(
                        $this->translations['validation']['in'],
                        $this->translations['fields'][$field] ?? $field
                    );
                    return false;
                }
                break;
        }

        return true;
    }
} 