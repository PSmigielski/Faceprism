<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VerifyEmailRequest;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class VerifyEmailController extends AbstractController
{   
    public function add(EntityManagerInterface $em, User $user) : VerifyEmailRequest
    {
        $dateTime= new DateTime("now");
        $verRequest = new VerifyEmailRequest();
        $verRequest->setUser($user);
        $verRequest->setRequestedAt($dateTime);
        $verRequest->setExpiresAt($dateTime->add(new DateInterval("PT1H")));
        $em->persist($verRequest);
        $em->flush();
        return $verRequest;
    }
    public function sendMail(MailerInterface $mailer, string $email, string $id) :bool{
        try{
            $email = (new TemplatedEmail())
            ->from(new Address('walek.smigielski@gmail.com', 'bot'))
            ->to($email)
            ->subject('Verify your email')
            ->htmlTemplate('verify_email/email.html.twig')
            ->context([
                'verifyToken' => $id
            ]);
            $mailer->send($email);
            return true;
        } catch(TransportExceptionInterface $e) {
            return false;
        }
    }
}