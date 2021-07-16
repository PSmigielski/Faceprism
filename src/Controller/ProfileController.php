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
     * @Route("/profilepic", name="change_profile_pic",methods={"POST"})
     */
    public function updateProfilePic(Request $req, ImageUploader $imageUploader): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $userID = $req->request->get("userID");
        $user = $em->getRepository(User::class)->find($userID);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            if(strpos($req->files->get("image")->getMimeType(), 'image') !== false){ 
                $user->setProfilePicUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), 200 , 200,"profile_pic"));
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "profile picture has been updated"], 201);
            } else{
                return new JsonResponse(["error" => "wrong file format"], 415);
            }
        }
    }
    /**
     * @Route("/banner", name="change_banner",methods={"POST"})
     */
    public function updateBanner(Request $req, ImageUploader $imageUploader): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $userID = $req->request->get("userID");
        $user = $em->getRepository(User::class)->find($userID);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            if(strpos($req->files->get("image")->getMimeType(), 'image') !== false){ 
                $user->setBannerUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), 820 , 312, "banner"));
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "banner has been updated"], 201);
            } else{
                return new JsonResponse(["error" => "wrong file format"], 415);
            }
        }
    }
}
