<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\DeviceToken;
use App\Models\NotificationLog;

class FcmService
{
    private $messaging;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
        $this->messaging = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createMessaging();
    }

    public function sendToToken(string $token, array $notification, array $data = []): bool
    {
        try {
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => $notification,
                'data' => $this->prepareData($data),
            ]);

            $response = $this->messaging->send($message);

            $this->logNotification($data, $notification, $response, 'success');

            return true;

        } catch (\Exception $e) {
            $this->logNotification($data, $notification, ['error' => $e->getMessage()],'failed');

            return false;
        }
    }

    public function sendMulticast(array $tokens, array $notification, array $data = []): array
    {
        $success = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $this->sendToToken($token, $notification, $data) ? $success++ : $failed++;
        }

        return ['success' => $success, 'failed' => $failed];
    }

    private function prepareData(array $data): array
    {
        $prepared = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, ['user_id', 'device_token_id'])) {
                $prepared[$key] = (string) $value;
            }
        }

        return $prepared;
    }

    private function logNotification(array $data, array $notification, mixed $response, string $status): void
    {
        NotificationLog::create([
            'user_id' => $data['user_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'device_token_id' => $data['device_token_id'] ?? null,
            'payload' => $notification,
            'response' => $response,
            'notification_status' => $status,
            'sent_at' => now(),
        ]);
    }
}
