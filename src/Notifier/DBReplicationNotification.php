<?php

namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;
use Monolog\Logger;

class DBReplicationNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();

        $notificationLogKey = 'db-replication-down';
        $webDownNotification = get_notification_log($notificationLogKey) ?? [];
        $currentNotified = ($webDownNotification['total-notified'] ?? 0);
        $currentNotificationDate = ($webDownNotification['next-notification'] ?? '');

        if ($statusCode != 200) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {
                $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
                $messages .= "——————————————————\n";
                $messages .= "*Service Name*: Database Replication\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";
                $messages .= "*Members*: \n";
                foreach ($data['data']['members'] as $index => $node) {
                    if ($node['MEMBER_HOST'] == $_ENV['NODE_PRIMARY_IP_ADDRESS']) {
                        $node['MEMBER_HOST'] = 'PRIMARY';
                    } else {
                        $node['MEMBER_HOST'] = 'SLAVE';
                    }
                    $messages .= "- Node {$index}: {$node['MEMBER_HOST']} ({$node['MEMBER_STATE']})\n";
                }
                $messages .= "- OTHER SLAVES (OFFLINE)\n";
                $waChatter = new WhatsappChatter();
                $waChatter->send([
                    'url' => 'sendMessage',
                    'payload' => [
                        'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                        'body' => $messages
                    ]
                ]);
                log_message('Service [Database Replication] Unavailable', $data);

                // log for next notification
                $totalNotified = $currentNotified + 1;
                $addMinutes = get_exp_minute($totalNotified);
                $nextNotification = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +" . $addMinutes . " minutes"));

                $notification = get_notification_log(null);
                $notification[$notificationLogKey] = [
                    'total-notified' => $totalNotified,
                    'next-notification' => $nextNotification
                ];
                set_notification_log($notification);
            }
        } else {
            if (!empty($webDownNotification['total-notified'])) {
                $messages = "✅ *DB REPLICATION RESTORED*\n";
                $messages .= "————————————————————\n";
                $messages .= "*Service Name*: Database Replication\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";
                $messages .= "*Members*: \n";
                foreach ($data['data']['members'] as $index => $node) {
                    if ($node['MEMBER_HOST'] == $_ENV['NODE_PRIMARY_IP_ADDRESS']) {
                        $node['MEMBER_HOST'] = 'PRIMARY';
                    } else {
                        $node['MEMBER_HOST'] = 'SLAVE';
                    }
                    $messages .= "- Node {$index}: {$node['MEMBER_HOST']} ({$node['MEMBER_STATE']})\n";
                }

                $waChatter = new WhatsappChatter();
                $waChatter->send([
                    'url' => 'sendMessage',
                    'payload' => [
                        'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                        'body' => $messages
                    ]
                ]);
                log_message('Service [Web] Restored', $data, Logger::INFO);
            }

            // reset notification
            $notification = get_notification_log(null);
            $notification[$notificationLogKey] = [
                'total-notified' => 0,
                'next-notification' => ""
            ];
            set_notification_log($notification);
        }
    }
}