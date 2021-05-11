<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class DBNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode != 200) {
            $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
            $messages .= "——————————————————\n";
            $messages .= "*Service Name*: Database\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*Host*: " . ($data['data']['host'] ?? 'Unavailable') . "\n";
            $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";

            $waChatter = new WhatsappChatter();
            $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
            log_message('Service [Database] Unavailable', $data);
        }
    }
}