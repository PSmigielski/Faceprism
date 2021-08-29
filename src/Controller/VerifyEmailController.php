<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VerifyEmailRequest;
use App\Service\UUIDService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class VerifyEmailController extends AbstractController
{   
    public function add(EntityManagerInterface $em, User $user) : VerifyEmailRequest
    {
        $requestTime= new \DateTime();
        $expTime = (new \DateTime())->add(new \DateInterval("PT1H"));
        $verRequest = new VerifyEmailRequest();
        $verRequest->setUser($user);
        $verRequest->setRequestedAt($requestTime);
        $verRequest->setExpiresAt($expTime);
        $em->persist($verRequest);
        $em->flush();
        return $verRequest;
    }
    public function sendMail(MailerInterface $mailer, string $email, string $id) :bool{
        try{
            $UUIDService = new UUIDService();
            $email = (new TemplatedEmail())
            ->from(new Address('faceprism@gmail.com', 'Faceprism Bot'))
            ->to($email)
            ->subject('Verify your email')
            ->htmlTemplate('verify_email/email.html.twig')
            ->context([
                'verifyToken' => $UUIDService->decodeUUID($id)
            ]);
            $mailer->send($email);
            return true;
        } catch(TransportExceptionInterface $e) {
            return false;
        }
    }
    /**
     * @Route("/v1/api/verify/{verifyId}",name="verify_email", methods={"POST"}, defaults={"_is_api": true})
     */
    public function verify(string $verifyId) : JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $verRequest=$em->getRepository(VerifyEmailRequest::class)->find(UUIDService::encodeUUID($verifyId));
        if(is_null($verRequest)){
            return new JsonResponse(["error" => "Verify Request with this id does not exist!"],404);
        }else{
            $now = new \DateTime();
            $expDate = $verRequest->getExpiresAt();
            $user = $verRequest->getUser();
            if($now>$expDate){
                $em->remove($verRequest);
                $em->remove($user);
                $em->flush();
                return new JsonResponse(["error" => "Verify Request is expired! Please register again"],403);
            }else{
                $user->setVerified(true);
                $em->persist($user);
                $em->remove($verRequest);
                $em->flush();
                return new JsonResponse(["message" => "account has been verififed. You can login now to your account!"],200);
            }
        }
    }
}