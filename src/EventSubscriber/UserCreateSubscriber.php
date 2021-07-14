<?php

namespace App\EventSubscriber;

use App\Controller\VerifyEmailController;
use App\Event\UserCreateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserCreateSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }    
    public function onUserCreate(UserCreateEvent $event)
    {
        $verifyEmailController = new VerifyEmailController();
        $user = $event->getUser();
        $res = $event->getResponse();
        $verReq = $verifyEmailController->add($this->entityManager, $user);
        $resData = [];
        if($content = $res->getContent()){
            $resData=json_decode($content, true);
        }
        $isMailSent = $verifyEmailController->sendMail($this->mailer, $user->getEmail(), $verReq->getId());
        if($isMailSent){
            $resData['isMailSent'] = $isMailSent;
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($resData, "json");
            $response = JsonResponse::fromJsonString($resData,201);
            $event->setResponse($response);
        } else {
            $this->entityManager->remove($user);
            $this->entityManager->remove($verReq);
            $this->entityManager->flush();
            $response = new JsonResponse(["error" => "email can't be sent. Try register again later"], 500);
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            UserCreateEvent::NAME => 'onUserCreate',
        ];
    }
}
