<?php

use HealthCheckNotifier\Notifier\DBReplicationNotification;
use HealthCheckNotifier\Service\Monitor\DBReplicationMonitoring;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$DBReplicationMonitoring = new DBReplicationMonitoring();
$DBReplicationNotification = new DBReplicationNotification($DBReplicationMonitoring->getHealthStatus());
$DBReplicationNotification->notify();
