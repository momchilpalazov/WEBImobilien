<?php

namespace App\Services;

use DateTime;
use Exception;

class ClientService
{
    private $clientRepository;
    private $propertyRepository;
    private $notificationService;
    private $interactionRepository;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        PropertyRepositoryInterface $propertyRepository,
        NotificationService $notificationService,
        InteractionRepositoryInterface $interactionRepository
    ) {
        $this->clientRepository = $clientRepository;
        $this->propertyRepository = $propertyRepository;
        $this->notificationService = $notificationService;
        $this->interactionRepository = $interactionRepository;
    }

    public function createClient(array $data): int
    {
        try {
            $this->validateClientData($data);

            // Проверка за съществуващ клиент
            if ($this->clientRepository->findByEmail($data['email'])) {
                throw new Exception('Клиент с този имейл вече съществува.');
            }

            if ($this->clientRepository->findByPhone($data['phone'])) {
                throw new Exception('Клиент с този телефон вече съществува.');
            }

            // Създаване на клиента
            $clientId = $this->clientRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'type' => $data['type'] ?? 'buyer', // buyer, seller, tenant, landlord
                'status' => 'active',
                'source' => $data['source'] ?? null,
                'notes' => $data['notes'] ?? null,
                'preferences' => $this->formatPreferences($data['preferences'] ?? []),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Записване на първото взаимодействие
            $this->logInteraction($clientId, 'create', 'Създаден нов клиент');

            // Изпращане на приветствено съобщение
            $this->notificationService->sendWelcomeMessage([
                'client_id' => $clientId,
                'email' => $data['email'],
                'name' => $data['name']
            ]);

            return $clientId;

        } catch (Exception $e) {
            error_log("Error creating client: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateClient(int $clientId, array $data): bool
    {
        try {
            $client = $this->clientRepository->find($clientId);
            if (!$client) {
                throw new Exception('Клиентът не е намерен.');
            }

            // Проверка за уникален имейл и телефон
            if (isset($data['email']) && $data['email'] !== $client['email']) {
                if ($this->clientRepository->findByEmail($data['email'])) {
                    throw new Exception('Клиент с този имейл вече съществува.');
                }
            }

            if (isset($data['phone']) && $data['phone'] !== $client['phone']) {
                if ($this->clientRepository->findByPhone($data['phone'])) {
                    throw new Exception('Клиент с този телефон вече съществува.');
                }
            }

            // Актуализиране на предпочитанията
            if (isset($data['preferences'])) {
                $data['preferences'] = $this->formatPreferences($data['preferences']);
            }

            // Актуализиране на данните
            $this->clientRepository->update($clientId, array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]));

            // Записване на взаимодействието
            $this->logInteraction($clientId, 'update', 'Актуализирани данни на клиента');

            return true;

        } catch (Exception $e) {
            error_log("Error updating client: " . $e->getMessage());
            throw $e;
        }
    }

    public function logInteraction(int $clientId, string $type, string $description, array $data = []): int
    {
        return $this->interactionRepository->create([
            'client_id' => $clientId,
            'type' => $type,
            'description' => $description,
            'data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
    }

    public function getClientHistory(int $clientId): array
    {
        return $this->interactionRepository->findByClient($clientId);
    }

    public function findMatchingProperties(int $clientId): array
    {
        $client = $this->clientRepository->find($clientId);
        if (!$client || empty($client['preferences'])) {
            return [];
        }

        $preferences = json_decode($client['preferences'], true);
        return $this->propertyRepository->findMatching($preferences);
    }

    public function updatePreferences(int $clientId, array $preferences): bool
    {
        try {
            $client = $this->clientRepository->find($clientId);
            if (!$client) {
                throw new Exception('Клиентът не е намерен.');
            }

            $formattedPreferences = $this->formatPreferences($preferences);
            
            $this->clientRepository->update($clientId, [
                'preferences' => $formattedPreferences,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->logInteraction($clientId, 'preferences', 'Актуализирани предпочитания за имоти');

            // Намиране на подходящи имоти и изпращане на известие
            $matchingProperties = $this->findMatchingProperties($clientId);
            if (!empty($matchingProperties)) {
                $this->notificationService->sendPropertyMatches([
                    'client_id' => $clientId,
                    'email' => $client['email'],
                    'name' => $client['name'],
                    'properties' => $matchingProperties
                ]);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error updating client preferences: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateClientData(array $data): void
    {
        $required = ['name', 'email', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Полето '{$field}' е задължително.");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Невалиден имейл адрес.');
        }

        // Валидация на телефонен номер (опростена)
        if (!preg_match('/^[0-9+\s-]{8,15}$/', $data['phone'])) {
            throw new Exception('Невалиден телефонен номер.');
        }
    }

    private function formatPreferences(array $preferences): string
    {
        $formatted = [
            'property_type' => $preferences['property_type'] ?? [],
            'price_range' => [
                'min' => $preferences['price_min'] ?? null,
                'max' => $preferences['price_max'] ?? null
            ],
            'area_range' => [
                'min' => $preferences['area_min'] ?? null,
                'max' => $preferences['area_max'] ?? null
            ],
            'locations' => $preferences['locations'] ?? [],
            'features' => $preferences['features'] ?? [],
            'transaction_type' => $preferences['transaction_type'] ?? 'any', // buy, rent, any
            'notifications_enabled' => $preferences['notifications_enabled'] ?? true
        ];

        return json_encode($formatted);
    }
} 