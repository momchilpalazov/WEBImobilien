<?php

namespace App\Controllers\Admin;

use App\Interfaces\ContractTemplateInterface;

class ContractTemplateController extends BaseAdminController
{
    private ContractTemplateInterface $templateService;
    
    public function __construct(ContractTemplateInterface $templateService)
    {
        parent::__construct();
        $this->templateService = $templateService;
    }
    
    public function index(): void
    {
        $templates = $this->templateService->getAvailableTypes();
        
        $this->render('admin/contracts/templates/index', [
            'templates' => $templates
        ]);
    }
    
    public function edit(string $type): void
    {
        try {
            if (!$this->isPost()) {
                $template = $this->templateService->getTemplate($type);
                $variables = $this->templateService->getTemplateVariables($type);
                
                $this->render('admin/contracts/templates/edit', [
                    'type' => $type,
                    'template' => $template,
                    'variables' => $variables
                ]);
                return;
            }
            
            $content = $_POST['content'] ?? '';
            if (empty($content)) {
                throw new \InvalidArgumentException('Съдържанието на шаблона е задължително.');
            }
            
            $this->templateService->saveTemplate($type, $content);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Шаблонът е запазен успешно.'
                ]);
                return;
            }
            
            $this->setSuccess('Шаблонът е запазен успешно.');
            $this->redirect('/admin/contracts/templates');
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/contracts/templates');
        }
    }
    
    public function delete(string $type): void
    {
        try {
            if (!$this->isPost()) {
                $this->redirect('/admin/contracts/templates');
                return;
            }
            
            $this->templateService->deleteTemplate($type);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Шаблонът е изтрит успешно.'
                ]);
                return;
            }
            
            $this->setSuccess('Шаблонът е изтрит успешно.');
            $this->redirect('/admin/contracts/templates');
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/contracts/templates');
        }
    }
    
    public function preview(string $type): void
    {
        try {
            $template = $this->templateService->getTemplate($type);
            $variables = $this->templateService->getTemplateVariables($type);
            
            // Генерираме примерни данни за преглед
            $data = $this->generatePreviewData($variables);
            
            // Заместваме променливите с примерни данни
            $content = $this->replaceVariables($template, $data);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'content' => $content
                ]);
                return;
            }
            
            $this->render('admin/contracts/templates/preview', [
                'type' => $type,
                'content' => $content
            ]);
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/contracts/templates');
        }
    }
    
    private function generatePreviewData(array $variables): array
    {
        $data = [];
        foreach ($variables as $variable) {
            switch ($variable) {
                case 'contract_number':
                    $data[$variable] = date('Ymd') . '-001';
                    break;
                case 'contract_date':
                    $data[$variable] = date('d.m.Y');
                    break;
                case 'agency_city':
                    $data[$variable] = 'София';
                    break;
                case 'client_name':
                    $data[$variable] = 'Иван Иванов';
                    break;
                case 'client_egn':
                    $data[$variable] = '1234567890';
                    break;
                case 'client_address':
                    $data[$variable] = 'ул. Примерна 1, София';
                    break;
                case 'property_description':
                    $data[$variable] = 'Двустаен апартамент';
                    break;
                case 'property_address':
                    $data[$variable] = 'ул. Примерна 2, София';
                    break;
                case 'property_area':
                    $data[$variable] = '65';
                    break;
                case 'price':
                    $data[$variable] = '100000';
                    break;
                default:
                    $data[$variable] = "{{$variable}}";
            }
        }
        return $data;
    }
    
    private function replaceVariables(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data) {
            $variable = $matches[1];
            return $data[$variable] ?? $matches[0];
        }, $template);
    }
} 