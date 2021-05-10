<?php

namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class HealthNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();
        if ($statusCode == 200) {
            $messages = "📈 *SERVICES HEALTH CHECK*\n";
            $messages .= "——————————————————\n";
            $messages .= "*Total Services*: {$data['total_service']}\n";
            $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
            $messages .= "*System Statuses*: \n\n";
            //$messages .= "*Response*: \n";
            //$messages .= json_encode($data['services'], JSON_PRETTY_PRINT);

            foreach ($data['services'] as $service) {
                switch ($service['service_name']) {
                    case 'Web':
                        $messages .= "*- Web*\n";
                        $messages .= "   Status: {$service['health_check']['status']}\n";
                        $messages .= "   Message: {$service['health_check']['message']}\n";
                        $messages .= "   Host: {$service['health_check']['data']['host']}\n";
                        $messages .= "\n";
                        break;
                    case 'DB':
                        $messages .= "*- DB*\n";
                        $messages .= "   Status: {$service['health_check']['status']}\n";
                        $messages .= "   Message: {$service['health_check']['message']}\n";
                        $messages .= "   Host: {$service['health_check']['data']['host']}\n";
                        $messages .= "\n";
                        break;
                    case 'DB Replication':
                        $messages .= "*- DB Replication*\n";
                        $messages .= "   Status: {$service['health_check']['status']}\n";
                        $messages .= "   Message: {$service['health_check']['message']}\n";
                        $messages .= "   Members: \n";
                        foreach ($service['health_check']['data']['members'] as $member) {
                            $messages .= "   - {$member['MEMBER_HOST']} ({$member['MEMBER_STATE']}) \n";
                        }
                        $messages .= "\n";
                        break;
                }
            }

            $waChatter = new WhatsappChatter();
            return $waChatter->send([
                'url' => 'sendMessage',
                'payload' => [
                    'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                    'body' => $messages
                ]
            ]);
        }
        return false;
    }
}