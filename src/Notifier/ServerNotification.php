<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

class ServerNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();

        $notificationLogKey = 'server-down';
        $webDownNotification = get_notification_log($notificationLogKey) ?? [];
        $currentNotified = ($webDownNotification['total-notified'] ?? 0);
        $currentNotificationDate = ($webDownNotification['next-notification'] ?? '');

        if ($statusCode != 200) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {
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
        } else if ($data['data']['disk']['usage_percent'] >= ($_ENV['SERVER_STORAGE_PERCENT_LIMIT'] ?? 95)) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {
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