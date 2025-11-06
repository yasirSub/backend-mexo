<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class ImageService
{
    /**
     * Resize and optimize image
     */
    public function resizeImage(UploadedFile $file, int $width = 800, int $height = 800): string
    {
        $image = Image::make($file);
        
        // Resize maintaining aspect ratio
        $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Optimize quality
        $image->save(null, 80);
        
        return $image->encode('jpg', 80);
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(UploadedFile $file, int $size = 300): string
    {
        $image = Image::make($file);
        
        $image->fit($size, $size);
        
        return $image->encode('jpg', 75);
    }
}
