<?php

use HealthCheckNotifier\Service\Notification\Mailer;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mailer = new Mailer();
$result = $mailer->send([
    'subject' => 'Send Email Test',
    'to' => 'receiver@example.com',
    'message' => 'This is email test'
]);

var_dump($result);