<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use DateTime;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\ImageUploader;
use App\Service\UUIDService;

/**
 * @Route("/v1/api/posts", defaults={"_is_api": true})
 * 
 */
class PostController extends AbstractController
{
    /**
     * @Route("", name="get_posts", methods={"GET"})
     */
    public function index(PostRepository $repo, SerializerInterface $serializer, Request $request, UUIDService $UUIDService): JsonResponse
    {
        try{
            $page = $request->query->get('page', 1);
            $qb = $repo->createFindAllQuery();
            $adapter = new QueryAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(25);
            $pagerfanta->setCurrentPage($page);
            $posts = array();
            foreach($pagerfanta->getCurrentPageResults() as $post){
                $posts[] = $post;
            } 
            $data = [
                "page"=> $page,
                "totalPages" => $pagerfanta->getNbPages(),
                "count" => $pagerfanta->getNbResults(),
                "posts"=> $posts
            ];
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            $tmp = json_decode($resData, true);
            $tmpPosts = [];
            foreach($tmp["posts"] as $p){
                $p["id"] = $UUIDService->decodeUUID($p["id"]);
                $p["author"]["id"] = $UUIDService->decodeUUID($p["author"]["id"]);
                array_push($tmpPosts, $p);
            }
            $tmp["posts"] = $tmpPosts;
            return new JsonResponse($tmp, 200);
        }
        catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }
    }
    /**
     * @Route("/{id}", name="get_post", methods={"GET"})
     */
    public function show(string $id, SerializerInterface $serializer, UUIDService $UUIDService): JsonResponse
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($UUIDService->encodeUUID($id));
        if(!$post){
            return new JsonResponse(["message" => "Post with this id does not exist"],404);
        }
        $resData = $serializer->serialize($post, "json",['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
        $tmp=json_decode($resData, true);
        $tmp["id"] = $UUIDService->decodeUUID($tmp["id"]);
        return new JsonResponse($tmp, 200);
    }
    /**
     * @Route("", name="add_post", methods={"POST"})
     */
    public function create(Request $request, ImageUploader $imageUploader, UUIDService $UUIDService):JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $post = new Post();
        $author = $this->getDoctrine()->getRepository(User::class)->find($UUIDService->encodeUUID($payload["user_id"]));
        if(!$author){
            return new JsonResponse(["error"=> "user with this id does not exist!"], 404);
        }
        if($request->request->get("text") != "" && !is_null($request->request->get("text"))){
            $post->setText($request->request->get("text"));
        }else{
            return new JsonResponse(["error"=>"text cannot be empty"], 400);
        }
        if(!is_null($request->files->get("file"))){
            if($request->files->get("file")->getError() == 1){
                return new JsonResponse(["error"=> "something went wrong with reading file! it might be corrupted"], 500);
            } else {
                if(strpos($request->files->get("file")->getMimeType(), 'image') !== false || strpos($request->files->get("file")->getMimeType(), 'video') !== false){
                    if($request->files->get("file")->getSize() < 20480000){
                        $post->setFileUrl($imageUploader->uploadFileToCloudinary($request->files->get("file")));
                    } else {
                        return new JsonResponse(["error"=> "file is too big"], 400);
                    }
                } else {
                    return new JsonResponse(["error"=> "wrong file type"], 400);
                }
            }
        }
        $post->setAuthor($author);
        $post->setCreatedAt(new DateTime("now"));
        $post->setLikeCount(0);
        $post->setCommentCount(0);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();
        $resData = $serializer->serialize($post, "json",['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username","gender","post", "verified", "bannerUrl", "bio", "timezone"]]);
        $tmp = json_decode($resData, true);
        $tmp["id"] = $UUIDService->decodeUUID($tmp["id"]);
        $tmp["author"]["id"] = $UUIDService->decodeUUID($tmp["author"]["id"]);
        return new JsonResponse($tmp, 201);
    }
    /**
     * @Route("/{postID}", name="edit_comment", methods={"POST"})
     */
    public function edit(Request $request, string $postID, UUIDService $UUIDService,ImageUploader $imageUploader):JsonResponse
    {

        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($UUIDService->encodeUUID($postID));
        if(!$post){
            return new JsonResponse(["message"=>"Post does not exist"], 404);
        }
        if($post->getAuthor()->getId() == $UUIDService->encodeUUID($payload['user_id'])){
            if($request->request->get("text") != "" && !is_null($request->request->get("text"))){
                $post->setText($request->request->get("text"));
            }else{
                return new JsonResponse(["error"=>"text cannot be empty"], 400);
            }
            if(!is_null($request->files->get("file"))){
                if($request->files->get("file")->getError() == 1){
                    return new JsonResponse(["error"=> "something went wrong with reading file! it might be corrupted"], 500);
                } else {
                    if(strpos($request->files->get("file")->getMimeType(), 'image') !== false || strpos($request->files->get("file")->getMimeType(), 'video') !== false){
                        if($request->files->get("file")->getSize() < 204800){
                            $post->setFileUrl($imageUploader->uploadFileToCloudinary($request->files->get("file")));
                        } else {
                            return new JsonResponse(["error"=> "file is too big"], 400);
                        }
                    } else {
                        return new JsonResponse(["error"=> "wrong file type"], 400);
                    }
                }
            }else{
                $post->setFileUrl(null);
            }
            $post->setEditedAt(new DateTime("now"));
            $em->persist($post);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($post, "json",['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username","gender","post", "verified", "bannerUrl", "bio", "timezone", "poComments"]]);
            $tmp = json_decode($resData, true);
            $tmp["id"] = $UUIDService->decodeUUID($tmp["id"]);
            $tmp["author"]["id"] = $UUIDService->decodeUUID($tmp["author"]["id"]);
            return new JsonResponse(["message"=>"Post has been edited", "post"=> $tmp], 200);
        } else {
            return new JsonResponse(["error"=>"This post does not belong to you"], 403);
        }
    }
    /**
     * @Route("/{postID}", name="delete_post", methods={"DELETE"})
     */
    public function remove(Request $request, string $postID,UUIDService $UUIDService) :JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($UUIDService->encodeUUID($postID));
        if(!$post){
            return new JsonResponse(["message"=>"Post does not exist"], 404);
        }
        if($post->getAuthor()->getId() == $UUIDService->encodeUUID($payload['user_id'])){
            $em->remove($post);
            $em->flush();
            return new JsonResponse(["message"=>"Post has been deleted"], 200);
        } else {
            return new JsonResponse(["error"=>"This post does not belong to you"], 403);
        }   
    }
}
