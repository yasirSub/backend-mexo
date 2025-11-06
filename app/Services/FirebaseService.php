<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

// Firebase is disabled for local development
// Uncomment when ready to use Firebase

/*
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
*/

class FirebaseService
{
    protected $auth;
    protected $messaging;

    public function __construct()
    {
        // Firebase disabled for local development
        /*
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->auth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
        */
    }

    /**
     * Verify Firebase ID token
     * Returns mock data for local development
     */
    public function verifyToken(string $token)
    {
        // For local development, return mock user data
        // TODO: Implement actual Firebase verification when ready
        return [
            'uid' => 'local-user-' . md5($token),
            'email' => 'seller@local.test'
        ];
        
        /*
        try {
            return $this->auth->verifyIdToken($token);
        } catch (\Exception $e) {
            return null;
        }
        */
    }

    /**
     * Send FCM notification
     * Disabled for local development
     */
    public function sendNotification(string $fcmToken, string $title, string $body, array $data = [])
    {
        // FCM disabled for local development
        Log::info('FCM Notification (local mode):', [
            'title' => $title,
            'body' => $body,
            'data' => $data
        ]);
        return true;
        
        /*
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data);

            return $this->messaging->send($message);
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
        */
    }
}
