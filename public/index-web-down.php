<?php

use HealthCheckNotifier\Notifier\WebNotification;
use HealthCheckNotifier\Service\Monitor\WebMonitoring;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$webMonitoring = new WebMonitoring();
$webNotification = new WebNotification($webMonitoring->getHealthStatus());
$webNotification->notify();
