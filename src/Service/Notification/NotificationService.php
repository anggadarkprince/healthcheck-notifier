<?php

namespace HealthCheckNotifier\Service\Notification;

interface NotificationService
{
    public function send($payload);
}