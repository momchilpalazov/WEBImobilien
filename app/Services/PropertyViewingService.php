<?php

namespace App\Services;

use DateTime;
use Exception;

class PropertyViewingService
{
    private $propertyRepository;
    private $agentRepository;
    private $viewingRepository;
    private $notificationService;
    private $calendarService;

    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        AgentRepositoryInterface $agentRepository,
        ViewingRepositoryInterface $viewingRepository,
        NotificationService $notificationService,
        CalendarService $calendarService
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->agentRepository = $agentRepository;
        $this->viewingRepository = $viewingRepository;
        $this->notificationService = $notificationService;
        $this->calendarService = $calendarService;
    }

    public function scheduleViewing(array $data): int
    {
        try {
            $this->validateViewingData($data);
            
            // Проверка дали имотът съществува и е активен
            $property = $this->propertyRepository->find($data['property_id']);
            if (!$property || $property['status'] !== 'active') {
                throw new Exception('Имотът не е наличен за огледи.');
            }

            // Проверка за наличност в избрания времеви слот
            if (!$this->isTimeSlotAvailable($data['date'], $data['time'], $data['property_id'])) {
                throw new Exception('Избраният час не е наличен. Моля, изберете друг час.');
            }

            // Намиране на свободен агент
            $agent = $this->findAvailableAgent($data['date'], $data['time']);
            if (!$agent) {
                throw new Exception('Няма свободни агенти за избрания час. Моля, изберете друг час.');
            }

            // Създаване на огледа
            $viewingId = $this->viewingRepository->create([
                'property_id' => $data['property_id'],
                'agent_id' => $agent['id'],
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'client_email' => $data['client_email'],
                'date' => $data['date'],
                'time' => $data['time'],
                'notes' => $data['notes'] ?? '',
                'status' => 'scheduled'
            ]);

            // Добавяне в календара на агента
            $this->calendarService->addEvent([
                'title' => "Оглед на имот #{$property['id']}",
                'description' => "Оглед с {$data['client_name']}\nТел: {$data['client_phone']}",
                'start' => "{$data['date']} {$data['time']}",
                'duration' => 60, // 1 час
                'agent_id' => $agent['id'],
                'viewing_id' => $viewingId
            ]);

            // Изпращане на потвърждения
            $this->sendConfirmations($viewingId, $property, $agent, $data);

            return $viewingId;

        } catch (Exception $e) {
            error_log("Error scheduling viewing: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelViewing(int $viewingId, string $reason = ''): bool
    {
        try {
            $viewing = $this->viewingRepository->find($viewingId);
            if (!$viewing) {
                throw new Exception('Огледът не е намерен.');
            }

            if ($viewing['status'] === 'cancelled') {
                throw new Exception('Огледът вече е отказан.');
            }

            if ($viewing['status'] === 'completed') {
                throw new Exception('Не може да откажете оглед, който вече е проведен.');
            }

            // Актуализиране на статуса
            $this->viewingRepository->update($viewingId, [
                'status' => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_at' => date('Y-m-d H:i:s')
            ]);

            // Премахване от календара
            $this->calendarService->removeEvent($viewing['calendar_event_id']);

            // Изпращане на известия
            $this->sendCancellationNotifications($viewing, $reason);

            return true;

        } catch (Exception $e) {
            error_log("Error cancelling viewing: " . $e->getMessage());
            throw $e;
        }
    }

    public function completeViewing(int $viewingId, array $feedback = []): bool
    {
        try {
            $viewing = $this->viewingRepository->find($viewingId);
            if (!$viewing) {
                throw new Exception('Огледът не е намерен.');
            }

            if ($viewing['status'] !== 'scheduled') {
                throw new Exception('Огледът не може да бъде маркиран като проведен.');
            }

            // Актуализиране на статуса и добавяне на обратна връзка
            $this->viewingRepository->update($viewingId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'client_feedback' => $feedback['client_feedback'] ?? null,
                'agent_notes' => $feedback['agent_notes'] ?? null,
                'client_interest_level' => $feedback['client_interest_level'] ?? null
            ]);

            // Актуализиране на събитието в календара
            $this->calendarService->updateEvent($viewing['calendar_event_id'], [
                'status' => 'completed'
            ]);

            // Изпращане на анкета за обратна връзка
            if (!empty($viewing['client_email'])) {
                $this->notificationService->sendFeedbackRequest($viewing);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error completing viewing: " . $e->getMessage());
            throw $e;
        }
    }

    public function rescheduleViewing(int $viewingId, array $newData): bool
    {
        try {
            $viewing = $this->viewingRepository->find($viewingId);
            if (!$viewing) {
                throw new Exception('Огледът не е намерен.');
            }

            if ($viewing['status'] !== 'scheduled') {
                throw new Exception('Само насрочени огледи могат да бъдат пренасрочени.');
            }

            // Проверка за наличност в новия времеви слот
            if (!$this->isTimeSlotAvailable($newData['date'], $newData['time'], $viewing['property_id'], $viewingId)) {
                throw new Exception('Избраният час не е наличен. Моля, изберете друг час.');
            }

            // Намиране на свободен агент
            $agent = $this->findAvailableAgent($newData['date'], $newData['time']);
            if (!$agent) {
                throw new Exception('Няма свободни агенти за избрания час. Моля, изберете друг час.');
            }

            // Актуализиране на огледа
            $this->viewingRepository->update($viewingId, [
                'date' => $newData['date'],
                'time' => $newData['time'],
                'agent_id' => $agent['id'],
                'rescheduled_at' => date('Y-m-d H:i:s')
            ]);

            // Актуализиране на събитието в календара
            $this->calendarService->updateEvent($viewing['calendar_event_id'], [
                'start' => "{$newData['date']} {$newData['time']}",
                'agent_id' => $agent['id']
            ]);

            // Изпращане на известия
            $this->sendRescheduleNotifications($viewing, $newData);

            return true;

        } catch (Exception $e) {
            error_log("Error rescheduling viewing: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateViewingData(array $data): void
    {
        $required = ['property_id', 'client_name', 'client_phone', 'date', 'time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Полето '{$field}' е задължително.");
            }
        }

        // Валидация на датата и часа
        $dateTime = new DateTime("{$data['date']} {$data['time']}");
        $now = new DateTime();

        // Минимум 2 часа предварително
        $minTime = (new DateTime())->modify('+2 hours');
        if ($dateTime < $minTime) {
            throw new Exception('Огледът трябва да бъде насрочен поне 2 часа предварително.');
        }

        // Максимум 30 дни напред
        $maxTime = (new DateTime())->modify('+30 days');
        if ($dateTime > $maxTime) {
            throw new Exception('Огледът не може да бъде насрочен за повече от 30 дни напред.');
        }

        // Проверка за работно време (9:00 - 18:00)
        $hour = (int)$dateTime->format('H');
        if ($hour < 9 || $hour >= 18) {
            throw new Exception('Огледите се провеждат между 9:00 и 18:00 часа.');
        }

        // Проверка за почивни дни
        if ($dateTime->format('N') >= 6) {
            throw new Exception('Огледи не се провеждат през почивните дни.');
        }
    }

    private function isTimeSlotAvailable(string $date, string $time, int $propertyId, ?int $excludeViewingId = null): bool
    {
        $existingViewings = $this->viewingRepository->findByDateTime($date, $time);

        foreach ($existingViewings as $viewing) {
            if ($viewing['id'] === $excludeViewingId) {
                continue;
            }

            // Проверка за припокриване с други огледи на същия имот
            if ($viewing['property_id'] === $propertyId) {
                return false;
            }

            // Проверка за припокриване с други огледи в рамките на 1 час
            $viewingTime = strtotime("{$viewing['date']} {$viewing['time']}");
            $requestedTime = strtotime("$date $time");
            
            if (abs($viewingTime - $requestedTime) < 3600) {
                return false;
            }
        }

        return true;
    }

    private function findAvailableAgent(string $date, string $time): ?array
    {
        $agents = $this->agentRepository->findAll();
        $dateTime = "{$date} {$time}";

        foreach ($agents as $agent) {
            if ($this->calendarService->isAgentAvailable($agent['id'], $dateTime)) {
                return $agent;
            }
        }

        return null;
    }

    private function sendConfirmations(int $viewingId, array $property, array $agent, array $data): void
    {
        // Изпращане на имейл до клиента
        if (!empty($data['client_email'])) {
            $this->notificationService->sendClientConfirmation([
                'viewing_id' => $viewingId,
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'property_title' => $property['title'],
                'property_address' => $property['address'],
                'date' => $data['date'],
                'time' => $data['time'],
                'agent_name' => $agent['name'],
                'agent_phone' => $agent['phone']
            ]);
        }

        // Изпращане на SMS до клиента
        if (!empty($data['client_phone'])) {
            $this->notificationService->sendClientSms([
                'phone' => $data['client_phone'],
                'message' => "Потвърден оглед на {$property['title']} на {$data['date']} в {$data['time']}. Агент: {$agent['name']}, тел: {$agent['phone']}"
            ]);
        }

        // Известие до агента
        $this->notificationService->notifyAgent([
            'agent_id' => $agent['id'],
            'type' => 'new_viewing',
            'data' => [
                'viewing_id' => $viewingId,
                'property_id' => $property['id'],
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'date' => $data['date'],
                'time' => $data['time']
            ]
        ]);
    }

    private function sendCancellationNotifications(array $viewing, string $reason): void
    {
        $property = $this->propertyRepository->find($viewing['property_id']);
        $agent = $this->agentRepository->find($viewing['agent_id']);

        // Известие до клиента
        if (!empty($viewing['client_email'])) {
            $this->notificationService->sendClientCancellation([
                'client_email' => $viewing['client_email'],
                'client_name' => $viewing['client_name'],
                'property_title' => $property['title'],
                'date' => $viewing['date'],
                'time' => $viewing['time'],
                'reason' => $reason
            ]);
        }

        // SMS до клиента
        if (!empty($viewing['client_phone'])) {
            $this->notificationService->sendClientSms([
                'phone' => $viewing['client_phone'],
                'message' => "Отменен оглед на {$property['title']} на {$viewing['date']} в {$viewing['time']}."
            ]);
        }

        // Известие до агента
        $this->notificationService->notifyAgent([
            'agent_id' => $agent['id'],
            'type' => 'viewing_cancelled',
            'data' => [
                'viewing_id' => $viewing['id'],
                'property_id' => $property['id'],
                'client_name' => $viewing['client_name'],
                'date' => $viewing['date'],
                'time' => $viewing['time'],
                'reason' => $reason
            ]
        ]);
    }

    private function sendRescheduleNotifications(array $viewing, array $newData): void
    {
        $property = $this->propertyRepository->find($viewing['property_id']);
        $agent = $this->agentRepository->find($viewing['agent_id']);

        // Известие до клиента
        if (!empty($viewing['client_email'])) {
            $this->notificationService->sendClientReschedule([
                'client_email' => $viewing['client_email'],
                'client_name' => $viewing['client_name'],
                'property_title' => $property['title'],
                'old_date' => $viewing['date'],
                'old_time' => $viewing['time'],
                'new_date' => $newData['date'],
                'new_time' => $newData['time'],
                'agent_name' => $agent['name'],
                'agent_phone' => $agent['phone']
            ]);
        }

        // SMS до клиента
        if (!empty($viewing['client_phone'])) {
            $this->notificationService->sendClientSms([
                'phone' => $viewing['client_phone'],
                'message' => "Пренасрочен оглед на {$property['title']} за {$newData['date']} в {$newData['time']}. Агент: {$agent['name']}, тел: {$agent['phone']}"
            ]);
        }

        // Известие до агента
        $this->notificationService->notifyAgent([
            'agent_id' => $agent['id'],
            'type' => 'viewing_rescheduled',
            'data' => [
                'viewing_id' => $viewing['id'],
                'property_id' => $property['id'],
                'client_name' => $viewing['client_name'],
                'old_date' => $viewing['date'],
                'old_time' => $viewing['time'],
                'new_date' => $newData['date'],
                'new_time' => $newData['time']
            ]
        ]);
    }
} 
