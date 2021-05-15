<?php
namespace App\Controller;

use App\Entity\User;
use DateTime;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
/**
 * @Route("/v1/api/auth", methods={"POST"})
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/login")
     */
    public function login(Request $req,JWTTokenManagerInterface $JWT): JsonResponse
    {
        $user = new User();
        $reqData = [];
        if($content = $req->getContent()){
            $reqData=json_decode($content, true);
        }
        $user->setEmail($reqData['email']);
        $user->getId();
        $res = new JsonResponse([
            "data"=>[
                "id" => $this->getUser()->getId(),
                "email" => $this->getUser()->getEmail(),
                "roles" => $this->getUser()->getRoles()
            ]
        ],200);
        $res->headers->setCookie(Cookie::create('token',$JWT->create($user), strtotime('Fri, 20-May-2044 15:25:52 GMT'),'/',null,null,true,false));
        return $res;
    }
    /**
     * @Route("/register")
     */
    public function add(Request $req,SchemaController $schemaController, UserPasswordEncoderInterface $passEnc):JsonResponse{  
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
                $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
                $resData = $serializer->serialize($user, "json",['ignored_attributes' => ['usPosts', "transitions"]]);
                $em->persist($user);
                $em->flush();
                return JsonResponse::fromJsonString($resData, 201);         
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
    public function remove(string $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if(!$user){
            return new JsonResponse(["error" => "User with this id does not exist!"], 404);
        }
        $em->remove($user);
        $em->flush();
        return new JsonResponse(["message"=>"User has been deleted"], 201);
    }
    //add token refresh method
}