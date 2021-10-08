<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;

class RefreshedTokenSubscriber implements EventSubscriberInterface {

    private $refTokenTTL;
    private $jwtTokenTTL;
    private $em;
    private $cookieSecure = false;

    public function __construct($refTokenTTL, $jwtTTL, $em)
    {
        $this->refTokenTTL = $refTokenTTL;
        $this->jwtTokenTTL = $jwtTTL;
        $this->em = $em;
    }

    public function setTokens(AuthenticationSuccessEvent $event)
    {
        $response = $event->getResponse();
        $data = $event->getData();
        $tokenJWT = $data['token'];
        $refreshToken = $data['refresh_token'];
        unset($data['token']);
        unset($data['refresh_token']);
        $tempUser = $event->getUser();
        $user = $this->em->getRepository(User::class)->findBy(["us_email"=>$tempUser->getUsername()])[0];
        $isVerified = $user->getVerified();
        if($isVerified){
            $response->headers->setCookie(new Cookie('REFRESH_TOKEN', $refreshToken, (
                new \DateTime())
                ->add(new \DateInterval('PT' . $this->refTokenTTL . 'S')), '/', null, $this->cookieSecure));
            $response->headers->setCookie(new Cookie('BEARER', $tokenJWT, (
                new \DateTime())
                ->add(new \DateInterval('PT' . $this->jwtTokenTTL . 'S')), '/', null, $this->cookieSecure));    
            $data = [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "profile_pic" => $user->getProfilePicUrl()
            ];
            $event->setData($data);
            return $response;
        }else{
            $data = ["error" => "Your email is not verified!"];
            $event->setData($data);
            $response->setStatusCode(401);
            return $response;
        }

    }
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => [
                ['setTokens']
            ]
        ];
    }
}