<?php

namespace App\Controllers\Admin;

use App\Services\PropertyReportService;
use App\Repositories\AgentRepository;
use App\Repositories\PropertyRepository;
use App\Models\Property;
use App\Controllers\Admin\BaseAdminController;

class ReportController extends BaseAdminController
{
    private PropertyReportService $reportService;
    private AgentRepository $agentRepository;
    private PropertyRepository $propertyRepository;

    public function __construct(
        PropertyReportService $reportService,
        AgentRepository $agentRepository,
        PropertyRepository $propertyRepository
    ) {
        parent::__construct();
        $this->reportService = $reportService;
        $this->agentRepository = $agentRepository;
        $this->propertyRepository = $propertyRepository;
    }

    public function index(): void
    {
        $this->render('admin/reports/index', [
            'agents' => $this->agentRepository->findAll(),
            'propertyTypes' => Property::TYPES,
            'locations' => $this->propertyRepository->getDistinctLocations()
        ]);
    }

    public function generate(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/reports');
            return;
        }

        try {
            $type = $_POST['type'] ?? '';
            $filters = $this->getReportFilters();
            $format = $_POST['format'] ?? 'pdf';

            $filename = $this->reportService->generateReport($type, $filters, $format);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'url' => "/admin/reports/download/{$filename}"
                ]);
                return;
            }

            $this->redirect("/admin/reports/download/{$filename}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/reports');
        }
    }

    private function getReportFilters(): array
    {
        return [
            'period' => $_POST['period'] ?? 'month',
            'agent_id' => $_POST['agent_id'] ?? null,
            'property_type' => $_POST['property_type'] ?? null,
            'location' => $_POST['location'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null
        ];
    }
} 