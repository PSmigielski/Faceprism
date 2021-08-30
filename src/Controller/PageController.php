<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Service\ImageUploader;
use App\Service\UUIDService;
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
    public function create(Request $request, ImageUploader $imageUploader) : JsonResponse 
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
                if(strlen($bio) < 256){
                    $page->setBio($bio);
                }else{
                    return new JsonResponse(["error"=>"bio is too long"],400);
                }
            }
            if(!is_null($email)){
                if(preg_match("/^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $email) === 1){
                    $page->setEmail($email);
                } else {
                    return new JsonResponse(["error"=>"wrong email format"],400);
                }
            }
            if(!is_null($website)){
                if(preg_match("/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})$/", $website) === 1){
                    $page->setWebsite($website);
                }
                else {
                    return new JsonResponse(["error"=>"wrong link format"],400);
                }
            }
            if(!is_null($profile_pic)){
                if(strpos($request->files->get("profile_pic")->getMimeType(), 'image') !== false){
                    $page->setProfilePicUrl($imageUploader->uploadFileToCloudinary($request->files->get("profile_pic"), 200, 200,"profile_pic"));
                }
                else {
                    return new JsonResponse(["error"=>"wrong file format"],400);
                }
            }else{
                $page->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
            }
            if(!is_null($banner)){
                if(strpos($request->files->get("banner")->getMimeType(), 'image') !== false){
                    $page->setBannerUrl($imageUploader->uploadFileToCloudinary($request->files->get("banner"), 820, 312,"banner"));
                }
                else {
                    return new JsonResponse(["error"=>"wrong file format"],400);
                }
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
}
