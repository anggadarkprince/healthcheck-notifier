<?php

use HealthCheckNotifier\Notifier\ObjectStorageNotification;
use HealthCheckNotifier\Service\Monitor\ObjectStorageMonitoring;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$objectStorageMonitoring = new ObjectStorageMonitoring();
$objectStorageNotification = new ObjectStorageNotification($objectStorageMonitoring->getHealthStatus());
$objectStorageNotification->notify();
