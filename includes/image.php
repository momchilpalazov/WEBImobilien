<?php
class ImageOptimizer {
    private $quality = 85;
    private $max_width = 1920;
    private $max_height = 1080;

    public function optimize($source_path, $destination_path = null) {
        if (!$destination_path) {
            $destination_path = $source_path;
        }

        list($width, $height, $type) = getimagesize($source_path);
        
        // Изчисляване на нови размери
        if ($width > $this->max_width || $height > $this->max_height) {
            $ratio = min($this->max_width / $width, $this->max_height / $height);
            $new_width = round($width * $ratio);
            $new_height = round($height * $ratio);
        } else {
            $new_width = $width;
            $new_height = $height;
        }

        // Създаване на ново изображение
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Зареждане на оригиналното изображение
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($source_path);
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }

        // Преоразмеряване
        imagecopyresampled(
            $new_image, $source,
            0, 0, 0, 0,
            $new_width, $new_height,
            $width, $height
        );

        // Запазване на оптимизираното изображение
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($new_image, $destination_path, $this->quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $destination_path, round($this->quality / 10));
                break;
            case IMAGETYPE_GIF:
                imagegif($new_image, $destination_path);
                break;
        }

        imagedestroy($new_image);
        imagedestroy($source);

        return true;
    }

    public function createThumbnail($source_path, $destination_path, $thumb_width = 300, $thumb_height = 200) {
        list($width, $height, $type) = getimagesize($source_path);
        
        $new_image = imagecreatetruecolor($thumb_width, $thumb_height);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($source_path);
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }

        // Изчисляване на размери за изрязване
        $ratio = max($thumb_width / $width, $thumb_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);
        $x = ($new_width - $thumb_width) / 2;
        $y = ($new_height - $thumb_height) / 2;

        imagecopyresampled(
            $new_image, $source,
            -$x, -$y, 0, 0,
            $new_width, $new_height,
            $width, $height
        );

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($new_image, $destination_path, $this->quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $destination_path, round($this->quality / 10));
                break;
            case IMAGETYPE_GIF:
                imagegif($new_image, $destination_path);
                break;
        }

        imagedestroy($new_image);
        imagedestroy($source);

        return true;
    }
} 