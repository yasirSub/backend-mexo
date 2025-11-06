<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Mexo Seller API is running',
        'timestamp' => now()
    ]);
});

// Public storage file serving route (for images)
Route::get('/storage/{folder}/{filename}', [App\Http\Controllers\Api\ImageController::class, 'serve'])
    ->where(['folder' => '[a-zA-Z0-9_-]+', 'filename' => '[a-zA-Z0-9._-]+']);

// Public endpoint to return the requester's IP address
Route::get('/ip', function (Request $request) {
    return response()->json([
        'ip' => $request->ip(),
    ]);
});

// API Version 1 Routes
Route::prefix('v1')->group(function () {
    
    // Public routes (no authentication)
    Route::post('/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth routes
        Route::post('/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        
        // Seller Profile routes
        Route::prefix('seller')->group(function () {
            Route::get('/profile', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'show']);
            Route::post('/profile', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'store']);
            Route::put('/profile', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'update']);
            Route::post('/profile/logo', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'uploadLogo']);
            Route::post('/profile/bank-details', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'updateBankDetails']);
            // Store settings
            Route::get('/store-settings', [App\Http\Controllers\Api\V1\StoreSettingController::class, 'show']);
            Route::put('/store-settings', [App\Http\Controllers\Api\V1\StoreSettingController::class, 'update']);
        });
        
        // Product routes
        Route::apiResource('products', App\Http\Controllers\Api\V1\ProductController::class);
        Route::post('/products/{id}/images', [App\Http\Controllers\Api\V1\ProductController::class, 'uploadImages']);
        Route::delete('/products/{id}/images/{imageId}', [App\Http\Controllers\Api\V1\ProductController::class, 'deleteImage']);
        Route::patch('/products/{id}/status', [App\Http\Controllers\Api\V1\ProductController::class, 'updateStatus']);
        
        // Order routes
        Route::apiResource('orders', App\Http\Controllers\Api\V1\OrderController::class)->except(['create', 'store']);
        Route::post('/orders/{id}/accept', [App\Http\Controllers\Api\V1\OrderController::class, 'accept']);
        Route::post('/orders/{id}/reject', [App\Http\Controllers\Api\V1\OrderController::class, 'reject']);
        Route::patch('/orders/{id}/status', [App\Http\Controllers\Api\V1\OrderController::class, 'updateStatus']);
        Route::post('/orders/{id}/deliver', [App\Http\Controllers\Api\V1\OrderController::class, 'deliver']);
        
        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [App\Http\Controllers\Api\V1\DashboardController::class, 'stats']);
            Route::get('/analytics', [App\Http\Controllers\Api\V1\DashboardController::class, 'analytics']);
        });
        
        // Notification routes
        Route::get('/notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/unread-count', [App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
        
        // Category routes
        Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
    });
    
    // File upload route
    Route::middleware('auth:sanctum')->post('/upload/image', [App\Http\Controllers\Api\V1\UploadController::class, 'uploadImage']);
});

// Admin Panel Routes
Route::prefix('admin')->group(function () {
    
    // Public admin routes
    Route::post('/login', [App\Http\Controllers\Api\Admin\AuthController::class, 'login']);
    
    // Protected admin routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/logout', [App\Http\Controllers\Api\Admin\AuthController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Api\Admin\AuthController::class, 'me']);
        
        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);
            Route::get('/analytics', [App\Http\Controllers\Api\Admin\DashboardController::class, 'analytics']);
        });
        
        // Seller Management routes
        Route::prefix('sellers')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'show']);
            Route::get('/{id}/products', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'products']);
            Route::patch('/{id}/approve', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'approve']);
            Route::patch('/{id}/reject', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'reject']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\SellerManagementController::class, 'destroy']);
        });
        
        // Product Management routes
        Route::prefix('products')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'show']);
            Route::patch('/{id}/approve', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'approve']);
            Route::patch('/{id}/reject', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'reject']);
            Route::patch('/{id}/status', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'updateStatus']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\ProductManagementController::class, 'destroy']);
        });
        
        // Order Management routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\OrderManagementController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\OrderManagementController::class, 'show']);
            Route::patch('/{id}/status', [App\Http\Controllers\Api\Admin\OrderManagementController::class, 'updateStatus']);
            Route::get('/statistics/summary', [App\Http\Controllers\Api\Admin\OrderManagementController::class, 'statistics']);
        });
        
        // Category Management routes
        Route::prefix('categories')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\CategoryManagementController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\Admin\CategoryManagementController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\CategoryManagementController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Api\Admin\CategoryManagementController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\CategoryManagementController::class, 'destroy']);
        });
        
        // Payment Management routes
        Route::prefix('payments')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'index']);
            Route::get('/statistics', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'statistics']);
            Route::get('/seller-payouts', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'sellerPayouts']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'show']);
            Route::patch('/{id}/status', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'updateStatus']);
            Route::patch('/{id}/mark-paid', [App\Http\Controllers\Api\Admin\PaymentManagementController::class, 'markPaid']);
        });
        
        // Customer Management routes
        Route::prefix('customers')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\CustomerManagementController::class, 'index']);
            Route::get('/statistics', [App\Http\Controllers\Api\Admin\CustomerManagementController::class, 'statistics']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\CustomerManagementController::class, 'show']);
            Route::patch('/{id}/toggle-status', [App\Http\Controllers\Api\Admin\CustomerManagementController::class, 'toggleStatus']);
        });
        
        // Notification Management routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\NotificationController::class, 'index']);
            Route::get('/statistics', [App\Http\Controllers\Api\Admin\NotificationController::class, 'statistics']);
            Route::get('/unread-count', [App\Http\Controllers\Api\Admin\NotificationController::class, 'unreadCount']);
            Route::post('/create-test', [App\Http\Controllers\Api\Admin\NotificationController::class, 'createTest']);
            Route::patch('/{id}/read', [App\Http\Controllers\Api\Admin\NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [App\Http\Controllers\Api\Admin\NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\NotificationController::class, 'destroy']);
            Route::delete('/read/all', [App\Http\Controllers\Api\Admin\NotificationController::class, 'deleteAllRead']);
        });
    });
});

// Seller Routes
Route::prefix('seller')->group(function () {
    // Public seller routes
    Route::post('/register', [App\Http\Controllers\Api\Seller\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Api\Seller\AuthController::class, 'login']);

    // Protected seller routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\Seller\AuthController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Api\Seller\AuthController::class, 'me']);
        
        // Profile
        Route::get('/profile', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'show']);
        Route::put('/profile', [App\Http\Controllers\Api\V1\SellerProfileController::class, 'update']);
        
        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/statistics', [App\Http\Controllers\Api\DashboardController::class, 'getStatistics']);
            Route::get('/product/{productId}/analytics', [App\Http\Controllers\Api\DashboardController::class, 'getProductAnalytics']);
            Route::get('/inventory-alerts', [App\Http\Controllers\Api\DashboardController::class, 'getInventoryAlerts']);
        });

        // Image Upload
        Route::post('/upload/image', [App\Http\Controllers\Api\ImageController::class, 'upload']);
        Route::delete('/upload/image', [App\Http\Controllers\Api\ImageController::class, 'delete']);

        // Payments
        Route::prefix('payments')->group(function () {
            Route::post('/create-intent', [App\Http\Controllers\Api\PaymentController::class, 'createPaymentIntent']);
            Route::get('/{orderId}/status', [App\Http\Controllers\Api\PaymentController::class, 'getPaymentStatus']);
        });
        Route::post('/webhook/stripe', [App\Http\Controllers\Api\PaymentController::class, 'handleWebhook']);

        // Delivery
        Route::prefix('delivery')->group(function () {
            Route::post('/orders/{orderId}/tracking', [App\Http\Controllers\Api\DeliveryController::class, 'updateTracking']);
            Route::get('/orders/{orderId}/tracking', [App\Http\Controllers\Api\DeliveryController::class, 'getTracking']);
            Route::get('/orders/search', [App\Http\Controllers\Api\DeliveryController::class, 'searchOrders']);
        });
        
        // Products
        Route::apiResource('products', App\Http\Controllers\Api\Seller\ProductController::class);
    // Store settings (legacy non-v1 routes)
    Route::get('/store-settings', [App\Http\Controllers\Api\V1\StoreSettingController::class, 'show']);
    Route::put('/store-settings', [App\Http\Controllers\Api\V1\StoreSettingController::class, 'update']);
        
        // Orders
        Route::get('/orders', [App\Http\Controllers\Api\Seller\OrderController::class, 'index']);
        Route::get('/orders/{id}', [App\Http\Controllers\Api\Seller\OrderController::class, 'show']);
        Route::post('/orders/{id}/ship', [App\Http\Controllers\Api\Seller\OrderController::class, 'ship']);
        Route::post('/orders/{id}/deliver', [App\Http\Controllers\Api\Seller\OrderController::class, 'deliver']);
    });
});