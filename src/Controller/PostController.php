<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use DateTime;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
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
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['usPosts', "transitions", "password",]]);
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
    public function create(Request $request):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../Schemas/postSchema.json'));
        $validator = new Validator();
        $object = (object)$reqData;
        $result = $validator->schemaValidation((object)$object, $schema);
        if($result->isValid()){
            $author_id = $reqData['author_uuid'];
            $post = new Post();
            $author = $this->getDoctrine()->getRepository(User::class)->find($author_id);
            $post->setAuthorId($author);
            if(array_key_exists('text', $reqData)){
                $post->setText($reqData['text']);
            }
            if(array_key_exists('img', $reqData)){
                $post->setImage($reqData['img']);
            }
            $post->setCreatedAt(new DateTime("now"));
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($post, "json",['ignored_attributes' => ['usPosts', "transitions"]]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return JsonResponse::fromJsonString($resData, 201);
        }
        else{
            switch($result->getFirstError()->keyword()){
                case "maxLength":
                    switch($result->getFirstError()->dataPointer()[0]){
                        case "text":
                            return new JsonResponse(["error"=>"text is too long"], 400);
                            break;
                    }
                    break;
                case "required":
                    switch ($result->getFirstError()->keywordArgs()["missing"]) {
                        case "author_uuid":
                            return new JsonResponse(["error"=>"author uuid is missing"], 400);
                            break;
                    }
                    break;
            }
        }
    }
}
