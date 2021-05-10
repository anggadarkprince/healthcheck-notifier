<?php

namespace HealthCheckNotifier\Notifier;

abstract class NotificationResponse
{
    protected $healthEntity;

    public function __construct($healthEntity)
    {
        $this->healthEntity = $healthEntity;
    }

    public abstract function notify();
}