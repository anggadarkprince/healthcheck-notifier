<?php


namespace HealthCheckNotifier\Service\Monitor\Entity;


class HealthEntity
{
    private $statusCode;
    private $data;

    public function __construct($statusCode = 200, $data = [])
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}