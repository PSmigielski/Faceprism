<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Service\ImageUploader;
use App\Service\SchemaValidator;
use App\Service\UUIDService;
use Opis\JsonSchema\MediaTypes\Json;
use PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/pages", name="page", defaults={"_is_api": true})
 */
class PageController extends AbstractController
{
    /**
     * @Route("", name="get_pages_for_user", methods={"GET"})
     */
    public function index(Request $request, PageRepository $repo):JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get("page", 1);
        $qb = $repo->getAllPagesForUser(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page, $qb, "pages");
        if(gettype($data) == "array"){
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $tmpPages = [];
            foreach ($data["pages"] as $value) {
                $resData = $serializer->serialize($value, "json",['ignored_attributes' => ["owner"]]);
                $resDataUser = $serializer->serialize($value->getOwner(), "json",['ignored_attributes' => ['posts', "dateOfBirth", "password", "email", "username","roles","gender", "salt", "post","verified", "bio", "bannerUrl"]]);
                $tmpuser = json_decode($resDataUser, true);
                $tmp = json_decode($resData, true);
                $tmpuser["id"] = UUIDService::decodeUUID($tmpuser["id"]);
                $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
                $tmp["owner"] = $tmpuser;
                array_push($tmpPages,$tmp);
            }
            $data["pages"] = $tmpPages;
            return new JsonResponse($data, 200);
        } else{
            return $data;
        }
    }
    /**
     * @Route("/{pageId}",name="get_page", methods={"GET"})
     */
    public function show(string $pageId):JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if(is_null($page)){
            return new JsonResponse(["error"=>"Page with this id does not exist"], 404);
        }else{
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($page, "json",['ignored_attributes' => ["owner"]]);
            $resDataUser = $serializer->serialize($page->getOwner(), "json",['ignored_attributes' => ['posts', "dateOfBirth", "password", "email", "username","roles","gender", "salt", "post","verified", "bio", "bannerUrl"]]);
            $tmpuser = json_decode($resDataUser, true);
            $tmp = json_decode($resData, true);
            $tmpuser["id"] = UUIDService::decodeUUID($tmpuser["id"]);
            $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
            $tmp["owner"] = $tmpuser;
            return new JsonResponse($tmp, 200);
        }
    }
    /**
     * @Route("",name="create_page", methods={"POST"})
     */
    public function create(Request $request, ImageUploader $imageUploader, SchemaValidator $schemaValidator) : JsonResponse 
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $name=$request->request->get("name", null);
        $email=$request->request->get("email", null);
        $bio=$request->request->get("bio", null);
        $website=$request->request->get("website", null);
        $profile_pic=$request->files->get("profile_pic", null);
        $banner=$request->files->get("banner", null);
        if(is_null($name)){
            return new JsonResponse(["error" => "Page name is required"], 400);
        }else{
            $page = new Page();
            $page->setName($name);
            $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
            $page->setOwner($user);
            if(!is_null($bio)){
                if($schemaValidator->validateFormData($bio, "bio")===true){$page->setBio($bio);}else{return $schemaValidator->validateFormData($bio, "bio");}
            }
            if(!is_null($email)){
                if($schemaValidator->validateFormData($email, "email")===true){$page->setEmail($email);}else{return $schemaValidator->validateFormData($email, "email");}
            }
            if(!is_null($website)){
                if($schemaValidator->validateFormData($website, "website")===true){$page->setWebsite($website);}else{return $schemaValidator->validateFormData($website, "website");}
            }
            if(!is_null($profile_pic)){
                if($schemaValidator->validateFormData($profile_pic, "profile_pic")===true){$page->setProfilePicUrl($imageUploader->uploadFileToCloudinary($profile_pic, 200, 200,"profile_pic"));}else{return $schemaValidator->validateFormData($profile_pic, "profile_pic");}
            }else{
                $page->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
            }
            if(!is_null($banner)){
                if($schemaValidator->validateFormData($banner, "banner")===true){$page->setBannerUrl($imageUploader->uploadFileToCloudinary($profile_pic, 820, 312,"banner"));}else{return $schemaValidator->validateFormData($banner, "banner");}
            }
            $page->setFollowCount(0);
            $em->persist($page);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $ownerData = $serializer->serialize($page->getOwner(), "json",["ignored_attributes"=>['posts', "dateOfBirth", "password", "email", "username","roles","gender", "salt", "post","verified", "bio", "bannerUrl"]]);
            $data = $serializer->serialize($page, "json", ["ignored_attributes"=>["owner"]]);
            $tmpOwner = json_decode($ownerData,true);
            $tmp = json_decode($data, true);
            $tmp["owner"]=$tmpOwner;
            $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
            $tmp["owner"]["id"] = UUIDService::decodeUUID($tmp["owner"]["id"]);
            return new JsonResponse($tmp, 201);
        }
    }
    /**
     * @Route("/{pageId}",name="remove_page", methods={"DELETE"})
     */
    public function remove(Request $request, string $pageId): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if(!is_null($page)){
            if($page->getOwner()->getId() === UUIDService::encodeUUID($payload["user_id"])){
                $em->remove($page);
                $em->flush();
                return new JsonResponse(["message"=>"Page has been deleted!"], 202);
            }else{
                return new JsonResponse(["error"=>"This page does not belongs to you"], 403);
            }
        }else{
            return new JsonResponse(["error"=>"Page with this id does not exist!"], 404);
        }
    }
}
