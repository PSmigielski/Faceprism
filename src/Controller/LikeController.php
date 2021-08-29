<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Service\UUIDService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
    /**
     * @Route("/v1/api/like", defaults={"_is_api": true})
     */
class LikeController extends AbstractController
{   
    /**
     * @Route("/{postID}", methods={"POST"})
     */
    public function index(string $postID, Request $request) : JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        $post = $em->getRepository(Post::class)->find(UUIDService::encodeUUID($postID));
        if(!$user){
            return new JsonResponse(["error"=>"user with this id doesn't exist!"], 400);
        }
        if(!$post){
            return new JsonResponse(["error"=>"post with this id doesn't exist!"], 400);
        }
        if($em->getRepository(Like::class)->findBy(["li_post"=>UUIDService::encodeUUID($postID),"li_user"=>UUIDService::encodeUUID($payload["user_id"])])){
            $like = $em->getRepository(Like::class)->findBy(["li_post"=>UUIDService::encodeUUID($postID),"li_user"=>UUIDService::encodeUUID($payload["user_id"])]);
            $em->remove($like[0]);
            $post->setLikeCount($post->getLikeCount()-1);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(["message"=>"like removed successfully"], 201);
        }
        $like = new Like();
        $like->setPost($post);
        $like->setUser($user);
        $post->setLikeCount($post->getLikeCount()+1);
        $em->persist($like);
        $em->persist($post);
        $em->flush();
        return new JsonResponse(["message"=>"like added successfully"], 201);  
    }
}
