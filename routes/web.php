<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImageController;

Route::get('/', function () {
    return view('welcome');
});

// Public storage file serving route (for direct access to /storage/ URLs)
Route::get('/storage/{folder}/{filename}', [ImageController::class, 'serve'])
    ->where(['folder' => '[a-zA-Z0-9_-]+', 'filename' => '[a-zA-Z0-9._-]+']);
