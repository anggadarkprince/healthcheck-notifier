<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$logFile = __DIR__ . '/../logs/notification-test.ini';

set_notification_log([
    'server-down.total-notified' => 2,
    'server-down.next-notification' => '2021-05-20 09:30:00',
    'web-down' => [
        'total-notified' => 1,
        'next-notification' => '2021-05-18 09:30:00'
    ],
    'db-down' => [
        'total-notified' => 3,
        'next-notification' => '2021-05-22 10:00:00'
    ],
], $logFile);

var_dump(get_notification_log(null, $logFile));
var_dump(get_notification_log('server-down.next-notification', $logFile));
var_dump(get_notification_log('web-down', $logFile));
