<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class TuyaService
{
    private Client $client;
    private string $endpoint;
    private string $clientId;
    private string $clientSecret;
    private string $homeId;

    public function __construct()
    {
        $this->client = new Client();
        $this->endpoint = config('services.tuya.endpoint');
        $this->clientId = config('services.tuya.client_id');
        $this->clientSecret = config('services.tuya.client_secret');
        $this->homeId = config('services.tuya.home_id');
    }

    public function getHomeId(): string
    {
        return $this->homeId;
    }

    private function getAccessToken(): string
    {
        $timestamp = (string) (time() * 1000);
        $nonce = "";

        $httpMethod = "GET";
        $sha256Body = "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855";
        $headers = "";
        $url = "/v1.0/token?grant_type=1";

        $stringToSign = $httpMethod . "\n" . $sha256Body . "\n" . $headers . "\n" . $url;
        $finalString = $this->clientId . $timestamp . $nonce . $stringToSign;
        $signStr = strtoupper(hash_hmac('sha256', $finalString, $this->clientSecret));

        try {
            $response = $this->client->get($this->endpoint . '/v1.0/token?grant_type=1', [
                'headers' => [
                    'client_id' => $this->clientId,
                    'sign' => $signStr,
                    't' => $timestamp,
                    'sign_method' => 'HMAC-SHA256',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['success']) || !$data['success']) {
                throw new \Exception('Tuya API error: ' . ($data['msg'] ?? 'Unknown error'));
            }

            return $data['result']['access_token'];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage());
        }

    }

    private function buildUrlWithSortedParams(string $path, array $queryParams): string
    {
        if (empty($queryParams)) {
            return $path;
        }

        ksort($queryParams);

        $pairs = [];
        foreach ($queryParams as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        return $path . '?' . implode('&', $pairs);
    }

    public function makeRequest(string $method, string $endpoint, array $data = [], array $queryParams = []): array | int
    {
        $accessToken = $this->getAccessToken();
        $timestamp = (string) (time() * 1000);
        $nonce = "";

        $httpMethod = strtoupper($method);
        $bodyContent = !empty($data) ? json_encode($data) : "";
        $sha256Body = hash('sha256', $bodyContent);
        $headers = "";

        $completeUrl = $this->buildUrlWithSortedParams($endpoint, $queryParams);

        $stringToSign = $httpMethod . "\n" . $sha256Body . "\n" . $headers . "\n" . $completeUrl;
        $finalString = $this->clientId . $accessToken . $timestamp . $nonce . $stringToSign;
        $signStr = strtoupper(hash_hmac('sha256', $finalString, $this->clientSecret));

        $options = [
            'headers' => [
                'client_id' => $this->clientId,
                'access_token' => $accessToken,
                'sign' => $signStr,
                't' => $timestamp,
                'sign_method' => 'HMAC-SHA256',
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        $response = $this->client->request($method, $this->endpoint . $completeUrl, $options);
        $result = json_decode($response->getBody(), true);

        if (!isset($result['success']) || !$result['success']) {
            throw new \Exception('Tuya API error: ' . ($result['msg'] ?? 'Unknown error'));
        }

        return $result['result'];
    }

    public function getAllDevices(): array
    {
        return Cache::remember('tuya_devices', app()->environment('production') ? 900 : 0, function () {
            return $this->makeRequest('GET', "/v1.0/homes/{$this->homeId}/devices");
        });
    }


    public function getRooms(): array
    {
        return Cache::remember('tuya_rooms', app()->environment('production') ? 1800 : 0, function () {
            $result = $this->makeRequest('GET', "/v1.0/homes/{$this->homeId}/rooms");

            if (isset($result['rooms']) && is_array($result['rooms'])) {
                return $result['rooms'];
            }

            return [];
        });
    }

    public function getRoomDevices(string $roomId): array
    {
        $cacheKey = "tuya_room_devices_{$roomId}";
        return Cache::remember($cacheKey, app()->environment('production') ? 900 : 0, function () use ($roomId) {
            return $this->makeRequest('GET', "/v1.0/homes/{$this->homeId}/rooms/{$roomId}/devices");
        });
    }
}
