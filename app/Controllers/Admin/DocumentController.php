<?php

namespace App\Controllers\Admin;

use App\Interfaces\DocumentManagementInterface;
use DateTime;
use Exception;
use App\Controllers\Admin\BaseAdminController;

class DocumentController extends BaseAdminController
{
    private DocumentManagementInterface $documentService;
    
    public function __construct(DocumentManagementInterface $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }
    
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        
        $criteria = [
            'title' => $_GET['title'] ?? '',
            'category' => $_GET['category'] ?? '',
            'entity_type' => $_GET['entity_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $sorting = [
            'field' => $_GET['sort_field'] ?? 'created_at',
            'direction' => $_GET['sort_direction'] ?? 'desc'
        ];
        
        $result = $this->documentService->searchDocuments($criteria, $sorting, $page, $perPage);
        $categories = $this->documentService->getCategories();
        $statistics = $this->documentService->getStatistics();
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'data' => $result,
                'statistics' => $statistics
            ]);
            return;
        }
        
        $this->render('admin/documents/index', [
            'documents' => $result['documents'],
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
            'categories' => $categories,
            'criteria' => $criteria,
            'sorting' => $sorting,
            'statistics' => $statistics
        ]);
    }
    
    public function upload(): void
    {
        try {
            if (!$this->isPost()) {
                throw new Exception('Невалиден метод на заявка');
            }
            
            if (empty($_FILES['document'])) {
                throw new Exception('Не е избран файл');
            }
            
            $metadata = [
                'title' => $_POST['title'] ?? '',
                'category' => $_POST['category'] ?? '',
                'entity_type' => $_POST['entity_type'] ?? null,
                'entity_id' => (int)($_POST['entity_id'] ?? 0),
                'user_id' => $this->getCurrentUserId()
            ];
            
            $document = $this->documentService->uploadDocument($_FILES['document'], $metadata);
            
            if (!$document) {
                throw new Exception('Грешка при качване на документа');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'document' => $document,
                    'message' => 'Документът е качен успешно'
                ]);
                return;
            }
            
            $this->setSuccess('Документът е качен успешно');
            $this->redirect('/admin/documents');
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/documents');
        }
    }
    
    public function update(int $id): void
    {
        try {
            if (!$this->isPost()) {
                throw new Exception('Невалиден метод на заявка');
            }
            
            $metadata = [
                'title' => $_POST['title'] ?? '',
                'category' => $_POST['category'] ?? '',
                'user_id' => $this->getCurrentUserId()
            ];
            
            if (!$this->documentService->updateDocument($id, $metadata)) {
                throw new Exception('Грешка при обновяване на документа');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Документът е обновен успешно'
                ]);
                return;
            }
            
            $this->setSuccess('Документът е обновен успешно');
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
        }
        
        $this->redirect('/admin/documents');
    }
    
    public function delete(int $id): void
    {
        try {
            if (!$this->documentService->hasAccess($id, $this->getCurrentUserId())) {
                throw new Exception('Нямате права за изтриване на този документ');
            }
            
            if (!$this->documentService->deleteDocument($id)) {
                throw new Exception('Грешка при изтриване на документа');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Документът е изтрит успешно'
                ]);
                return;
            }
            
            $this->setSuccess('Документът е изтрит успешно');
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
        }
        
        $this->redirect('/admin/documents');
    }
    
    public function share(int $id): void
    {
        try {
            if (!$this->isPost()) {
                throw new Exception('Невалиден метод на заявка');
            }
            
            $clientId = (int)($_POST['client_id'] ?? 0);
            if (!$clientId) {
                throw new Exception('Не е избран клиент');
            }
            
            $expiresAt = null;
            if (!empty($_POST['expires_at'])) {
                $expiresAt = new DateTime($_POST['expires_at']);
            }
            
            $shareData = [
                'client_id' => $clientId,
                'expires_at' => $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : null,
                'shared_by' => $this->getCurrentUserId()
            ];
            
            $shareUrl = $this->documentService->shareDocument($id, $shareData);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'share_url' => $shareUrl,
                    'message' => 'Документът е споделен успешно'
                ]);
                return;
            }
            
            $this->setSuccess('Документът е споделен успешно');
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
        }
        
        $this->redirect('/admin/documents');
    }
    
    public function entityDocuments(string $entityType, int $entityId): void
    {
        try {
            $documents = $this->documentService->getDocumentsByEntity($entityType, $entityId);
            $statistics = $this->documentService->getStatistics($entityType, $entityId);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'documents' => $documents,
                    'statistics' => $statistics
                ]);
                return;
            }
            
            $this->render('admin/documents/entity', [
                'documents' => $documents,
                'statistics' => $statistics,
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
            
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/documents');
        }
    }
} 