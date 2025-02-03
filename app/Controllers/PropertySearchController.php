<?php

namespace App\Controllers;

use App\Services\PropertySearchService;

class PropertySearchController extends BaseController
{
    private PropertySearchService $searchService;

    public function __construct(PropertySearchService $searchService)
    {
        parent::__construct();
        $this->searchService = $searchService;
    }

    public function search(): void
    {
        try {
            $criteria = $this->sanitizeInput($_GET);
            $userId = $_SESSION['user_id'] ?? null;

            $results = $this->searchService->search($criteria, $userId);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'results' => $results
                ]);
                return;
            }

            $this->render('properties/search', [
                'results' => $results,
                'criteria' => $criteria
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
            $this->redirect('/properties/search');
        }
    }

    public function similar(int $id): void
    {
        try {
            $results = $this->searchService->getSimilarProperties($id);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'results' => $results
                ]);
                return;
            }

            $this->render('properties/similar', [
                'results' => $results,
                'propertyId' => $id
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
            $this->redirect("/properties/{$id}");
        }
    }

    public function saveSearch(): void
    {
        if (!$this->isPost() || !isset($_SESSION['user_id'])) {
            $this->redirect('/properties/search');
            return;
        }

        try {
            $data = $this->sanitizeInput($_POST);
            $searchId = $this->searchService->saveSearch($_SESSION['user_id'], $data);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Търсенето е запазено успешно.',
                    'search_id' => $searchId
                ]);
                return;
            }

            $this->setSuccess('Търсенето е запазено успешно.');
            $this->redirect('/account/saved-searches');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/properties/search');
        }
    }

    public function savedSearches(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $searches = $this->searchService->getSavedSearches($_SESSION['user_id']);
        
        $this->render('account/saved-searches', [
            'searches' => $searches
        ]);
    }

    public function deleteSavedSearch(int $id): void
    {
        if (!$this->isPost() || !isset($_SESSION['user_id'])) {
            $this->redirect('/account/saved-searches');
            return;
        }

        try {
            $this->searchService->deleteSavedSearch($id, $_SESSION['user_id']);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Търсенето е изтрито успешно.'
                ]);
                return;
            }

            $this->setSuccess('Търсенето е изтрито успешно.');
            $this->redirect('/account/saved-searches');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/account/saved-searches');
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