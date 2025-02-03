<?php

namespace App\Interfaces;

interface AuthInterface
{
    /**
     * Опит за автентикация на потребител
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function attempt(string $email, string $password): bool;

    /**
     * Проверка дали потребителят е автентикиран
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Връща текущия потребител
     *
     * @return array|null
     */
    public function user(): ?array;

    /**
     * Изход от системата
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Проверка дали потребителят има определена роля
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool;
} 