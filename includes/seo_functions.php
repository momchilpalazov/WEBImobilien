<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';

use App\Database;

class SEOManager {
    private $db;
    private $current_language;
    private $openai_config;

    public function __construct($language = 'bg') {
        $this->db = Database::getInstance()->getConnection();
        $this->current_language = $language;
        $this->openai_config = require __DIR__ . '/../config/openai.php';
        $this->ensureTableExists();
    }

    // Проверка и създаване на таблицата ако не съществува
    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS seo_meta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_type VARCHAR(50) NOT NULL,
            page_id INT,
            language VARCHAR(2) NOT NULL,
            title VARCHAR(255),
            meta_description TEXT,
            meta_keywords TEXT,
            canonical_url VARCHAR(255),
            og_title VARCHAR(255),
            og_description TEXT,
            og_image VARCHAR(255),
            schema_markup TEXT,
            robots_meta VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_page_lang (page_type, page_id, language)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            error_log("Грешка при създаване на таблица seo_meta: " . $e->getMessage());
        }
    }

    // Функция за извличане на SEO мета данни с проверка за грешки
    public function getSEOMeta($page_type, $page_id = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM seo_meta 
                WHERE page_type = ? 
                AND (page_id = ? OR page_id IS NULL) 
                AND language = ?
            ");
            $stmt->execute([$page_type, $page_id, $this->current_language]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'canonical_url' => '',
                'og_title' => '',
                'og_description' => '',
                'og_image' => '',
                'schema_markup' => '',
                'robots_meta' => ''
            ];
        } catch (\PDOException $e) {
            error_log("Грешка при извличане на SEO мета данни: " . $e->getMessage());
            return [
                'title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'canonical_url' => '',
                'og_title' => '',
                'og_description' => '',
                'og_image' => '',
                'schema_markup' => '',
                'robots_meta' => ''
            ];
        }
    }

    // Функция за генериране на Schema.org markup
    public function generatePropertySchema($property) {
        return json_encode([
            "@context" => "https://schema.org",
            "@type" => "RealEstateListing",
            "name" => $property['title_' . $this->current_language],
            "description" => $property['description_' . $this->current_language],
            "price" => [
                "@type" => "PriceSpecification",
                "price" => $property['price'],
                "priceCurrency" => "EUR"
            ],
            "location" => [
                "@type" => "Place",
                "address" => [
                    "@type" => "PostalAddress",
                    "addressLocality" => $property['location_' . $this->current_language]
                ]
            ],
            "image" => $this->getPropertyImages($property['id']),
            "datePosted" => $property['created_at'],
            "dateModified" => $property['updated_at']
        ], JSON_PRETTY_PRINT);
    }

    // Функция за автоматично генериране на мета описания
    public function generateMetaDescription($content, $max_length = 160) {
        $description = strip_tags($content);
        $description = str_replace(["\n", "\r", "\t"], ' ', $description);
        $description = preg_replace('/\s+/', ' ', $description);
        return mb_substr($description, 0, $max_length) . (mb_strlen($description) > $max_length ? '...' : '');
    }

    // Функция за запазване на SEO мета данни
    public function saveSEOMeta($data) {
        $stmt = $this->db->prepare("
            INSERT INTO seo_meta 
            (page_type, page_id, language, title, meta_description, meta_keywords, 
             canonical_url, og_title, og_description, og_image, schema_markup, robots_meta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            meta_description = VALUES(meta_description),
            meta_keywords = VALUES(meta_keywords),
            canonical_url = VALUES(canonical_url),
            og_title = VALUES(og_title),
            og_description = VALUES(og_description),
            og_image = VALUES(og_image),
            schema_markup = VALUES(schema_markup),
            robots_meta = VALUES(robots_meta)
        ");
        
        return $stmt->execute([
            $data['page_type'],
            $data['page_id'],
            $data['language'],
            $data['title'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['canonical_url'],
            $data['og_title'],
            $data['og_description'],
            $data['og_image'],
            $data['schema_markup'],
            $data['robots_meta']
        ]);
    }

    // Функция за генериране на hreflang тагове
    public function generateHrefLangTags($page_type, $page_id = null) {
        $tags = [];
        $languages = ['bg', 'en', 'de', 'ru'];
        $current_url = $this->getCurrentUrl();
        $base_url = $this->getBaseUrl();

        foreach ($languages as $lang) {
            $url = $this->getLocalizedUrl($current_url, $lang);
            $tags[] = "<link rel=\"alternate\" hreflang=\"{$lang}\" href=\"{$url}\" />";
        }

        // Добавяме x-default
        $default_url = $this->getLocalizedUrl($current_url, 'en');
        $tags[] = "<link rel=\"alternate\" hreflang=\"x-default\" href=\"{$default_url}\" />";

        return implode("\n    ", $tags);
    }

    // Помощни функции
    private function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    private function getLocalizedUrl($url, $language) {
        // Заменяме текущия език в URL с новия
        $pattern = '/\/(bg|en|de|ru)\//';
        if (preg_match($pattern, $url)) {
            return preg_replace($pattern, '/' . $language . '/', $url);
        }
        return $url . $language . '/';
    }

    private function getPropertyImages($property_id) {
        $stmt = $this->db->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
        $stmt->execute([$property_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map(function($img) {
            return $this->getBaseUrl() . '/uploads/properties/' . $img;
        }, $images);
    }

    // AI-оптимизация
    public function generateAIMetadata($content, $type = 'property') {
        $language_prompts = [
            'bg' => [
                'title' => 'Генерирай SEO заглавие за следното съдържание, максимум 60 символа:',
                'description' => 'Създай мета описание за следното съдържание, максимум 160 символа:',
                'keywords' => 'Извлечи ключови думи от следното съдържание (до 10 думи/фрази):',
            ],
            'en' => [
                'title' => 'Generate an SEO title for the following content, maximum 60 characters:',
                'description' => 'Create a meta description for the following content, maximum 160 characters:',
                'keywords' => 'Extract keywords from the following content (up to 10 words/phrases):',
            ],
            'de' => [
                'title' => 'Generiere einen SEO-Titel für den folgenden Inhalt, maximal 60 Zeichen:',
                'description' => 'Erstelle eine Meta-Beschreibung für den folgenden Inhalt, maximal 160 Zeichen:',
                'keywords' => 'Extrahiere Keywords aus dem folgenden Inhalt (bis zu 10 Wörter/Phrasen):',
            ],
            'ru' => [
                'title' => 'Создайте SEO заголовок для следующего контента, максимум 60 символов:',
                'description' => 'Создайте мета-описание для следующего контента, максимум 160 символов:',
                'keywords' => 'Извлеките ключевые слова из следующего контента (до 10 слов/фраз):',
            ]
        ];

        $prompts = $language_prompts[$this->current_language];

        try {
            $title = $this->callOpenAI($prompts['title'] . "\n\n" . $content);
            $description = $this->callOpenAI($prompts['description'] . "\n\n" . $content);
            $keywords = $this->callOpenAI($prompts['keywords'] . "\n\n" . $content);

            return [
                'title' => $title,
                'meta_description' => $description,
                'meta_keywords' => $keywords,
                'success' => true
            ];
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function callOpenAI($prompt) {
        $curl = curl_init();
        
        $data = [
            'model' => $this->openai_config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional SEO specialist. Provide concise and effective SEO metadata.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $this->openai_config['max_tokens'],
            'temperature' => $this->openai_config['temperature']
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->openai_config['api_key'],
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception('cURL Error: ' . $err);
        }

        $result = json_decode($response, true);
        if (isset($result['error'])) {
            throw new Exception('OpenAI API Error: ' . $result['error']['message']);
        }

        return trim($result['choices'][0]['message']['content']);
    }
} 