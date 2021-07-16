<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ImageUploader;
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
        $userID = $req->request->get("userID");
        $user = $em->getRepository(User::class)->find($userID);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            $width = $imageType == "banner" ? 820 : 200;
            $height = $imageType == "banner" ? 312 : 200;
            if(strpos($req->files->get("image")->getMimeType(), 'image') !== false){ 
                $user->setProfilePicUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), $width , $height, $imageType));
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => $imageType." has been updated"], 201);
            } else{
                return new JsonResponse(["error" => "wrong file format"], 415);
            }
        }
    }
    /**
     * @Route("/bio/{userID}", name="change_bio",methods={"PUT"})
     */
    public function updateBio(Request $req, string $userID, SchemaController $schemaController): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($userID);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            $reqData = [];
            if($content = $req->getContent()){
                $reqData=json_decode($content, true);
            }
            $result = $schemaController->validateSchema('/../Schemas/profileUpdateBioSchema.json', (object)$reqData);
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
}
