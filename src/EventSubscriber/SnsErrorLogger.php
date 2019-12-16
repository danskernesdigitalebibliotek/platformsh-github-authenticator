<?php

namespace App\EventSubscriber;

use Bref\MessengerSns\Event\SnsMessageDecodeFailed;
use Bref\MessengerSns\Event\SnsMessageFailed;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class SnsErrorLogger implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public static function getSubscribedEvents() : array
    {
        return [
            SnsMessageFailed::class => [
                'logError'
            ],
            SnsMessageDecodeFailed::class => [
                'logError'
            ]
        ];
    }

    /**
     * @param \Bref\MessengerSns\Event\SnsMessageFailed|\Bref\MessengerSns\Event\SnsMessageDecodeFailed $error
     */
    public function logError($error)
    {
        $throwables = [ $error->getThrowable() ];

        $throwable = $error->getThrowable();
        if ($throwable instanceof HandlerFailedException) {
            $throwables = array_merge($throwables, $throwable->getNestedExceptions());
        }

        array_map(function (\Throwable $throwable) use ($error) {
            $this->logger->error(
                'SNS error',
                [
                    'error' => get_class($error),
                    'exception_class' => get_class($throwable),
                    'exception' => $throwable,
                    'trace' => $throwable->getTrace()
                ]
            );
        }, $throwables);
    }
}
