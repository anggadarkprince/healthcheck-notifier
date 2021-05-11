<?php

namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class DBReplicationNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode != 200) {
            $isOfflineNodes = false;
            foreach ($data['data']['members'] as $node) {
                if ($node['MEMBER_STATE'] == 'ONLINE') {
                    $isOfflineNodes = true;
                    break;
                }
            }
            if ($isOfflineNodes) {
                $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
                $messages .= "——————————————————\n";
                $messages .= "*Service Name*: Database Replication\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";
                $messages .= "*Members*: \n";
                foreach ($data['data']['members'] as $node) {
                    $messages .= "- {$node['MEMBER_HOST']} ({$node['MEMBER_STATE']})\n";
                }
                $waChatter = new WhatsappChatter();
                $waChatter->send([
                    'url' => 'sendMessage',
                    'payload' => [
                        'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                        'body' => $messages
                    ]
                ]);
                log_message('Service [Database Replication] Unavailable', $data);
            }
        }
    }
}