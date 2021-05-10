<?php

namespace HealthCheckNotifier\Service\Notification;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WhatsappChatter implements NotificationService
{
    /**
     * Implementation sending chat.
     *
     * @param $payload
     * @return false|mixed
     */
    public function send($payload)
    {
        $baseUri = $_ENV['CHAT_API_URL'];
        $chatApiToken = $_ENV['CHAT_API_TOKEN'];
        $chatApiSecure = $_ENV['CHAT_API_SECURE'];
        $chatApiSandbox = $_ENV['CHAT_API_SANDBOX_NUMBER'];
        $chatMethod = $payload['method'] ?? 'post';
        $chatUrl = $payload['url'] ?? '/';
        $chatPayload = $payload['payload'];

        if (!key_exists('token', $payload)) {
            $chatPayload['token'] = $chatApiToken;
        }
        if (!empty($chatApiSandbox)) {
            $chatPayload['chatId'] = detect_chat_id($chatApiSandbox);
        }

        try {
            $client = new Client([
                'base_uri' => $baseUri,
                'verify' => boolval($chatApiSecure)
            ]);
            $response = $client->request($chatMethod, $chatUrl, [
                'query' => ['token' => $chatApiToken],
                'form_params' => $chatPayload
            ]);
            return json_decode($response->getBody(), true);

        } catch (GuzzleException $e) {
            return false;
        }
    }
}