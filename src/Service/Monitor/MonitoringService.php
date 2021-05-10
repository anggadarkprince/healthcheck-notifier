<?php

namespace HealthCheckNotifier\Service\Monitor;

interface MonitoringService
{
    public function getHealthStatus();
}