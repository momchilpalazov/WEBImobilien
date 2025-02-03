<?php

namespace App\Models;

class Agent
{
    public int $id;
    public string $name;
    public string $email;
    public string $phone;
    public bool $active;
    public ?string $created_at;
    public ?string $updated_at;
} 