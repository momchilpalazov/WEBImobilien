<?php

namespace App\Controllers;

use App\Interfaces\MarketingManagementInterface;
use PDO;

class MarketingController extends BaseController
{
    private $marketingService;
    private $db;

    public function __construct(MarketingManagementInterface $marketingService, PDO $db)
    {
        parent::__construct();
        $this->marketingService = $marketingService;
        $this->db = $db;
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $campaigns = $this->marketingService->getAllCampaigns($filters);
        
        $this->view('marketing/index', [
            'campaigns' => $campaigns,
            'filters' => $filters
        ]);
    }

    public function materials(int $propertyId): void
    {
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? 'active'
        ];

        $materials = $this->marketingService->getPropertyMaterials($propertyId, $filters);

        // Get property details
        $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute([':id' => $propertyId]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('marketing/materials', [
            'materials' => $materials,
            'property' => $property,
            'filters' => $filters
        ]);
    }

    public function uploadMaterial(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'property_id' => $_POST['property_id'] ?? null,
                    'type' => $_POST['type'] ?? null,
                    'title' => $_POST['title'] ?? null,
                    'description' => $_POST['description'] ?? null,
                    'status' => $_POST['status'] ?? 'active'
                ];

                if (empty($_FILES['file'])) {
                    throw new \Exception('No file uploaded');
                }

                $materialId = $this->marketingService->uploadMaterial($data, $_FILES['file']);
                
                // Return JSON response for AJAX uploads
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'id' => $materialId]);
                    exit;
                }

                $this->redirect("/marketing/materials/{$data['property_id']}");
            } catch (\Exception $e) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    exit;
                }

                $error = $e->getMessage();
            }
        }

        // Get available properties
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('marketing/upload', [
            'properties' => $properties,
            'error' => $error ?? null
        ]);
    }

    public function updateMaterial(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'status' => $_POST['status'] ?? null
            ];

            if ($this->marketingService->updateMaterial($id, $data)) {
                $material = $this->marketingService->getMaterialById($id);
                $this->redirect("/marketing/materials/{$material['property_id']}");
            }
        }

        $material = $this->marketingService->getMaterialById($id);
        if (!$material) {
            $this->redirect('/marketing');
        }

        $this->view('marketing/edit_material', [
            'material' => $material
        ]);
    }

    public function deleteMaterial(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $material = $this->marketingService->getMaterialById($id);
            if ($material && $this->marketingService->deleteMaterial($id)) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                $this->redirect("/marketing/materials/{$material['property_id']}");
            }
        }
    }

    public function updateSortOrder(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sortData = json_decode(file_get_contents('php://input'), true);
            if ($this->marketingService->updateSortOrder($sortData)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function toggleFeatured(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->marketingService->toggleFeatured($id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function campaigns(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $campaigns = $this->marketingService->getAllCampaigns($filters);
        
        $this->view('marketing/campaigns', [
            'campaigns' => $campaigns,
            'filters' => $filters
        ]);
    }

    public function createCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'] ?? null,
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'] ?? null,
                    'budget' => $_POST['budget'] ?? null,
                    'status' => $_POST['status'] ?? 'draft',
                    'property_ids' => $_POST['property_ids'] ?? [],
                    'channels' => []
                ];

                // Process channels if any
                if (!empty($_POST['channels'])) {
                    foreach ($_POST['channels'] as $channel) {
                        if (!empty($channel['channel_type']) && !empty($channel['channel_name'])) {
                            $data['channels'][] = $channel;
                        }
                    }
                }

                $campaignId = $this->marketingService->createCampaign($data);
                $this->redirect("/marketing/campaigns/view/{$campaignId}");
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Get available properties
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('marketing/create_campaign', [
            'properties' => $properties,
            'error' => $error ?? null
        ]);
    }

    public function viewCampaign(int $id): void
    {
        $campaign = $this->marketingService->getCampaignById($id);
        if (!$campaign) {
            $this->redirect('/marketing/campaigns');
        }

        $report = $this->marketingService->generateCampaignReport($id, [
            'date_from' => $_GET['date_from'] ?? $campaign['start_date'],
            'date_to' => $_GET['date_to'] ?? ($campaign['end_date'] ?? date('Y-m-d'))
        ]);

        $this->view('marketing/view_campaign', [
            'campaign' => $campaign,
            'report' => $report
        ]);
    }

    public function editCampaign(int $id): void
    {
        $campaign = $this->marketingService->getCampaignById($id);
        if (!$campaign) {
            $this->redirect('/marketing/campaigns');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null,
                'budget' => $_POST['budget'] ?? null,
                'status' => $_POST['status']
            ];

            if ($this->marketingService->updateCampaign($id, $data)) {
                // Update properties if changed
                if (!empty($_POST['property_ids'])) {
                    $currentPropertyIds = array_column($campaign['properties'], 'id');
                    $newPropertyIds = $_POST['property_ids'];

                    $toAdd = array_diff($newPropertyIds, $currentPropertyIds);
                    $toRemove = array_diff($currentPropertyIds, $newPropertyIds);

                    if (!empty($toAdd)) {
                        $this->marketingService->addCampaignProperties($id, $toAdd);
                    }
                    if (!empty($toRemove)) {
                        $this->marketingService->removeCampaignProperties($id, $toRemove);
                    }
                }

                $this->redirect("/marketing/campaigns/view/{$id}");
            }
        }

        // Get available properties
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('marketing/edit_campaign', [
            'campaign' => $campaign,
            'properties' => $properties
        ]);
    }

    public function deleteCampaign(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->marketingService->deleteCampaign($id)) {
                $this->redirect('/marketing/campaigns');
            }
        }
    }

    public function addChannel(int $campaignId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $channelData = [
                'channel_type' => $_POST['channel_type'],
                'channel_name' => $_POST['channel_name'],
                'target_audience' => $_POST['target_audience'] ?? null,
                'budget_allocation' => $_POST['budget_allocation'] ?? null,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'metrics' => $_POST['metrics'] ?? null,
                'status' => $_POST['status'] ?? 'planned'
            ];

            if ($this->marketingService->addCampaignChannel($campaignId, $channelData)) {
                $this->redirect("/marketing/campaigns/view/{$campaignId}#channels");
            }
        }

        $campaign = $this->marketingService->getCampaignById($campaignId);
        $this->view('marketing/add_channel', [
            'campaign' => $campaign
        ]);
    }

    public function editChannel(int $channelId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $channelData = [
                'channel_type' => $_POST['channel_type'],
                'channel_name' => $_POST['channel_name'],
                'target_audience' => $_POST['target_audience'] ?? null,
                'budget_allocation' => $_POST['budget_allocation'] ?? null,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'metrics' => $_POST['metrics'] ?? null,
                'status' => $_POST['status']
            ];

            if ($this->marketingService->updateCampaignChannel($channelId, $channelData)) {
                // Get campaign ID for redirect
                $stmt = $this->db->prepare("SELECT campaign_id FROM campaign_channels WHERE id = :id");
                $stmt->execute([':id' => $channelId]);
                $channel = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->redirect("/marketing/campaigns/view/{$channel['campaign_id']}#channels");
            }
        }

        // Get channel details
        $stmt = $this->db->prepare("
            SELECT ch.*, c.title as campaign_title 
            FROM campaign_channels ch
            JOIN marketing_campaigns c ON ch.campaign_id = c.id
            WHERE ch.id = :id
        ");
        $stmt->execute([':id' => $channelId]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$channel) {
            $this->redirect('/marketing/campaigns');
        }

        $this->view('marketing/edit_channel', [
            'channel' => $channel
        ]);
    }

    public function deleteChannel(int $channelId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get campaign ID for redirect
            $stmt = $this->db->prepare("SELECT campaign_id FROM campaign_channels WHERE id = :id");
            $stmt->execute([':id' => $channelId]);
            $channel = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($this->marketingService->deleteCampaignChannel($channelId)) {
                $this->redirect("/marketing/campaigns/view/{$channel['campaign_id']}#channels");
            }
        }
    }

    public function recordAnalytics(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'material_id' => $_POST['material_id'] ?? null,
                'campaign_id' => $_POST['campaign_id'] ?? null,
                'channel_id' => $_POST['channel_id'] ?? null,
                'metric_type' => $_POST['metric_type'],
                'metric_value' => $_POST['metric_value'],
                'date_recorded' => $_POST['date_recorded'] ?? date('Y-m-d')
            ];

            if ($this->marketingService->recordAnalytics($data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }
} 