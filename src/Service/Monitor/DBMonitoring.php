<?php

namespace HealthCheckNotifier\Service\Monitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HealthCheckNotifier\Service\Monitor\Entity\HealthEntity;

class DBMonitoring implements MonitoringService
{
    public function getHealthStatus()
    {
        try {
            $client = new Client([
                'base_uri' => $_ENV['HEALTH_CHECK_HOST'],
                'verify' => false
            ]);
            $response = $client->request('GET', 'index-db.php', [
                'headers' => ['Cache-Control' => [
                    'no-store',
                    'no-cache',
                    'must-revalidate',
                    'max-age=0',
                ]]
            ]);

            return new HealthEntity(
                $response->getStatusCode(),
                json_decode($response->getBody(), true)
            );
        } catch (GuzzleException $e) {
            return false;
        }
    }
}