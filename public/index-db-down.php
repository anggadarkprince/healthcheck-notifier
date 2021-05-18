<?php

use HealthCheckNotifier\Notifier\DBNotification;
use HealthCheckNotifier\Service\Monitor\DBMonitoring;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$DBMonitoring = new DBMonitoring();
$DBNotification = new DBNotification($DBMonitoring->getHealthStatus());
$DBNotification->notify();
