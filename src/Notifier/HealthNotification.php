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
                $healthCheck = $service['health_check'] ?? [];
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
                            $messages .= "   - {$member['MEMBER_HOST']} ({$member['MEMBER_STATE']}) " . ($member['MEMBER_STATE'] != 'ONLINE' ? "‼" : '') . "\n";
                        }
                        $messages .= "\n";
                        break;
                    case 'Object Storage':
                        $messages .= "*- Object Storage*\n";
                        $messages .= "   Status: {$service['health_check']['status']}\n";
                        $messages .= "   Message: {$service['health_check']['message']}\n";
                        $messages .= "   Capacity: " . ($service['health_check']['data']['reserved_space'] ?? 0) . ' ' . ($service['health_check']['data']['reserved_space_unit'] ?? '') . "\n";
                        $messages .= "   Total Usage: " . number_format($service['health_check']['data']['total_usage'] ?? 0, 1, ',', '.') . ' ' . ($service['health_check']['data']['total_usage_unit'] ?? '') . "\n";
                        $messages .= "   Total Left: " . number_format($service['health_check']['data']['total_left'] ?? 0, 1, ',', '.') . ' ' . ($service['health_check']['data']['total_left_unit'] ?? '') . "\n";
                        $messages .= "   Usage Percent: " . number_format($service['health_check']['data']['usage_percent'] ?? 0, 1, ',', '.') . "%" . ($service['health_check']['data']['usage_percent'] >= $_ENV['OBJECT_STORAGE_PERCENT_LIMIT'] ? "‼" : '') . "\n";
                        $messages .= "   Buckets: \n";
                        foreach ($service['health_check']['data']['buckets'] as $bucket) {
                            $messages .= "   - {$bucket['bucket_name']} (" . number_format($bucket['total'], 1, ',', '.') . " {$bucket['total_unit']}) \n";
                        }

                        $messages .= "\n";
                        break;
                    case 'Server':
                        $system =
                        $messages .= "*- Server*\n";
                        $messages .= "   Status: {$healthCheck['status']}\n";
                        $messages .= "   Message: {$healthCheck['message']}\n";
                        $messages .= "   OS: " . ($healthCheck['data']['system']['operating_system'] ?? 'Unavailable') . "\n";
                        $messages .= "   Host: " . ($healthCheck['data']['system']['static_hostname'] ?? 'Unavailable') . "\n";
                        $messages .= "   Virtualization: " . ($healthCheck['data']['system']['virtualization'] ?? 'Unavailable') . "\n";
                        $messages .= "   Memory: " . ($healthCheck['data']['memory']['total'] ?? 'Unavailable') . "\n";
                        $messages .= "   Disk: " . ($healthCheck['data']['disk']['total'] ?? 'Unavailable') . "\n";
                        $messages .= "     Disk Free: " . ($healthCheck['data']['disk']['free'] ?? 'Unavailable') . "\n";
                        $messages .= "     Disk Percent: " . number_format($healthCheck['data']['disk']['usage_percent'] ?? 0, 1, ',', '.') . "%" . ($healthCheck['data']['disk']['usage_percent'] >= $_ENV['SERVER_STORAGE_PERCENT_LIMIT'] ? "‼" : '') . "\n";
                        $messages .= "     Directory Report: \n";
                        foreach ($healthCheck['data']['disk']['directory_report']['contents'] as $dir => $size) {
                            $messages .= "     - {$dir} ({$size}) \n";
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