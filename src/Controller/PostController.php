<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Controller\SchemaController;
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

/**
 * @Route("/v1/api/posts")
 * 
 */
class PostController extends AbstractController
{
    /**
     * @Route("", name="get_posts", methods={"GET"})
     */
    public function index(PostRepository $repo, SerializerInterface $serializer, Request $request): JsonResponse
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
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['usPosts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            return JsonResponse::fromJsonString($resData, 200);
        }
        catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }
    }
    /**
     * @Route("/{id}", name="get_post", methods={"GET"})
     */
    public function show(int $id): JsonResponse
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);
        if(!$post){
            return new JsonResponse(["message" => "no posts found"],404);
        }
        return new JsonResponse($post, 200);
    }
    /**
     * @Route("", name="add_post", methods={"POST"})
     */
    public function create(Request $request, SchemaController $schemaController):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/postSchema.json', (object) $reqData);
        if($result === true){
            $author_id = $reqData['author_uuid'];
            $post = new Post();
            $author = $this->getDoctrine()->getRepository(User::class)->find($author_id);
            if(!$author){
                return new JsonResponse(["error"=> "user with this id does not exist!"], 404);
            }
            $post->setAuthor($author);
            if(array_key_exists('text', $reqData)||array_key_exists('img', $reqData)){
                if(array_key_exists('text', $reqData)){
                    $post->setText($reqData['text']);
                }
                if(array_key_exists('img', $reqData)){
                    $post->setImage($reqData['img']);
                }
            }
            else{
                return new JsonResponse(["error"=> "text or image required!"], 400);
            }
            $post->setCreatedAt(new DateTime("now"));
            $post->setLikeCount(0);
            $post->setCommentCount(0);
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($post, "json",['ignored_attributes' => ['usPosts', "transitions", "password", "salt", "dateOfBirth", "roles"]]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return JsonResponse::fromJsonString($resData, 201);
        }
        else{
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="edit_comment", methods={"PUT"})
     */
    public function edit(Request $request, string $id, SchemaController $schemaController)
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/editPostSchema.json', (object) $reqData);
        if($result===true){
            $em = $this->getDoctrine()->getManager();
            $post = $em->getRepository(Post::class)->find($id);
            if(!$post){
                return new JsonResponse(["message"=>"Post does not exist"], 404);
            }
            if(array_key_exists('text', $reqData)||array_key_exists('img', $reqData)){
                if(array_key_exists('text', $reqData)){
                    $post->setText($reqData['text']);
                }
                if(array_key_exists('img', $reqData)){
                    $post->setImage($reqData['img']);
                }
            }
            else{
                return new JsonResponse(["error"=> "text or image required!"], 400);
            }
            $em->persist($post);
            $em->flush();
            return new JsonResponse(["message"=>"Post has been edited"], 200);
        }
        else{
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="delete_post", methods={"DELETE"})
     */
    public function remove(string $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if(!$post){
            return new JsonResponse(["message"=>"Post does not exist"], 404);
        }
        $em->remove($post);
        $em->flush();
        return new JsonResponse(["message"=>"Post has been deleted"], 200);
    }
}
