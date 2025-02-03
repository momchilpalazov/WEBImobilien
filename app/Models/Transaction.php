<?php

namespace App\Models;

use App\Models\Property;
use App\Models\Client;
use App\Models\User;

class Transaction
{
    public int $id;
    public string $type;
    public ?int $property_id;
    public ?int $client_id;
    public ?int $agent_id;
    public float $amount;
    public string $currency;
    public ?float $commission_rate;
    public ?float $commission_amount;
    public string $status;
    public ?string $description;
    public string $transaction_date;
    public ?string $due_date;
    public ?string $payment_method;
    public ?string $reference_number;
    public ?int $created_by;
    public string $created_at;
    public ?string $updated_at;

    // Relationships
    public ?Property $property;
    public ?Client $client;
    public ?User $agent;
    public ?User $creator;

    public function calculateCommission(): void
    {
        if ($this->commission_rate && ($this->type === 'sale' || $this->type === 'rent')) {
            $this->commission_amount = round($this->amount * ($this->commission_rate / 100), 2);
        }
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status !== 'pending') {
            return false;
        }
        return strtotime($this->due_date) < strtotime('today');
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getFormattedCommission(): ?string
    {
        if ($this->commission_amount === null) {
            return null;
        }
        return number_format($this->commission_amount, 2) . ' ' . $this->currency;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'pending' => 'bg-warning',
            default => 'bg-secondary'
        };
    }
} 
