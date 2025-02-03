<?php

namespace App\Controllers;

use App\Interfaces\ViewingManagementInterface;
use PDO;

class ViewingController extends BaseController
{
    private $viewingService;
    private $db;

    public function __construct(ViewingManagementInterface $viewingService, PDO $db)
    {
        parent::__construct();
        $this->viewingService = $viewingService;
        $this->db = $db;
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $viewings = $this->viewingService->getAllViewings($filters);
        
        $this->view('viewings/index', [
            'viewings' => $viewings,
            'filters' => $filters
        ]);
    }

    public function details(int $id): void
    {
        $viewing = $this->viewingService->getViewingById($id);
        if (!$viewing) {
            $this->redirect('/viewings');
        }

        $feedback = $this->viewingService->getViewingFeedback($id);
        $notifications = $this->viewingService->getViewingNotifications($id);

        $this->view('viewings/view', [
            'viewing' => $viewing,
            'feedback' => $feedback,
            'notifications' => $notifications
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'property_id' => $_POST['property_id'] ?? null,
                'client_id' => $_POST['client_id'] ?? null,
                'agent_id' => $_POST['agent_id'] ?? null,
                'scheduled_at' => $_POST['scheduled_at'] ?? null,
                'status' => 'scheduled'
            ];

            try {
                $viewingId = $this->viewingService->createViewing($data);
                $this->redirect("/viewings/view/{$viewingId}");
            } catch (\Exception $e) {
                // Handle error (e.g., agent not available)
                $error = $e->getMessage();
            }
        }

        // Get available properties, clients and agents
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM clients WHERE status != 'inactive' ORDER BY first_name");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'agent' AND status = 'active' ORDER BY first_name");
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('viewings/create', [
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents,
            'error' => $error ?? null
        ]);
    }

    public function edit(int $id): void
    {
        $viewing = $this->viewingService->getViewingById($id);
        if (!$viewing) {
            $this->redirect('/viewings');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'property_id' => $_POST['property_id'] ?? $viewing['property_id'],
                'client_id' => $_POST['client_id'] ?? $viewing['client_id'],
                'agent_id' => $_POST['agent_id'] ?? $viewing['agent_id'],
                'scheduled_at' => $_POST['scheduled_at'] ?? $viewing['scheduled_at'],
                'status' => $_POST['status'] ?? $viewing['status']
            ];

            if ($this->viewingService->updateViewing($id, $data)) {
                $this->redirect("/viewings/view/{$id}");
            }
        }

        // Get available properties, clients and agents
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM clients WHERE status != 'inactive' ORDER BY first_name");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'agent' AND status = 'active' ORDER BY first_name");
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('viewings/edit', [
            'viewing' => $viewing,
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents
        ]);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->viewingService->deleteViewing($id);
            $this->redirect('/viewings');
        }
    }

    public function addFeedback(int $id): void
    {
        $viewing = $this->viewingService->getViewingById($id);
        if (!$viewing) {
            $this->redirect('/viewings');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $feedbackData = [
                'property_condition' => $_POST['property_condition'] ?? null,
                'price_opinion' => $_POST['price_opinion'] ?? null,
                'location_rating' => $_POST['location_rating'] ?? null,
                'overall_impression' => $_POST['overall_impression'] ?? null,
                'interested' => isset($_POST['interested']) ? (bool)$_POST['interested'] : false,
                'comments' => $_POST['comments'] ?? null
            ];

            if ($this->viewingService->addFeedback($id, $feedbackData)) {
                $this->redirect("/viewings/view/{$id}#feedback");
            }
        }

        $this->view('viewings/add_feedback', [
            'viewing' => $viewing
        ]);
    }

    public function updateStatus(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? null;
            if ($status) {
                $this->viewingService->updateStatus($id, $status);
            }
        }
        
        // Return JSON response for AJAX calls
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function reschedule(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newDateTime = $_POST['scheduled_at'] ?? null;
            if ($newDateTime) {
                try {
                    $this->viewingService->rescheduleViewing($id, $newDateTime);
                    $this->redirect("/viewings/view/{$id}");
                } catch (\Exception $e) {
                    // Handle error (e.g., agent not available)
                    $error = $e->getMessage();
                }
            }
        }

        $viewing = $this->viewingService->getViewingById($id);
        $this->view('viewings/reschedule', [
            'viewing' => $viewing,
            'error' => $error ?? null
        ]);
    }

    public function calendar(): void
    {
        $filters = [
            'agent_id' => $_GET['agent_id'] ?? ($_SESSION['user_role'] === 'agent' ? $_SESSION['user_id'] : null),
            'start' => $_GET['start'] ?? date('Y-m-01'),
            'end' => $_GET['end'] ?? date('Y-m-t')
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle AJAX requests for calendar events
            $events = $this->viewingService->getCalendarEvents($filters);
            header('Content-Type: application/json');
            echo json_encode($events);
            exit;
        }

        // Get available agents for filter
        $stmt = $this->db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'agent' AND status = 'active' ORDER BY first_name");
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('viewings/calendar', [
            'agents' => $agents,
            'filters' => $filters
        ]);
    }
} 