<?php

use HealthCheckNotifier\Notifier\HealthNotification;
use HealthCheckNotifier\Service\Monitor\HealthMonitoring;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$healthMonitoring = new HealthMonitoring();
$healthNotification = new HealthNotification($healthMonitoring->getHealthStatus());
$healthNotification->notify();
