<?php

namespace HealthCheckNotifier\Service\Monitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HealthCheckNotifier\Service\Monitor\Entity\HealthEntity;

class BackupMonitoring implements MonitoringService
{
    public function getHealthStatus()
    {
        try {
            $client = new Client([
                'base_uri' => $this->getBackupHost(),
                'verify' => false
            ]);
            $response = $client->request('GET', 'index-backup.php');

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

    public function getBackupHost()
    {
        return $_ENV['HEALTH_BACKUP_CHECK_HOST'] ?? $_ENV['HEALTH_CHECK_HOST'];
    }
}