<?php

namespace App\Controllers;

use App\Interfaces\ClientManagementInterface;
use App\Services\ClientManagementService;
use PDO;

class ClientController extends BaseController
{
    private $clientService;
    private $db;

    public function __construct(ClientManagementInterface $clientService, PDO $db)
    {
        parent::__construct();
        $this->clientService = $clientService;
        $this->db = $db;
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $clients = $this->clientService->getAllClients($filters);
        
        $this->view('clients/index', [
            'clients' => $clients,
            'filters' => $filters
        ]);
    }

    protected function view(string $name, array $data = []): void
    {
        parent::view($name, $data);
    }

    public function details(int $id): void
    {
        $client = $this->clientService->getClientById($id);
        if (!$client) {
            $this->redirect('/clients');
        }

        $preferences = $this->clientService->getClientPreferences($id);
        $interactions = $this->clientService->getInteractions($id);
        $matches = $this->clientService->getPropertyMatches($id);
        $documents = $this->clientService->getClientDocuments($id);

        $this->view('clients/view', [
            'client' => $client,
            'preferences' => $preferences,
            'interactions' => $interactions,
            'matches' => $matches,
            'documents' => $documents
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'status' => $_POST['status'] ?? 'potential',
                'source' => $_POST['source'] ?? null
            ];

            $clientId = $this->clientService->createClient($data);
            
            if ($clientId) {
                // If preferences were submitted
                if (!empty($_POST['preferences'])) {
                    $this->clientService->updateClientPreferences($clientId, $_POST['preferences']);
                }
                
                $this->redirect("/clients/view/{$clientId}");
            }
        }

        $this->view('clients/create');
    }

    public function edit(int $id): void
    {
        $client = $this->clientService->getClientById($id);
        if (!$client) {
            $this->redirect('/clients');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? $client['first_name'],
                'last_name' => $_POST['last_name'] ?? $client['last_name'],
                'email' => $_POST['email'] ?? $client['email'],
                'phone' => $_POST['phone'] ?? $client['phone'],
                'status' => $_POST['status'] ?? $client['status'],
                'source' => $_POST['source'] ?? $client['source']
            ];

            if ($this->clientService->updateClient($id, $data)) {
                // If preferences were submitted
                if (!empty($_POST['preferences'])) {
                    $this->clientService->updateClientPreferences($id, $_POST['preferences']);
                }
                
                $this->redirect("/clients/view/{$id}");
            }
        }

        $preferences = $this->clientService->getClientPreferences($id);

        $this->view('clients/edit', [
            'client' => $client,
            'preferences' => $preferences
        ]);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->clientService->deleteClient($id);
            $this->redirect('/clients');
        }
    }

    public function addInteraction(int $clientId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $interactionData = [
                'interaction_type' => $_POST['interaction_type'] ?? null,
                'description' => $_POST['description'] ?? null,
                'agent_id' => $_SESSION['user_id'] ?? null,
                'property_id' => $_POST['property_id'] ?? null,
                'scheduled_at' => $_POST['scheduled_at'] ?? null,
                'status' => $_POST['status'] ?? 'planned',
                'notes' => $_POST['notes'] ?? null
            ];

            if ($this->clientService->addInteraction($clientId, $interactionData)) {
                $this->redirect("/clients/view/{$clientId}#interactions");
            }
        }

        $client = $this->clientService->getClientById($clientId);
        
        // Get available properties
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $available_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('clients/add_interaction', [
            'client' => $client,
            'available_properties' => $available_properties
        ]);
    }

    public function updateMatchStatus(int $matchId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? null;
            if ($status) {
                $this->clientService->updateMatchStatus($matchId, $status);
            }
        }
        
        // Return JSON response for AJAX calls
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function calculateMatches(int $clientId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->clientService->calculateMatches($clientId);
            $this->redirect("/clients/view/{$clientId}#matches");
        }
    }

    public function linkDocument(int $clientId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? null;
            if ($documentId) {
                $this->clientService->linkDocument($clientId, $documentId);
                $this->redirect("/clients/view/{$clientId}#documents");
            }
        }
    }
} 
