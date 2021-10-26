<?php

namespace App\EventSubscriber;

use ErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;

class JWTAuthFailureSubscriber implements EventSubscriberInterface
{
    public function getError(AuthenticationFailureEvent $event)
    {
        throw new ErrorException($event->getException()->getMessage(), 401);
    }
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_failure' => [
                ['getError']
            ]
        ];
    }
}
