<?php

namespace App\Controllers;

use App\Services\TranslationService;
use App\Repositories\PropertyRepository;
use App\Services\Validator;

class PropertyController extends Controller
{
    private PropertyRepository $propertyRepository;
    private Validator $validator;
    
    public function __construct(
        TranslationService $translationService,
        PropertyRepository $propertyRepository,
        Validator $validator
    ) {
        parent::__construct($translationService);
        $this->propertyRepository = $propertyRepository;
        $this->validator = $validator;
    }
    
    public function index(): string
    {
        // Вземане на параметрите за филтриране
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'min_area' => $_GET['min_area'] ?? null,
            'max_area' => $_GET['max_area'] ?? null,
            'location' => $_GET['location'] ?? null
        ];
        
        // Вземане на параметрите за сортиране
        $sorting = [];
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'price_asc':
                    $sorting['price'] = 'ASC';
                    break;
                case 'price_desc':
                    $sorting['price'] = 'DESC';
                    break;
                case 'area_asc':
                    $sorting['area'] = 'ASC';
                    break;
                case 'area_desc':
                    $sorting['area'] = 'DESC';
                    break;
                case 'newest':
                    $sorting['created_at'] = 'DESC';
                    break;
                case 'oldest':
                    $sorting['created_at'] = 'ASC';
                    break;
            }
        }

        // Вземане на параметрите за пагинация
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;
        
        // Вземане на имотите
        $result = $this->propertyRepository->findAll($filters, $sorting, $page, $perPage);
        
        // Рендериране на изгледа
        return $this->view('properties.index', [
            'properties' => $result['data'],
            'filters' => $filters,
            'sorting' => $_GET['sort'] ?? 'newest',
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page']
            ]
        ]);
    }
    
    public function show(int $id): string
    {
        // Вземане на имота
        $property = $this->propertyRepository->findById($id);
        
        if (!$property) {
            header("HTTP/1.0 404 Not Found");
            return $this->view('errors.404');
        }
        
        // Вземане на подобни имоти
        $similarProperties = $this->propertyRepository->findSimilar(
            $property->getType(),
            $property->getPrice(),
            $property->getArea(),
            $property->getId()
        );
        
        // Рендериране на изгледа
        return $this->view('properties.show', [
            'property' => $property,
            'similar_properties' => $similarProperties
        ]);
    }

    public function contact(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/properties/{$id}");
            return;
        }

        // Validate contact form
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ];

        if (!$this->validator->validate($data, $rules)) {
            $_SESSION['errors'] = $this->validator->getErrors();
            $_SESSION['old'] = $data;
            $this->redirect("/properties/{$id}#contact-form");
            return;
        }

        // TODO: Send contact email
        // For now just set success message
        $_SESSION['success'] = $this->translations['contact']['success'];
        $this->redirect("/properties/{$id}#contact-form");
    }
} 
