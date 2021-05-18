<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!function_exists('detect_chat_id')) {

    function detect_chat_id($chatId)
    {
        $chatId = str_replace(['-', ' ', '+'], '', $chatId);
        if (strpos($chatId, '-') !== false) {
            if (!(strpos($chatId, '@g.us') !== false)) {
                $chatId .= '@g.us';
            }
        } else if (!(strpos($chatId, '@c.us') !== false)) {
            $chatId = preg_replace('/^08/', '628', $chatId);
            $chatId .= '@c.us';
        }

        return $chatId;
    }
}

if (!function_exists('log_message')) {

    function log_message($message, $data = [], $level = Logger::ALERT)
    {
        $log = new Logger('app-logger');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/logs.log', $level));

        $log->alert($message, $data);
    }
}

if (!function_exists('get_notification_log')) {

    function get_notification_log($key = null, $logFile = __DIR__ . '/../../logs/notification.ini')
    {
        if (file_exists($logFile)) {
            $result = parse_ini_file($logFile, true);
            if (empty($key)) {
                return $result;
            }
            return $result[$key] ?? '';
        } else {
            fopen($logFile, "w");
            return [];
        }
    }
}

if (!function_exists('set_notification_log')) {

    function set_notification_log($array, $logFile = __DIR__ . '/../../logs/notification.ini')
    {
        get_notification_log(null, $logFile);

        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $subKey => $subVal) {
                    $res[] = "$subKey = " . (is_numeric($subVal) ? $subVal : '"' . $subVal . '"');
                }
            } else {
                $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
            }
        }

        if ($fp = fopen($logFile, 'w')) {
            $startTime = microtime(TRUE);
            do {
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                if (!$canWrite) usleep(round(rand(0, 100) * 1000));
            } while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

            // file was locked so now we can store information
            if ($canWrite) {
                fwrite($fp, implode("\r\n", $res));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
}