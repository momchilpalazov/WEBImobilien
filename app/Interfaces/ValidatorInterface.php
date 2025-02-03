<?php

namespace App\Interfaces;

interface ValidatorInterface
{
    /**
     * Validate data against rules
     * 
     * @param array $data
     * @param array $rules
     * @return bool
     */
    public function validate(array $data, array $rules): bool;

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array;

    /**
     * Get validated data
     * 
     * @return array
     */
    public function getValidData(): array;
} 
