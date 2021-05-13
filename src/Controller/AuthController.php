<?php
namespace App\Controller;

use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};
use PDOException;
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
        if(isset($reqData['email'])){
            if(isset($reqData['password'])){
                $email = $reqData['email'];
                $user->setEmail($email);
                $user->getId();
                $res = 
                new JsonResponse([
                    "data"=>[
                        "id" => $this->getUser()->getId(),
                        "email" => $this->getUser()->getEmail(),
                        "roles" => $this->getUser()->getRoles()
                    ]
                ],200);
                $res->headers->setCookie(Cookie::create('token',$JWT->create($user), strtotime('Fri, 20-May-2044 15:25:52 GMT'),'/',null,null,true,false));
                return $res;

            }else{
                return new JsonResponse(["error"=>"invalid data"], 400);
            }
        }else{
            return new JsonResponse(["error"=>"invalid data"], 400);
        }

    }
    /**
     * @Route("/register")
     */
    public function add(Request $req, UserPasswordEncoderInterface $passEnc):JsonResponse{  
        $reqData = [];
        if($content = $req->getContent()){
            $reqData=json_decode($content, true);
        }
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../Schemas/registerSchema.json'));
        $validator = new Validator();
        $result = $validator->schemaValidation((object)$reqData, $schema);
        if($result->isValid()){
            $email = $reqData['email'];
            $passwd = $reqData['password'];
            $name = $reqData['name']; 
            $surname = $reqData['surname'];
            $BDate = $reqData['date_of_birth'];
            $gender = $reqData['gender'];
            $user = new User();
            $passwordEncoder = $passEnc;
            $em = $this->getDoctrine()->getManager();
            if(!$em->getRepository(User::class)->findOneBy(["us_email" => $email])){
                $user->setEmail($email);
                $user->setPassword($passwordEncoder->encodePassword($user, $passwd));
                $user->setDateOfBirth(new DateTime($BDate));
                $user->setGender($gender);
                $user->setName($name);
                $user->setSurname($surname);
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
            switch($result->getFirstError()->keyword()){
                case "format":
                    return new JsonResponse(["error"=>"invalid email format"], 400);
                    break;
                case "minLength":
                    switch($result->getFirstError()->dataPointer()[0]){
                        case "password":
                            return new JsonResponse(["error"=>"password is too short"], 400);
                            break;
                        case "date_of_birth":
                            return new JsonResponse(["error"=>"date of birth has wrong format"], 400);
                            break;
                        }
                    break;
                case "maxLength":
                    switch($result->getFirstError()->dataPointer()[0]){
                        case "password":
                            return new JsonResponse(["error"=>"password is too long"], 400);
                            break;
                        case "date_of_birth":
                            return new JsonResponse(["error"=>"date of birth has wrong format"], 400);
                            break;
                        case "email":
                            return new JsonResponse(["error"=>"email is too long"], 400);
                            break;
                        case "name":
                            return new JsonResponse(["error"=>"name is too long"], 400);
                            break;
                        case "surname":
                            return new JsonResponse(["error"=>"surname is too long"], 400);
                            break;
                    }

                    break;
                case "required":
                    switch ($result->getFirstError()->keywordArgs()["missing"]) {
                        case "password":
                            return new JsonResponse(["error"=>"password is missing"], 400);
                            break;
                        case "email":
                            return new JsonResponse(["error"=>"email is missing"], 400);
                            break;
                        case "name":
                            return new JsonResponse(["error"=>"name is missing"], 400);
                            break;
                        case "surname":
                            return new JsonResponse(["error"=>"surname is missing"], 400);
                            break;
                        case "date_of_birth":
                            return new JsonResponse(["error"=>"date of birth is missing"], 400);
                            break;
                        case "gender":
                            return new JsonResponse(["error"=>"gender is missing"], 400);
                            break;
                    }
            }
        }

    }
    //add token refresh method
}