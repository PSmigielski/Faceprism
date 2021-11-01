<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserCreateEvent;
use App\Service\JsonDecoder;
use App\Service\UUIDService;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/auth", methods={"POST"}, defaults={"_is_api": true})
 */
class AuthController extends AbstractController
{

    private EventDispatcherInterface $eventDispatcher;
    private ValidatorService $validator;
    private UserPasswordEncoderInterface $passEnc;
    private JWTEncoderInterface $JWTEncoderInterface;
    private JsonDecoder $jsonDecoder;
    private EntityManagerInterface $em;
    private Serializer $serializer;
    public function __construct(
        JsonDecoder $jsonDecoder,
        ProfileController $profileController,
        JWTEncoderInterface $JWTEncoderInterface,
        EventDispatcherInterface $eventDispatcher,
        ValidatorService $validator,
        UserPasswordEncoderInterface $passEnc,
        EntityManagerInterface $em
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->passEnc = $passEnc;
        $this->JWTEncoderInterface = $JWTEncoderInterface;
        $this->jsonDecoder = $jsonDecoder;
        $this->em = $em;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }
    /**
     * @Route("/login", name="auth_login")
     */
    public function login()
    {
    }

    /**
     * @Route("/register", name="auth_register")
     */
    public function create(Request $request): JsonResponse
    {
        $requestData = $this->jsonDecoder->decode($request);
        $this->validator->validateSchema('/../Schemas/registerSchema.json', (object)$requestData);
        if (!$this->em->getRepository(User::class)->findOneBy(["us_email" => $requestData['email']])) {
            if (!$this->em->getRepository(User::class)->findOneBy(["us_tag" => $requestData['tag']])) {
                $user = new User($requestData);
                $user->setPassword($this->passEnc->encodePassword($user, $requestData["password"]));
                $this->em->persist($user);
                $this->em->flush();
                $responseData = $this->serializer->serialize($user, "json", ['ignored_attributes' => ['posts', "transitions", "timezone"]]);
                $event = $this->eventDispatcher->dispatch(new UserCreateEvent($user, JsonResponse::fromJsonString($responseData, 201)), UserCreateEvent::NAME);
                return $event->getResponse();
            } else {
                throw new ErrorException("user with this tag exist!", 400);
            }
        } else {
            throw new ErrorException("user with this email exist!", 400);
        }
    }

    /**
     * @Route("/account", name="auth_remove_account", methods={"DELETE"})
     */
    public function remove(Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (!$user) {
            return new JsonResponse(["error" => "User with this id does not exist!"], 404);
        }
        $em->remove($user);
        $em->flush();
        return new JsonResponse(["message" => "User has been deleted"], 202);
    }
    /**
     * @Route("/account", name="auth_update_account", methods={"PUT"})
     */
    public function updateAccount(Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $requestData = $this->jsonDecoder->decode($request);
        $this->validator->validateSchema('/../Schemas/editAccountDataSchema.json', (object)$requestData);
        $user = $this->em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (!$user) {
            return new JsonResponse(["error" => "User with this id does not exist!"], 404);
        }
        foreach ($requestData as $key => $value) {
            match ($key) {
                "name" => $user->setName($value),
                "surname" => $user->setSurname($value),
                "date_of_birth" => $user->setDateOfBirth($value),
                "gender" => $user->setGender($value)
            };
        }
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse(["message" => "Account data has been modified"], 201);
    }
    /**
     * @Route("/logout", name="auth_logout", methods={"POST"})
     */
    public function logout(Request $request): JsonResponse
    {
        $decodedToken = $this->JWTEncoderInterface->decode($request->cookies->get("BEARER"));
        $em = $this->getDoctrine()->getManager();
        $refToken = $em->getRepository(RefreshToken::class)->findBy(["username" =>  $decodedToken["username"]]);
        if (gettype($refToken) == "array") {
            foreach ($refToken as $token) {
                $em->remove($token);
                $em->flush();
            }
        } else {
            $em->remove($refToken);
            $em->flush();
        }
        $response = new JsonResponse(["message" => "successfully logged out"]);
        $response->headers->clearCookie("BEARER");
        $response->headers->clearCookie("REFRESH_TOKEN");
        return $response;
    }
}
