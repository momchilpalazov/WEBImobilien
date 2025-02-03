<?php

// Масив с URL адреси на примерни изображения
$images = [
    'blog' => [
        'https://source.unsplash.com/800x600/?industrial,warehouse',
        'https://source.unsplash.com/800x600/?factory',
        'https://source.unsplash.com/800x600/?logistics',
        'https://source.unsplash.com/800x600/?industry',
        'https://source.unsplash.com/800x600/?manufacturing'
    ],
    'properties' => [
        'https://source.unsplash.com/1200x800/?warehouse',
        'https://source.unsplash.com/1200x800/?industrial+building',
        'https://source.unsplash.com/1200x800/?storage+facility',
        'https://source.unsplash.com/1200x800/?industrial+park',
        'https://source.unsplash.com/1200x800/?factory+building'
    ]
];

// Функция за изтегляне и запазване на изображение
function downloadImage($url, $directory, $index) {
    $extension = '.jpg';
    $filename = $directory . '/' . basename($directory) . '_' . ($index + 1) . $extension;
    
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    try {
        $image = file_get_contents($url);
        if ($image !== false) {
            file_put_contents($filename, $image);
            echo "<p class='text-success'>✓ Изтеглено изображение: $filename</p>";
            
            // Създаване на умалено изображение за properties
            if ($directory === 'uploads/properties') {
                createThumbnail($filename, 'uploads/properties/thumbnails/' . basename($filename), 300, 200);
            }
            
            return true;
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>Грешка при изтегляне на $url: " . $e->getMessage() . "</p>";
    }
    
    return false;
}

// Функция за създаване на умалено изображение
function createThumbnail($source, $destination, $width, $height) {
    list($src_width, $src_height) = getimagesize($source);
    
    $thumb = imagecreatetruecolor($width, $height);
    $source_img = imagecreatefromjpeg($source);
    
    imagecopyresampled(
        $thumb, 
        $source_img, 
        0, 0, 0, 0, 
        $width, $height, 
        $src_width, $src_height
    );
    
    imagejpeg($thumb, $destination, 80);
    imagedestroy($thumb);
    imagedestroy($source_img);
    
    echo "<p class='text-success'>✓ Създадено умалено изображение: " . basename($destination) . "</p>";
}

// Изтегляне на изображенията
foreach ($images as $directory => $urls) {
    echo "<h4>Изтегляне на изображения за $directory</h4>";
    
    foreach ($urls as $index => $url) {
        downloadImage($url, "uploads/$directory", $index);
    }
}

?> 