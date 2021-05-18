<?php

use HealthCheckNotifier\Notifier\ServerNotification;
use HealthCheckNotifier\Service\Monitor\ServerMonitoring;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$serverMonitoring = new ServerMonitoring();
$serverNotification = new ServerNotification($serverMonitoring->getHealthStatus());
$serverNotification->notify();
