<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function setErrorResponse(ExceptionEvent $event)
    {
        if ($event->getThrowable()?->getCode()) {
            $code = $event->getThrowable()->getCode();
        } else if ($event->getThrowable()?->getCode() === 0) {
            $code = ($event->getThrowable()?->getStatusCode()) ? $event->getThrowable()?->getStatusCode() : 500;
        }
        $event->setResponse(new JsonResponse(["error" => $event->getThrowable()->getMessage()],  $code));
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => [
                ['setErrorResponse']
            ]
        ];
    }
}
