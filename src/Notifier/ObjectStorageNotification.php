<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class ObjectStorageNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode != 200) {
            $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
            $messages .= "——————————————————\n";
            $messages .= "*Service Name*: Object Storage\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*Endpoint*: " . ($data['data']['endpoint'] ?? 'Unavailable') . "\n";
            $messages .= "*Region*: " . ($data['data']['region'] ?? 'Unavailable') . "\n";
            $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";

            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
        } else if ($data['data']['usage_percent'] >= ($_ENV['OBJECT_STORAGE_PERCENT_LIMIT'] ?? 95)) {
            $messages = "❌ *INSUFFICIENT S3 STORAGE* ❌\n";
            $messages .= "————————————————————\n";
            $messages .= "*Service Name*: Object Storage\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*Endpoint*: " . ($data['data']['endpoint'] ?? 'Unavailable') . "\n";
            $messages .= "*Region*: " . ($data['data']['region'] ?? 'Unavailable') . "\n";
            $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";
            $messages .= "*Capacity*: " . ($data['data']['reserved_space'] ?? 0) . ' ' . ($data['data']['reserved_space_unit'] ?? '') . "\n";
            $messages .= "*Total Usage*: " . number_format($data['data']['total_usage'] ?? 0, 1, ',', '.') . ' ' . ($data['data']['total_usage_unit'] ?? '') . "\n";
            $messages .= "*Total Left*: " . number_format($data['data']['total_left'] ?? 0, 1, ',', '.') . ' ' . ($data['data']['total_left_unit'] ?? '') . "\n";
            $messages .= "*Usage Percent*: " . number_format($data['data']['usage_percent'] ?? 0, 1, ',', '.') . "%\n";

            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
        }
    }
}