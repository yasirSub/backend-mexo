<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required|in:product,profile,logo'
        ]);

        $image = $request->file('image');
        // Store in public disk (storage/app/public/{type}/)
        $path = $image->store($request->type, 'public');
        
        // Build the relative path directly (e.g., "/storage/product/filename.jpg")
        // The path from store() is like "product/filename.jpg", so we add /storage/ prefix
        $relativePath = '/storage/' . $path;
        
        // Build full URL using the request's scheme and host (not APP_URL from config)
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();
        $baseUrl = $scheme . '://' . $host . ($port && $port != 80 && $port != 443 ? ':' . $port : '');
        $url = $baseUrl . $relativePath;
        
        return response()->json([
            'url' => $url,
            'path' => $path
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        // Use public disk to delete files
        if (Storage::disk('public')->exists($request->path)) {
            Storage::disk('public')->delete($request->path);
            return response()->json(['message' => 'Image deleted successfully']);
        }

        return response()->json(['message' => 'Image not found'], 404);
    }

    /**
     * Serve storage files publicly
     */
    public function serve(Request $request, $folder, $filename)
    {
        $path = storage_path("app/public/{$folder}/{$filename}");
        
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }
        
        $file = file_get_contents($path);
        $type = mime_content_type($path) ?: 'application/octet-stream';
        
        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
    }
}