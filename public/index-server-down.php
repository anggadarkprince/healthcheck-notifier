<?php

use HealthCheckNotifier\Notifier\ServerNotification;
use HealthCheckNotifier\Service\Monitor\ServerMonitoring;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$serverMonitoring = new ServerMonitoring();
$serverNotification = new ServerNotification($serverMonitoring->getHealthStatus());
$serverNotification->notify();
