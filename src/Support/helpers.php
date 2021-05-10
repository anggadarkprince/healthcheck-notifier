<?php

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