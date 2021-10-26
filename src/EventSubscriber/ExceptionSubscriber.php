<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function setErrorResponse(ExceptionEvent $event)
    {
        $event->setResponse(new JsonResponse(["error" => $event->getThrowable()->getMessage()], $event->getThrowable()->getCode() ? $event->getThrowable()->getCode() : 500));
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
