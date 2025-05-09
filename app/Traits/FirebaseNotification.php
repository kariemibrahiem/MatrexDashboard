<?php

namespace App\Traits;

use App\Models\DeviceToken;
use App\Models\Notification;
use GuzzleHttp\Client;

trait FirebaseNotification
{
    protected function fcmUrl()
    {
        return "https://fcm.googleapis.com/v1/projects/ataaby-c45d7/messages:send";
    }

    public function sendFcm($data, $user_ids = [], $user_type = 'client_api', $additionalData = [])
    {
        $apiUrl = $this->fcmUrl();
        $accessToken = $this->getAccessToken();

        if ($user_ids && $user_type == 'client_api') {
            $deviceTokens = DeviceToken::query()->whereIn('user_id', $user_ids)->where('user_type', 'client')->pluck('token')->toArray();
        } elseif ($user_ids && $user_type == 'lawyer_api') {
            $deviceTokens = DeviceToken::query()->whereIn('user_id', $user_ids)->where('user_type', 'lawyer')->pluck('token')->toArray();
        } else {
            $deviceTokens = DeviceToken::query()->pluck('token')->toArray();
        }

        foreach ($user_ids as $user_id) {
            Notification::query()->create([
                'title' => $data['title'],
                'body' => $data['body'],
                'user_id' => $user_id,
                'user_type' => $user_type,
                'court_case_id' => isset($data['court_case_id']) ? $data['court_case_id'] : null,
            ]);
        }

        $responses = [];
        foreach ($deviceTokens as $token) {
            $payload = $this->preparePayload($data, $token, $additionalData);
            $responses[] = $this->sendNotification($apiUrl, $accessToken, $payload);
        }

        return response()->json(['responses' => $responses]);
    }

    protected function getAccessToken()
    {
        // Move this file outside public directory (e.g., storage/app/firebase.json)
        $credentialsFilePath = storage_path('app/firebase.json');

        if (!file_exists($credentialsFilePath)) {
            throw new \Exception('Firebase credentials file not found');
        }

        $credentials = json_decode(file_get_contents($credentialsFilePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in Firebase credentials');
        }

        $now = time();
        $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $jwtPayload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]);

        $jwtHeaderBase64 = $this->base64UrlEncode($jwtHeader);
        $jwtPayloadBase64 = $this->base64UrlEncode($jwtPayload);

        $signature = '';
        $privateKey = $credentials['private_key'];

        openssl_sign(
            "$jwtHeaderBase64.$jwtPayloadBase64",
            $signature,
            $privateKey,
            'sha256'
        );

        $jwtSignatureBase64 = $this->base64UrlEncode($signature);

        $jwt = "$jwtHeaderBase64.$jwtPayloadBase64.$jwtSignatureBase64";

        // Exchange JWT for access token
        $client = new Client();
        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        return $tokenData['access_token'];
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function preparePayload($data, $token, $additionalData = [])
    {
        // Ensure additionalData is an array (not an object)
        if (is_object($additionalData) && method_exists($additionalData, 'toArray')) {
            $additionalData = $additionalData->toArray();
        } elseif (!is_array($additionalData)) {
            $additionalData = [];
        }

        // Ensure all values are strings (FCM requires string values)
        array_walk_recursive($additionalData, function (&$item) {
            $item = (string)$item;
        });

        // Fix reserved keywords (like 'from')
        if (isset($additionalData['from'])) {
            $additionalData['from_custom'] = $additionalData['from'];
            unset($additionalData['from']);
        }

        // Build the correct FCM payload structure
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $data['title'] ?? '',
                    'body' => $data['body'] ?? '',
                ],
//                'data' => $additionalData,
            ],
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    protected function sendNotification($url, $accessToken, $payload)
    {
        $client = new Client();

        try {
            $response = $client->post($url, [
                'headers' => [
                    "Authorization" => "Bearer " . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => $payload,
            ]);


            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
