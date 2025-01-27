<?php

// Масив с URL адреси на изображения за услуги
$service_images = [
    'consulting' => 'https://source.unsplash.com/800x600/?business+consulting',
    'valuation' => 'https://source.unsplash.com/800x600/?property+valuation',
    'management' => 'https://source.unsplash.com/800x600/?property+management',
    'investment' => 'https://source.unsplash.com/800x600/?real+estate+investment',
    'development' => 'https://source.unsplash.com/800x600/?property+development',
    'research' => 'https://source.unsplash.com/800x600/?market+research'
];

// Създаване на директорията за услуги
$services_dir = 'uploads/services';
if (!file_exists($services_dir)) {
    mkdir($services_dir, 0777, true);
}

// Изтегляне на изображенията
foreach ($service_images as $service => $url) {
    $filename = $services_dir . '/' . $service . '.jpg';
    
    try {
        echo "<p>Изтегляне на изображение за $service...</p>";
        
        $image = file_get_contents($url);
        if ($image !== false) {
            file_put_contents($filename, $image);
            echo "<p class='text-success'>✓ Изтеглено изображение: $filename</p>";
            
            // Създаване на умалено изображение
            list($width, $height) = getimagesize($filename);
            $thumb_width = 400;
            $thumb_height = (int)($height * ($thumb_width / $width));
            
            $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
            $source = imagecreatefromjpeg($filename);
            
            imagecopyresampled(
                $thumb,
                $source,
                0, 0, 0, 0,
                $thumb_width, $thumb_height,
                $width, $height
            );
            
            $thumb_filename = $services_dir . '/thumb_' . $service . '.jpg';
            imagejpeg($thumb, $thumb_filename, 80);
            
            imagedestroy($thumb);
            imagedestroy($source);
            
            echo "<p class='text-success'>✓ Създадено умалено изображение: " . basename($thumb_filename) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>Грешка при изтегляне на изображение за $service: " . $e->getMessage() . "</p>";
    }
}

?> 