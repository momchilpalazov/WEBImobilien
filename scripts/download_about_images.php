<?php

// Масив с URL адреси на изображения
$images = [
    'mission.jpg' => 'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=800',
    'experience.jpg' => 'https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=800'
];

// Създаване на директорията
$about_dir = 'images/about';
if (!file_exists($about_dir)) {
    mkdir($about_dir, 0777, true);
}

// Изтегляне на изображенията
foreach ($images as $filename => $url) {
    $destination = $about_dir . '/' . $filename;
    
    try {
        echo "<p>Изтегляне на $filename...</p>";
        
        $image = file_get_contents($url);
        if ($image !== false) {
            file_put_contents($destination, $image);
            echo "<p class='text-success'>✓ Изтеглено изображение: $filename</p>";
            
            // Оптимизиране на размера
            list($width, $height) = getimagesize($destination);
            $new_width = 800;
            $new_height = (int)($height * ($new_width / $width));
            
            $thumb = imagecreatetruecolor($new_width, $new_height);
            $source = imagecreatefromjpeg($destination);
            
            imagecopyresampled(
                $thumb,
                $source,
                0, 0, 0, 0,
                $new_width, $new_height,
                $width, $height
            );
            
            imagejpeg($thumb, $destination, 80);
            imagedestroy($thumb);
            imagedestroy($source);
            
            echo "<p class='text-success'>✓ Оптимизирано изображение: $filename</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>Грешка при изтегляне на $filename: " . $e->getMessage() . "</p>";
    }
}

?> 