<?php

use HealthCheckNotifier\Notifier\WebNotification;
use HealthCheckNotifier\Service\Monitor\WebMonitoring;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$webMonitoring = new WebMonitoring();
$webNotification = new WebNotification($webMonitoring->getHealthStatus());
$webNotification->notify();
