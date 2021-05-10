<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class DBNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode == 200) {
            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => "❌ *SERVICE UNAVAILABLE* ❌

*Service Name*: Database️
*Check*: " . date('Y-m-d H:i:s') . "
*Host*: " . ($data['data']['host'] ?? 'Unavailable') . "
*Status*: " . ($statusCode ?? 500) . "
*Response*: \n" . json_encode($data, JSON_PRETTY_PRINT)
                ]
            ]);
        }
    }
}