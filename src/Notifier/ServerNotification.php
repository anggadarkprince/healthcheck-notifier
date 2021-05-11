<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class ServerNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode != 200) {
            $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
            $messages .= "——————————————————\n";
            $messages .= "*Service Name*: Server\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*OS*: " . ($data['data']['system']['operating_system'] ?? 'Unavailable') . "\n";
            $messages .= "*Host*: " . ($data['data']['system']['static_hostname'] ?? 'Unavailable') . "\n";
            $messages .= "*Virtualization*: " . ($data['data']['system']['virtualization'] ?? 'Unavailable') . "\n";
            $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";

            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
            log_message('Service [Server] Unavailable', $data);
        } else if ($data['data']['disk']['usage_percent'] >= ($_ENV['SERVER_STORAGE_PERCENT_LIMIT'] ?? 95)) {
            $messages = "❌ *INSUFFICIENT STORAGE* ❌\n";
            $messages .= "———————————————————\n";
            $messages .= "*Service Name*: Server\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";
            $messages .= "*Capacity*: " . ($data['data']['disk']['total'] ?? 0) . "\n";
            $messages .= "*Free*: " . ($data['data']['disk']['free'] ?? 0) . "\n";
            $messages .= "*Usage Percent*: " . number_format($data['data']['disk']['usage_percent'] ?? 0, 1, ',', '.') . "%\n";

            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
            log_message('Service [Server] Insufficient Storage', $data);
        }
    }
}