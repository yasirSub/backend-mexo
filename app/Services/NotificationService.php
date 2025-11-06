<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

// Firebase disabled for local development
// use App\Services\FirebaseService;

class NotificationService
{
    // protected $firebaseService;

    public function __construct()
    {
        // Firebase disabled for local development
        // $this->firebaseService = $firebaseService;
    }

    /**
     * Create and send notification
     * Works offline/local - saves to database only
     */
    public function sendNotification($sellerId, string $type, string $title, string $message, array $data = [])
    {
        // Save to database (local mode)
        $notification = Notification::create([
            'seller_id' => $sellerId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);

        // FCM push notifications disabled for local development
        // TODO: Enable when ready for production
        // if ($seller->fcm_token) {
        //     $this->firebaseService->sendNotification($seller->fcm_token, $title, $message, $data);
        // }

        Log::info('Notification created (local mode)', [
            'notification_id' => $notification->id,
            'seller_id' => $sellerId,
            'type' => $type
        ]);

        return $notification;
    }
}
