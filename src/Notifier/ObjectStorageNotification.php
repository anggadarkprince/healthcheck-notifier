<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;
use Monolog\Logger;

class ObjectStorageNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();

        $notificationLogKey = 'object-storage-down';
        $webDownNotification = get_notification_log($notificationLogKey) ?? [];
        $currentNotified = ($webDownNotification['total-notified'] ?? 0);
        $currentNotificationDate = ($webDownNotification['next-notification'] ?? '');

        if ($statusCode != 200) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {
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
                log_message('Service [Object Storage] Unavailable', $data);

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
        } else if ($data['data']['usage_percent'] >= ($_ENV['OBJECT_STORAGE_PERCENT_LIMIT'] ?? 95)) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {
                $messages = "❌ *INSUFFICIENT S3 STORAGE* ❌\n";
                $messages .= "————————————————————\n";
                $messages .= "*Service Name*: Object Storage\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Endpoint*: " . ($data['data']['endpoint'] ?? 'Unavailable') . "\n";
                $messages .= "*Region*: " . ($data['data']['region'] ?? 'Unavailable') . "\n";
                $messages .= "*Status*: 500\n";
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
                log_message('Service [Object Storage] Insufficient Storage', $data);

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
                $messages = "✅ *OBJECT STORAGE RESTORED*\n";
                $messages .= "————————————————————\n";
                $messages .= "*Service Name*: Object Storage\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
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
                log_message('Service [Object Storage] Restored', $data, Logger::INFO);
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