<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

log_message('Test Service [Database] Unavailable', [
    'status' => 500,
    'message' => 'Service unavailable'
]);