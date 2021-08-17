<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ImageUploader;
use App\Service\SchemaValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/api/profile", defaults={"_is_api": true})
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/image/{imageType}", name="change_image",methods={"POST"})
     */
    public function updateProfilePic(Request $req, ImageUploader $imageUploader, string $imageType): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $payload = $req->attributes->get("payload");
        $user = $em->getRepository(User::class)->find($payload["user_id"]);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            $width = $imageType == "banner" ? 820 : 200;
            $height = $imageType == "banner" ? 312 : 200;
            if(strpos($req->files->get("image")->getMimeType(), 'image') !== false){ 
                switch($imageType){
                    case "banner":
                        $user->setBannerUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), $width , $height, $imageType));
                        break;
                    case "profile_pic":
                        $user->setProfilePicUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), $width , $height, $imageType));
                        break;
                }
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => $imageType." has been updated"], 201);
            } else{
                return new JsonResponse(["error" => "wrong file format"], 415);
            }
        }
    }
    /**
     * @Route("/bio", name="change_bio",methods={"PUT"})
     */
    public function updateBio(Request $req, SchemaValidator $schemaValidator): JsonResponse
    {
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($payload["user_id"]);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            $reqData = [];
            if($content = $req->getContent()){
                $reqData=json_decode($content, true);
            }
            $result = $schemaValidator->validateSchema('/../Schemas/profileUpdateBioSchema.json', (object)$reqData);
            if($result === true){
                $user->setBio($reqData["bio"]);
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "bio has been updated"], 201);
            }else{
                return $result;
            }
        }
    }
    /**
     * @Route("/tag/{newTag}", name="change_bio",methods={"PUT"})
     */
    public function updateTag(Request $request, string $newTag ) : JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($payload["user_id"]);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            if(preg_match("/^[a-zA-Z0-9_]{3,15}$/", $newTag) === 1){
                $user->setTag("@".$newTag);
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "user tag has been changed!"], 200);
            }else{
                return new JsonResponse(["error"=>"Illegal characters used in this tag"], 404);
            }
        }
    }
}
