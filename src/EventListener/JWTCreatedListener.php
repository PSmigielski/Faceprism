<?php 
namespace App\EventListener;

use App\Service\UUIDService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener{
    private RequestStack $requestStack;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event, )
    {
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['user_id'] = UUIDService::decodeUUID($user->getId());
        $event->setData($payload);
    }
}

?>