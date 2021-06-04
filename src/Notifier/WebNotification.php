<?php
namespace HealthCheckNotifier\Notifier;

use HealthCheckNotifier\Service\Notification\WhatsappChatter;
use Monolog\Logger;

class WebNotification extends NotificationResponse
{
    public function notify()
    {
        $data = $this->healthEntity->getData();
        $statusCode = $this->healthEntity->getStatusCode();

        $notificationLogKey = 'web-down';
        $webDownNotification = get_notification_log($notificationLogKey) ?? [];
        $currentNotified = ($webDownNotification['total-notified'] ?? 0);
        $currentNotificationDate = ($webDownNotification['next-notification'] ?? '');

        if ($statusCode != 200) {
            if (empty($currentNotificationDate) || format_date($currentNotificationDate, 'Y-m-d H:i') == date('Y-m-d H:i')) {

                $messages = "❌ *SERVICE UNAVAILABLE* ❌\n";
                $messages .= "——————————————————\n";
                $messages .= "*Service Name*: Web\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Host*: " . ($data['data']['host'] ?? 'Unavailable') . "\n";
                $messages .= "*Web Server*: " . ($data['data']['web_server'] ?? 'Unavailable') . "\n";
                $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";

                $waChatter = new WhatsappChatter();
                $waChatter->send([
                    'url' => 'sendMessage',
                    'payload' => [
                        'chatId' => detect_chat_id($_ENV['HEALTH_CHAT_REPORT']),
                        'body' => $messages
                    ]
                ]);
                log_message('Service [Web] Unavailable', $data);

                // log for next notification
                $totalNotified = $currentNotified + 1;
                $addMinutes = get_exp_minute($totalNotified);
                //$addMinutes = (ceil(exp($totalNotified) * 10 / 10) * 10);
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
                $messages = "✅ *WEB RESTORED*\n";
                $messages .= "————————————————————\n";
                $messages .= "*Service Name*: Web\n";
                $messages .= "*Health Check*: " . date('Y-m-d H:i:s') . "\n";
                $messages .= "*Host*: " . ($data['data']['host'] ?? 'Unavailable') . "\n";
                $messages .= "*Web Server*: " . ($data['data']['web_server'] ?? 'Unavailable') . "\n";
                $messages .= "*Status*: " . ($statusCode ?? 500) . "\n";

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