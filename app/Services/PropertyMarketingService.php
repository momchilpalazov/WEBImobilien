<?php

namespace App\Services;

class PropertyMarketingService
{
    private $propertyRepository;
    private $imageService;
    private $exportService;
    private $marketingPath;

    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        ImageService $imageService,
        ExportService $exportService,
        string $marketingPath = 'storage/marketing'
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->imageService = $imageService;
        $this->exportService = $exportService;
        $this->marketingPath = $marketingPath;
    }

    public function generateMarketingMaterials(int $propertyId, array $options = []): array
    {
        try {
            $property = $this->propertyRepository->find($propertyId);
            if (!$property) {
                throw new \Exception('Имотът не е намерен.');
            }

            $materials = [];

            if (empty($options['types']) || in_array('social', $options['types'])) {
                $materials['social'] = $this->generateSocialMediaPosts($property);
            }

            if (empty($options['types']) || in_array('banners', $options['types'])) {
                $materials['banners'] = $this->generateBanners($property);
            }

            if (empty($options['types']) || in_array('email', $options['types'])) {
                $materials['email'] = $this->generateEmailTemplate($property);
            }

            return $materials;
        } catch (\Exception $e) {
            error_log("Error generating marketing materials: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateSocialMediaPosts(array $property): array
    {
        $posts = [];

        // Facebook пост
        $posts['facebook'] = [
            'text' => $this->generateFacebookText($property),
            'image' => $this->prepareImage($property['images'][0], [
                'width' => 1200,
                'height' => 630,
                'watermark' => true
            ])
        ];

        // Instagram пост
        $posts['instagram'] = [
            'text' => $this->generateInstagramText($property),
            'image' => $this->prepareImage($property['images'][0], [
                'width' => 1080,
                'height' => 1080,
                'watermark' => true
            ])
        ];

        // LinkedIn пост
        $posts['linkedin'] = [
            'text' => $this->generateLinkedInText($property),
            'image' => $this->prepareImage($property['images'][0], [
                'width' => 1200,
                'height' => 627,
                'watermark' => true
            ])
        ];

        return $posts;
    }

    private function generateBanners(array $property): array
    {
        $banners = [];

        // Стандартни размери за банери
        $sizes = [
            'rectangle' => ['width' => 300, 'height' => 250],
            'leaderboard' => ['width' => 728, 'height' => 90],
            'skyscraper' => ['width' => 160, 'height' => 600],
            'mobile' => ['width' => 320, 'height' => 100]
        ];

        foreach ($sizes as $type => $dimensions) {
            $banners[$type] = $this->generateBanner($property, $dimensions);
        }

        return $banners;
    }

    private function generateEmailTemplate(array $property): array
    {
        return [
            'subject' => $this->generateEmailSubject($property),
            'content' => $this->generateEmailContent($property),
            'images' => array_map(
                fn($image) => $this->prepareImage($image, [
                    'width' => 600,
                    'height' => null,
                    'watermark' => true
                ]),
                array_slice($property['images'], 0, 3)
            )
        ];
    }

    private function generateFacebookText(array $property): string
    {
        $text = "🏠 НОВО ПРЕДЛОЖЕНИЕ!\n\n";
        $text .= "{$property['title']}\n\n";
        $text .= "✨ Основни характеристики:\n";
        $text .= "📍 Локация: {$property['location']}\n";
        $text .= "🛏 Спални: {$property['bedrooms']}\n";
        $text .= "🚿 Бани: {$property['bathrooms']}\n";
        $text .= "📐 Площ: {$property['area']}кв.м\n\n";
        $text .= "💰 Цена: {$property['price']}€\n\n";
        $text .= "🔍 За повече информация и огледи:\n";
        $text .= "📞 Тел: +359 888 123456\n";
        $text .= "🌐 www.imoti.com/properties/{$property['id']}\n\n";
        $text .= "#недвижимиимоти #имоти #продажба #" . $property['location'];

        return $text;
    }

    private function generateInstagramText(array $property): string
    {
        $text = "🏠 {$property['title']}\n\n";
        $text .= "📍 {$property['location']}\n";
        $text .= "💰 {$property['price']}€\n\n";
        $text .= "✨ {$property['bedrooms']} спални\n";
        $text .= "✨ {$property['bathrooms']} бани\n";
        $text .= "✨ {$property['area']}кв.м\n\n";
        $text .= "👉 Линк в био\n\n";
        $text .= "#realestate #property #home #" . str_replace(' ', '', $property['location']);

        return $text;
    }

    private function generateLinkedInText(array $property): string
    {
        $text = "🏠 Ексклузивно предложение\n\n";
        $text .= "Представяме Ви {$property['title']} - отлична възможност за инвестиция или дом.\n\n";
        $text .= "Основни характеристики:\n";
        $text .= "• Локация: {$property['location']}\n";
        $text .= "• Площ: {$property['area']}кв.м\n";
        $text .= "• {$property['bedrooms']} спални и {$property['bathrooms']} бани\n";
        $text .= "• Цена: {$property['price']}€\n\n";
        $text .= "За повече информация и огледи:\n";
        $text .= "🌐 www.imoti.com/properties/{$property['id']}\n";
        $text .= "📞 +359 888 123456\n\n";
        $text .= "#RealEstate #Investment #" . $property['location'];

        return $text;
    }

    private function generateEmailSubject(array $property): string
    {
        return "Ново предложение: {$property['title']} в {$property['location']}";
    }

    private function generateEmailContent(array $property): string
    {
        $content = "<h2>Представяме Ви {$property['title']}</h2>";
        $content .= "<p>Уникална възможност за закупуване на имот в {$property['location']}.</p>";
        
        $content .= "<h3>Основни характеристики:</h3>";
        $content .= "<ul>";
        $content .= "<li>Локация: {$property['location']}</li>";
        $content .= "<li>Площ: {$property['area']}кв.м</li>";
        $content .= "<li>Спални: {$property['bedrooms']}</li>";
        $content .= "<li>Бани: {$property['bathrooms']}</li>";
        $content .= "</ul>";

        $content .= "<p><strong>Цена: {$property['price']}€</strong></p>";
        
        $content .= "<p>{$property['description']}</p>";
        
        $content .= "<p>За повече информация и огледи:<br>";
        $content .= "Тел: +359 888 123456<br>";
        $content .= "Email: office@imoti.com</p>";

        return $content;
    }

    private function generateBanner(array $property, array $dimensions): array
    {
        return [
            'image' => $this->prepareImage($property['images'][0], [
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'watermark' => true
            ]),
            'text' => [
                'title' => $this->truncateText($property['title'], 30),
                'price' => number_format($property['price'], 0) . '€',
                'location' => $this->truncateText($property['location'], 20)
            ]
        ];
    }

    private function prepareImage(string $imagePath, array $options): string
    {
        $filename = basename($imagePath);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        $newFilename = sprintf(
            '%s_%dx%d%s.%s',
            pathinfo($filename, PATHINFO_FILENAME),
            $options['width'],
            $options['height'] ?? 'auto',
            $options['watermark'] ? '_wm' : '',
            $extension
        );

        $outputPath = $this->marketingPath . '/' . $newFilename;
        
        if (!file_exists($outputPath)) {
            $this->imageService->resize($imagePath, $outputPath, $options);
            
            if ($options['watermark']) {
                $this->imageService->addWatermark($outputPath);
            }
        }

        return $newFilename;
    }

    private function truncateText(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3) . '...';
    }
} 