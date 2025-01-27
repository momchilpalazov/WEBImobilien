<?php

class ImageGenerator {
    public static function generatePropertyImages($propertyId, $count) {
        $uploadDir = __DIR__ . '/../../public/uploads/properties/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        for ($i = 0; $i < $count; $i++) {
            $width = 1200;
            $height = 800;
            $image = imagecreatetruecolor($width, $height);
            
            // Генериране на случаен цвят за фона
            $bgColor = imagecolorallocate($image, 
                rand(200, 255), 
                rand(200, 255), 
                rand(200, 255)
            );
            imagefill($image, 0, 0, $bgColor);
            
            // Добавяне на текст
            $textColor = imagecolorallocate($image, 50, 50, 50);
            $text = "Property #{$propertyId}\nImage #{$i}";
            imagettftext(
                $image,
                24,
                0,
                $width/3,
                $height/2,
                $textColor,
                __DIR__ . '/../../public/assets/fonts/Roboto-Regular.ttf',
                $text
            );
            
            $filename = "property_{$propertyId}_image_{$i}.jpg";
            imagejpeg($image, $uploadDir . $filename, 90);
            imagedestroy($image);
            
            // Създаване на миниатюра
            $thumb = imagecreatetruecolor(300, 200);
            $source = imagecreatefromjpeg($uploadDir . $filename);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, 300, 200, $width, $height);
            
            $thumbsDir = $uploadDir . 'thumbnails/';
            if (!is_dir($thumbsDir)) {
                mkdir($thumbsDir, 0777, true);
            }
            
            imagejpeg($thumb, $thumbsDir . $filename, 80);
            imagedestroy($thumb);
            imagedestroy($source);
        }
    }
} 