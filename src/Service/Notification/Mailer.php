<?php

namespace HealthCheckNotifier\Service\Notification;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer implements NotificationService
{
    /**
     * Implementation sending email.
     *
     * @param $payload
     * @return int
     */
    public function send($payload)
    {
        $host = $_ENV['MAIL_HOST'];
        $port = $_ENV['MAIL_PORT'];
        $username = $_ENV['MAIL_USERNAME'];
        $password = $_ENV['MAIL_PASSWORD'];
        $defaultName = $_ENV['MAIL_FROM_NAME'];
        $defaultEmail = $_ENV['MAIL_FROM_ADDRESS'];

        $transport = (new Swift_SmtpTransport($host, $port))
            ->setUsername($username)
            ->setPassword($password);

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message($payload['subject'] ?? 'No Subject'))
            ->setFrom($payload['from'] ?? [$defaultEmail => $defaultName])
            ->setTo($payload['to'] ?? '')
            ->setBody($payload['message'] ?? '');

        return $mailer->send($message);
    }
}