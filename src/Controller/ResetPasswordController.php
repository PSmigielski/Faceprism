<?php

namespace App\Controller;

use App\Entity\User;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/v1/api/auth/resetpaswd", methods={"POST"})
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer, SchemaController $schemaController): Response
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/resetPasswordRequestSchema.json', $reqData);
        if($result===true){
            return $this->processSendingPasswordResetEmail(
                $reqData['email'],
                $mailer
            );
        }
        else{
            return $result;
        }

    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/{token}")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token= null): JsonResponse
    {
        if (null === $token) {
            return new JsonResponse(["message" => 'No reset password token found in the URL or in the session.'], 400);
        }
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(["message" => sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            )], 200);
        }
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../Schemas/resetPasswordSchema.json'));
        $validator = new Validator();
        $result = $validator->schemaValidation((object)$reqData, $schema);
        if($result->isValid()){
            $password = $reqData['password'];
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $password
            );
            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(["message" => "password has been changed"], 200);
        }

    }

    private function processSendingPasswordResetEmail(string $email, MailerInterface $mailer): JsonResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'us_email' => $email
        ]);

        if (!$user) {
            return new JsonResponse(["message" => "email hasn't been sent"], 400);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(["message" => "email hasn't been sent"], 400);
        }

        $email = (new TemplatedEmail())
            ->from(new Address('walek.smigielski@gmail.com', 'bot'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
        $mailer->send($email);
        return new JsonResponse(["message" => "email has been sent"], 200);
    }
}
