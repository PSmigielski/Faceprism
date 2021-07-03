<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Symfony\Component\HttpFoundation\Cookie;

class AuthenticationSuccessListener
{
    private $jwtTokenTTL;
    private $em;
    private $cookieSecure = false;

    public function __construct($ttl, $em)
    {
        $this->jwtTokenTTL = $ttl;
        $this->em = $em;
    }

    /**
     * This function is responsible for the authentication part
     *
     * @param AuthenticationSuccessEvent $event
     * @return JWTAuthenticationSuccessResponse
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        /** @var JWTAuthenticationSuccessResponse $response */
        $response = $event->getResponse();
        $data = $event->getData();
        $tokenJWT = $data['token'];
        unset($data['token']);
        unset($data['refresh_token']);
        $event->setData($data);
        $tempUser = $event->getUser();
        $user = $this->em->getRepository(User::class)->findBy(["us_email"=>$tempUser->getUsername()])[0];
        $response->headers->setCookie(new Cookie('BEARER', $tokenJWT, (
            new \DateTime())
            ->add(new \DateInterval('PT' . $this->jwtTokenTTL . 'S')), '/', null, $this->cookieSecure));    
        $data = [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "roles" => $user->getRoles()
        ];
        $event->setData($data);
        return $response;
    }
}