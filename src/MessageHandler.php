<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Exceptions\{MessageCantMarkAsProcessed, MessageHandlerFailed};

class MessageHandler extends AbstractHandler
{
    public function processRetries(array $processExceptionsOnly = [], int $maxMessagesPerException = -1): void
    {
        foreach ($this->config as $config) {
            if (!empty($processExceptionsOnly) && !in_array($config->getName(), $processExceptionsOnly, true)) {
                continue;
            }

            $messages = $config->getTransport()->fetchUnprocessedMessages($maxMessagesPerException);

            foreach ($messages ?? [] as $message) {
                if (!$config->getExecutor()->handle($message)) {
                    throw new MessageHandlerFailed();
                }

                if (!$config->getTransport()->markMessageAsProcessed($message)) {
                    throw new MessageCantMarkAsProcessed();
                }
            }
        }
    }
}
