<?php

namespace App\Controllers;

use App\Interfaces\DocumentManagementInterface;
use PDO;

class DocumentController extends BaseController
{
    private $documentService;
    private $db;

    public function __construct(DocumentManagementInterface $documentService, PDO $db)
    {
        parent::__construct();
        $this->documentService = $documentService;
        $this->db = $db;
    }

    public function index(): void
    {
        $filters = [
            'category' => $_GET['category'] ?? null,
            'status' => $_GET['status'] ?? 'active',
            'search' => $_GET['search'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $documents = $this->documentService->getAllDocuments($filters);
        
        $this->view('documents/index', [
            'documents' => $documents,
            'filters' => $filters
        ]);
    }

    protected function view(string $name, array $data = []): void
    {
        parent::view($name, $data);
    }

    public function viewDocument(int $id): void
    {
        $document = $this->documentService->getDocumentById($id);
        if (!$document) {
            $this->redirect('/documents');
        }

        // Log view access
        $this->documentService->logAccess($id, $_SESSION['user_id'], 'view');

        // Get related entities
        $relations = $this->getDocumentRelations($id);

        // Get versions
        $versions = $this->documentService->getDocumentVersions($id);

        // Get signatures
        $signatures = $this->documentService->getDocumentSignatures($id);

        // Get shares
        $shares = $this->documentService->getDocumentShares($id);

        // Get access logs
        $logs = $this->documentService->getAccessLogs($id);

        $this->view('documents/view', [
            'document' => $document,
            'relations' => $relations,
            'versions' => $versions,
            'signatures' => $signatures,
            'shares' => $shares,
            'logs' => $logs
        ]);
    }

    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'] ?? null,
                    'category' => $_POST['category'],
                    'status' => $_POST['status'] ?? 'active',
                    'created_by' => $_SESSION['user_id']
                ];

                // Process relations
                if (!empty($_POST['relations'])) {
                    $data['relations'] = [];
                    foreach ($_POST['relations'] as $type => $ids) {
                        foreach ($ids as $id) {
                            $data['relations'][] = [
                                'type' => $type,
                                'id' => $id
                            ];
                        }
                    }
                }

                // Process signatures
                if (!empty($_POST['signatures'])) {
                    $data['signatures'] = [];
                    foreach ($_POST['signatures'] as $signature) {
                        if (!empty($signature['name'])) {
                            $data['signatures'][] = $signature;
                        }
                    }
                }

                if (empty($_FILES['file'])) {
                    throw new \Exception('No file uploaded');
                }

                $documentId = $this->documentService->uploadDocument($data, $_FILES['file']);
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'id' => $documentId]);
                    exit;
                }

                $this->redirect("/documents/view/{$documentId}");
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

        // Get related entities for selection
        $properties = $this->getActiveProperties();
        $clients = $this->getActiveClients();
        $agents = $this->getActiveAgents();

        $this->view('documents/upload', [
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents,
            'error' => $error ?? null
        ]);
    }

    public function update(int $id): void
    {
        $document = $this->documentService->getDocumentById($id);
        if (!$document) {
            $this->redirect('/documents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'status' => $_POST['status']
            ];

            if ($this->documentService->updateDocument($id, $data)) {
                // Update relations if changed
                if (!empty($_POST['relations'])) {
                    $newRelations = [];
                    foreach ($_POST['relations'] as $type => $ids) {
                        foreach ($ids as $id) {
                            $newRelations[] = [
                                'type' => $type,
                                'id' => $id
                            ];
                        }
                    }

                    // Get current relations
                    $currentRelations = $this->getDocumentRelations($id);
                    
                    // Calculate differences
                    $toAdd = array_udiff($newRelations, $currentRelations,
                        fn($a, $b) => strcmp("{$a['type']}-{$a['id']}", "{$b['type']}-{$b['id']}"));
                    
                    $toRemove = array_udiff($currentRelations, $newRelations,
                        fn($a, $b) => strcmp("{$a['type']}-{$a['id']}", "{$b['type']}-{$b['id']}"));

                    if (!empty($toAdd)) {
                        $this->documentService->addDocumentRelations($id, $toAdd);
                    }
                    if (!empty($toRemove)) {
                        $this->documentService->removeDocumentRelations($id, $toRemove);
                    }
                }

                $this->redirect("/documents/view/{$id}");
            }
        }

        // Get related entities for selection
        $properties = $this->getActiveProperties();
        $clients = $this->getActiveClients();
        $agents = $this->getActiveAgents();

        // Get current relations
        $relations = $this->getDocumentRelations($id);

        $this->view('documents/edit', [
            'document' => $document,
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents,
            'relations' => $relations
        ]);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->documentService->deleteDocument($id)) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                $this->redirect('/documents');
            }
        }
    }

    public function uploadVersion(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'changes_description' => $_POST['changes_description'] ?? null,
                    'created_by' => $_SESSION['user_id']
                ];

                if (empty($_FILES['file'])) {
                    throw new \Exception('No file uploaded');
                }

                if ($this->documentService->createVersion($id, $data, $_FILES['file'])) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true]);
                        exit;
                    }
                    $this->redirect("/documents/view/{$id}");
                }
            } catch (\Exception $e) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    exit;
                }
            }
        }
    }

    public function addSignature(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signerData = [
                'type' => $_POST['signer_type'],
                'id' => $_POST['signer_id'] ?? null,
                'name' => $_POST['signer_name'],
                'email' => $_POST['signer_email'] ?? null,
                'expiration_date' => $_POST['expiration_date'] ?? null
            ];

            if ($this->documentService->addSignatureRequest($id, $signerData)) {
                $this->redirect("/documents/view/{$id}");
            }
        }

        $document = $this->documentService->getDocumentById($id);
        $this->view('documents/add_signature', [
            'document' => $document
        ]);
    }

    public function updateSignature(int $signatureId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'];
            $data = [];

            if ($status === 'signed') {
                $data = [
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'signature_data' => $_POST['signature_data'] ?? null
                ];
            }

            if ($this->documentService->updateSignatureStatus($signatureId, $status, $data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function share(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $shareData = [
                'shared_by' => $_SESSION['user_id'],
                'email' => $_POST['email'],
                'permissions' => $_POST['permissions'] ?? null,
                'expiration_date' => $_POST['expiration_date'] ?? null
            ];

            $accessToken = $this->documentService->shareDocument($id, $shareData);
            
            if ($accessToken) {
                // Generate share URL
                $shareUrl = "https://{$_SERVER['HTTP_HOST']}/documents/shared/{$accessToken}";
                
                // TODO: Send email to recipient with share URL
                
                $this->redirect("/documents/view/{$id}");
            }
        }

        $document = $this->documentService->getDocumentById($id);
        $this->view('documents/share', [
            'document' => $document
        ]);
    }

    public function updateShare(int $shareId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'permissions' => $_POST['permissions'] ?? null,
                'expiration_date' => $_POST['expiration_date'] ?? null,
                'is_active' => $_POST['is_active'] ?? true
            ];

            if ($this->documentService->updateShare($shareId, $data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function revokeShare(int $shareId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->documentService->revokeShare($shareId)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    public function shared(string $accessToken): void
    {
        $share = $this->documentService->validateShareAccess($accessToken);
        if (!$share) {
            $this->view('documents/shared_error', [
                'error' => 'Invalid or expired share link'
            ]);
            return;
        }

        $document = $this->documentService->getDocumentById($share['document_id']);
        $this->view('documents/shared', [
            'document' => $document,
            'share' => $share
        ]);
    }

    public function templates(): void
    {
        $filters = [
            'category' => $_GET['category'] ?? null,
            'is_active' => isset($_GET['is_active']) ? (bool)$_GET['is_active'] : true,
            'search' => $_GET['search'] ?? null
        ];

        $templates = $this->documentService->getAllTemplates($filters);
        
        $this->view('documents/templates', [
            'templates' => $templates,
            'filters' => $filters
        ]);
    }

    public function createTemplate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'content' => $_POST['content'],
                'variables' => $_POST['variables'] ?? null,
                'created_by' => $_SESSION['user_id']
            ];

            $templateId = $this->documentService->createTemplate($data);
            if ($templateId) {
                $this->redirect('/documents/templates');
            }
        }

        $this->view('documents/create_template');
    }

    public function editTemplate(int $id): void
    {
        $template = $this->documentService->getTemplateById($id);
        if (!$template) {
            $this->redirect('/documents/templates');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'content' => $_POST['content'],
                'variables' => $_POST['variables'] ?? null,
                'is_active' => isset($_POST['is_active'])
            ];

            if ($this->documentService->updateTemplate($id, $data)) {
                $this->redirect('/documents/templates');
            }
        }

        $this->view('documents/edit_template', [
            'template' => $template
        ]);
    }

    public function deleteTemplate(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->documentService->deleteTemplate($id)) {
                $this->redirect('/documents/templates');
            }
        }
    }

    public function generateFromTemplate(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $content = $this->documentService->generateFromTemplate($id, $_POST['variables'] ?? []);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'content' => $content]);
                exit;
            } catch (\Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }

        $template = $this->documentService->getTemplateById($id);
        if (!$template) {
            $this->redirect('/documents/templates');
        }

        $this->view('documents/generate', [
            'template' => $template
        ]);
    }

    private function getDocumentRelations(int $documentId): array
    {
        $sql = "SELECT dr.relation_type, dr.relation_id,
                CASE dr.relation_type
                    WHEN 'property' THEN p.title
                    WHEN 'client' THEN c.name
                    WHEN 'agent' THEN a.name
                END as relation_name
                FROM document_relations dr
                LEFT JOIN properties p ON dr.relation_type = 'property' AND dr.relation_id = p.id
                LEFT JOIN clients c ON dr.relation_type = 'client' AND dr.relation_id = c.id
                LEFT JOIN users a ON dr.relation_type = 'agent' AND dr.relation_id = a.id
                WHERE dr.document_id = :document_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getActiveProperties(): array
    {
        $stmt = $this->db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getActiveClients(): array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM clients WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getActiveAgents(): array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE role = 'agent' AND status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 