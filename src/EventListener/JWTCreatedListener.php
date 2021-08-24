<?php 
namespace App\EventListener;

use App\Service\UUIDService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener{
    private RequestStack $requestStack;
    private UUIDService $UUIDService;
    public function __construct(RequestStack $requestStack, UUIDService $UUIDService)
    {
        $this->UUIDService = $UUIDService;
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event, )
    {
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['user_id'] = $this->UUIDService->decodeUUID($user->getId());
        $event->setData($payload);
    }
}

?>