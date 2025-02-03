<?php

namespace App\Services;

use PDO;

class Auth
{
    private PDO $db;
    private ?array $user = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->loadUser();
    }

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $this->user = $user;
            return true;
        }

        return false;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user ? $this->user['id'] : null;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        $this->user = null;
    }

    public function hasRole(string $role): bool
    {
        return $this->user && $this->user['role'] === $role;
    }

    private function loadUser(): void
    {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->user) {
                unset($_SESSION['user_id']);
            }
        }
    }
} 