<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

class MessageHandler extends AbstractHandler
{
    public function processRetries(array $processExceptionsOnly = [], int $maxMessagesPerException = -1)
    {
        foreach ($this->config as $config) {
            $messages = $config->getTransport()->fetchMessage($maxMessagesPerException);
            
            foreach ($messages as $message) {
                $config->getExecutor()->handle($message);
            }

            return;
        }
    }
}
