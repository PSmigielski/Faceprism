<?php
namespace App\Controller;

use App\Entity\User;
use App\Event\UserCreateEvent;
use DateTime;
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
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    /**
     * @Route("/login")
     */
    public function login(Request $req)
    {
    }
    /**
     * @Route("/register")
     */
    public function add(Request $req,SchemaController $schemaController, UserPasswordEncoderInterface $passEnc) : JsonResponse
    {  
        $reqData = [];
        if($content = $req->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/registerSchema.json', (object)$reqData);
        if($result === true){
            $user = new User();
            $em = $this->getDoctrine()->getManager();
            if(!$em->getRepository(User::class)->findOneBy(["us_email" => $reqData['email']])){
                $user->setEmail($reqData['email']);
                $user->setPassword($passEnc->encodePassword($user, $reqData['password']));
                if(!$schemaController->verifyDate($reqData['date_of_birth'])){
                    return new JsonResponse(["error"=>"invalid date"], 400);
                }
                $user->setDateOfBirth(new DateTime($reqData['date_of_birth']));
                $user->setGender($reqData['gender']);
                $user->setName($reqData['name']);
                $user->setSurname( $reqData['surname']);
                $user->setRoles([]);
                $user->setVerified(false);
                $user->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
                $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
                $resData = $serializer->serialize($user, "json",['ignored_attributes' => ['usPosts', "transitions","timezone"]]);
                $em->persist($user);
                $em->flush();
                $response = JsonResponse::fromJsonString($resData, 201);
                $event = $this->eventDispatcher->dispatch(new UserCreateEvent($user, $response), UserCreateEvent::NAME); 
                return $event->getResponse();
            }else{
                return new JsonResponse(["error"=>"user with this email exist!"], 400);
            }
        }
        else{
            return $result;
        }

    }
    /**
     * @Route("/account", methods={"DELETE"})
     */
    public function remove(string $id) : JsonResponse{
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if(!$user){
            return new JsonResponse(["error" => "User with this id does not exist!"], 404);
        }
        $em->remove($user);
        $em->flush();
        return new JsonResponse(["message"=>"User has been deleted"], 201);
    }
    /**
     * @Route("/logout", methods={"POST"})
     */
    public function logout(Request $request, JWTEncoderInterface $token) : JsonResponse
    {
        $decodedToken = $token->decode($request->cookies->get("BEARER"));
        $em = $this->getDoctrine()->getManager();
        $refToken = $em->getRepository(RefreshToken::class)->findBy(["username" =>  $decodedToken["username"]]);
        if(gettype($refToken) == "array"){
            foreach($refToken as $token){
                $em->remove($token);
                $em->flush();    
            }
        }
        else{
            $em->remove($refToken);
            $em->flush();
        }
        $response = new JsonResponse(["message" => "successfully logged out"]);
        $response->headers->clearCookie("BEARER");
        $response->headers->clearCookie("REFRESH_TOKEN");
        return $response;
    }
}