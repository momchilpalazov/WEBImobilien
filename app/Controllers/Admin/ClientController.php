<?php

namespace App\Controllers\Admin;

use App\Services\ClientService;

class ClientController extends BaseAdminController
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        parent::__construct();
        $this->clientService = $clientService;
    }

    public function index(): void
    {
        $filter = $_GET['filter'] ?? [];
        $clients = $this->clientService->getClients($filter);
        
        $this->render('admin/clients/index', [
            'clients' => $clients,
            'filter' => $filter
        ]);
    }

    public function create(): void
    {
        if (!$this->isPost()) {
            $this->render('admin/clients/create');
            return;
        }

        try {
            $data = $this->sanitizeInput($_POST);
            $clientId = $this->clientService->createClient($data);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Клиентът е създаден успешно.',
                    'client_id' => $clientId
                ]);
                return;
            }

            $this->setSuccess('Клиентът е създаден успешно.');
            $this->redirect("/admin/clients/{$clientId}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/clients/create');
        }
    }

    public function edit(int $id): void
    {
        $client = $this->clientService->getClient($id);
        if (!$client) {
            $this->setError('Клиентът не е намерен.');
            $this->redirect('/admin/clients');
            return;
        }

        if (!$this->isPost()) {
            $this->render('admin/clients/edit', [
                'client' => $client
            ]);
            return;
        }

        try {
            $data = $this->sanitizeInput($_POST);
            $this->clientService->updateClient($id, $data);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Данните са актуализирани успешно.'
                ]);
                return;
            }

            $this->setSuccess('Данните са актуализирани успешно.');
            $this->redirect("/admin/clients/{$id}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect("/admin/clients/{$id}/edit");
        }
    }

    public function show(int $id): void
    {
        $client = $this->clientService->getClient($id);
        if (!$client) {
            $this->setError('Клиентът не е намерен.');
            $this->redirect('/admin/clients');
            return;
        }

        $history = $this->clientService->getClientHistory($id);
        $matchingProperties = $this->clientService->findMatchingProperties($id);

        $this->render('admin/clients/show', [
            'client' => $client,
            'history' => $history,
            'matchingProperties' => $matchingProperties
        ]);
    }

    public function updatePreferences(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect("/admin/clients/{$id}");
            return;
        }

        try {
            $preferences = $this->sanitizeInput($_POST['preferences'] ?? []);
            $this->clientService->updatePreferences($id, $preferences);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Предпочитанията са актуализирани успешно.'
                ]);
                return;
            }

            $this->setSuccess('Предпочитанията са актуализирани успешно.');
            $this->redirect("/admin/clients/{$id}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect("/admin/clients/{$id}");
        }
    }

    public function logInteraction(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect("/admin/clients/{$id}");
            return;
        }

        try {
            $data = $this->sanitizeInput($_POST);
            $this->clientService->logInteraction(
                $id,
                $data['type'],
                $data['description'],
                $data['additional_data'] ?? []
            );

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Взаимодействието е записано успешно.'
                ]);
                return;
            }

            $this->setSuccess('Взаимодействието е записано успешно.');
            $this->redirect("/admin/clients/{$id}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect("/admin/clients/{$id}");
        }
    }

    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
} 