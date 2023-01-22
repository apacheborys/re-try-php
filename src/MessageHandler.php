<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Exceptions\{MessageCantMarkAsProcessed, MessageHandlerFailed};
use Psr\Log\LogLevel;

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
                $this->sendLogRecordAboutNewMessage($message, $config);

                if (!$config->getExecutor()->handle($message)) {
                    throw new MessageHandlerFailed();
                }

                if (!$config->getTransport()->markMessageAsProcessed($message)) {
                    throw new MessageCantMarkAsProcessed();
                }

                $this->sendLogRecordAboutProcessedMessage($message);
            }
        }
    }

    private function sendLogRecordAboutNewMessage(Message $message, Config $config): void
    {
        $this->sendLogRecord(
            LogLevel::DEBUG,
            sprintf('Received new message %s for exception %s', $message->getId(), $config->getHandledException())
        );
    }

    private function sendLogRecordAboutProcessedMessage(Message $message): void
    {
        $this->sendLogRecord(
            LogLevel::DEBUG,
            sprintf('Received message %s was processed and marked as processed successfully', $message->getId())
        );
    }
}
