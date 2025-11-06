<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class S3Service
{
    protected $disk;

    public function __construct()
    {
        // Use local storage for local development
        // Change to 's3' when ready for production
        $this->disk = Storage::disk('public');
    }

    /**
     * Upload file to storage (local or S3)
     */
    public function uploadFile(UploadedFile $file, string $path): string
    {
        $filename = time() . '_' . Str::slug($file->getClientOriginalName());
        $filePath = $path . '/' . $filename;
        
        $this->disk->put($filePath, file_get_contents($file));
        
        // Return public URL for local storage
        // For S3, this would return S3 URL
        return asset('storage/' . $filePath);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $path): bool
    {
        return $this->disk->delete($path);
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $path): string
    {
        // For local storage, return asset URL
        // For S3, this would return S3 URL
        return asset('storage/' . $path);
    }
}
