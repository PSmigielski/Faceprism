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
     * @Route("/addprofilepic", name="change_profilepic",methods={"POST"})
     */
    public function index(Request $req, ImageUploader $imageUploader): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $userID = $req->request->get("userID");
        $user = $em->getRepository(User::class)->find($userID);
        if(is_null($user)){
            return new JsonResponse(["error"=>"user with this id does not exist!"], 404);
        }else{
            if(strpos($req->files->get("image")->getMimeType(), 'image') !== false){ 
                $user->setProfilePicUrl($imageUploader->uploadFileToCloudinary($req->files->get("image")));
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "profile picture has been added"], 201);
            } else{
                return new JsonResponse(["error" => "wrong file format"], 415);
            }
        }
    }
}
