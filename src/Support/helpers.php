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