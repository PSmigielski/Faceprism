<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
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
        $reqData = [];
        if($content = $request->getContent()){
            $reqData = json_decode($content, true);
        }
        if(array_key_exists('user_uuid', $reqData)&&isset($reqData['user_uuid'])){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->find($reqData["user_uuid"]);
            $post = $em->getRepository(Post::class)->find($postID);
            if(!$user){
                return new JsonResponse(["error"=>"user with this id doesn't exist!"], 400);
            }
            if(!$post){
                return new JsonResponse(["error"=>"post with this id doesn't exist!"], 400);
            }
            if($em->getRepository(Like::class)->findBy(["li_post"=>$postID,"li_user"=>$reqData['user_uuid']])){
                $like = $em->getRepository(Like::class)->findBy(["li_post"=>$postID,"li_user"=>$reqData['user_uuid']]);
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

        }else{
            return new JsonResponse(["error"=>"provide user uuid!"], 400);
        }
        
    }
}
