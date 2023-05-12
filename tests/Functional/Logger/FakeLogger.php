<?php

declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Logger;

use Psr\Log\LoggerInterface;

class FakeLogger implements LoggerInterface
{
    private array $storage = [];
    
    public function emergency($message, array $context = array())
    {
        $this->storage['emergency'][]['message'] = $message;
        $this->storage['emergency'][]['context'] = $context;
    }

    public function alert($message, array $context = array())
    {
        $this->storage['alert'][]['message'] = $message;
        $this->storage['alert'][]['context'] = $context;
    }

    public function critical($message, array $context = array())
    {
        $this->storage['critical'][]['message'] = $message;
        $this->storage['critical'][]['context'] = $context;
    }

    public function error($message, array $context = array())
    {
        $this->storage['error'][]['message'] = $message;
        $this->storage['error'][]['context'] = $context;
    }

    public function warning($message, array $context = array())
    {
        $this->storage['warning'][]['message'] = $message;
        $this->storage['warning'][]['context'] = $context;
    }

    public function notice($message, array $context = array())
    {
        $this->storage['notice'][]['message'] = $message;
        $this->storage['notice'][]['context'] = $context;
    }

    public function info($message, array $context = array())
    {
        $this->storage['info'][]['message'] = $message;
        $this->storage['info'][]['context'] = $context;
    }

    public function debug($message, array $context = array())
    {
        $this->storage['debug'][]['message'] = $message;
        $this->storage['debug'][]['context'] = $context;
    }

    public function log($level, $message, array $context = array())
    {
        $this->storage['log'][]['message'] = $message;
        $this->storage['log'][]['context'] = $context;
    }

    public function getStorage(): array
    {
        return $this->storage;
    }
}
