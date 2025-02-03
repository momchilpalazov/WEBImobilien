<?php

namespace App\Controllers\Admin;

use App\Services\PropertyViewingService;

class PropertyViewController extends BaseAdminController
{
    private PropertyViewingService $viewingService;

    public function __construct(PropertyViewingService $viewingService)
    {
        parent::__construct();
        $this->viewingService = $viewingService;
    }

    public function index(): void
    {
        $viewings = $this->viewingService->getUpcomingViewings();
        
        $this->render('admin/viewings/index', [
            'viewings' => $viewings
        ]);
    }

    public function schedule(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/properties');
            return;
        }

        try {
            $data = $this->sanitizeInput($_POST);
            $viewingId = $this->viewingService->scheduleViewing($data);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Огледът е насрочен успешно.',
                    'viewing_id' => $viewingId
                ]);
                return;
            }

            $this->setSuccess('Огледът е насрочен успешно.');
            $this->redirect('/admin/viewings');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/properties');
        }
    }

    public function cancel(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/viewings');
            return;
        }

        try {
            $reason = $_POST['reason'] ?? '';
            $this->viewingService->cancelViewing($id, $reason);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Огледът е отказан успешно.'
                ]);
                return;
            }

            $this->setSuccess('Огледът е отказан успешно.');
            $this->redirect('/admin/viewings');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/viewings');
        }
    }

    public function complete(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/viewings');
            return;
        }

        try {
            $feedback = $this->sanitizeInput($_POST);
            $this->viewingService->completeViewing($id, $feedback);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Огледът е маркиран като проведен.'
                ]);
                return;
            }

            $this->setSuccess('Огледът е маркиран като проведен.');
            $this->redirect('/admin/viewings');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/viewings');
        }
    }

    public function reschedule(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/viewings');
            return;
        }

        try {
            $newData = $this->sanitizeInput($_POST);
            $this->viewingService->rescheduleViewing($id, $newData);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Огледът е пренасрочен успешно.'
                ]);
                return;
            }

            $this->setSuccess('Огледът е пренасрочен успешно.');
            $this->redirect('/admin/viewings');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/viewings');
        }
    }

    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
} 