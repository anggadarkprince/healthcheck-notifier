<?php

use HealthCheckNotifier\Service\Notification\WhatsappChatter;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$waChatter = new WhatsappChatter();
$result = $waChatter->send([
    'url' => 'sendMessage',
    'payload' => [
        'to_number' => detect_chat_id('6285655479868'),
        'message' => "Test chat whatsapp 👷🏼"
    ]
]);

var_dump($result);