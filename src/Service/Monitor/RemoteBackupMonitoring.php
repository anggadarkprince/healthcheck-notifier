<?php

namespace HealthCheckNotifier\Service\Monitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HealthCheckNotifier\Service\Monitor\Entity\HealthEntity;

class RemoteBackupMonitoring implements MonitoringService
{
    public function getHealthStatus()
    {
        try {
            $client = new Client([
                'base_uri' => $_ENV['HEALTH_CHECK_HOST'],
                'verify' => false
            ]);
            $response = $client->request('GET', 'index-remote-backup.php');

            return new HealthEntity(
                $response->getStatusCode(),
                json_decode($response->getBody(), true)
            );
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            return new HealthEntity(
                $response->getStatusCode(),
                json_decode($response->getBody()->getContents(), true)
            );
        }
    }
}