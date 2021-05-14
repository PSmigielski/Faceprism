<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use DateTime;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
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
     * @Route("/v1/api/comments", name="comment")
     */
class CommentController extends AbstractController
{
    /**
     * @Route("/{postId}", name="get_comments", methods={"GET"})
     */
    public function index(CommentRepository $repo, Request $request, SerializerInterface $serializer, string $postId): JsonResponse
    {
        try{
            $page = $request->query->get('page', 1);
            $qb = $repo->findAllComments($postId);
            $adapter = new QueryAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(25);
            $pagerfanta->setCurrentPage($page);
            $comments = array();
            foreach($pagerfanta->getCurrentPageResults() as $comment){
                $comments[] = $comment;
            }
            $data = [
                "page"=> $page,
                "totalPages" => $pagerfanta->getNbPages(),
                "count" => $pagerfanta->getNbResults(),
                "comments"=> $comments
            ];
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['usPosts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            return JsonResponse::fromJsonString($resData);
        }catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }

    }
    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../Schemas/commentSchema.json'));
        $validator = new Validator();
        $object = (object)$reqData;
        $result = $validator->schemaValidation((object)$object, $schema);
        if($result->isValid()){
            $comment = new Comment();
            $author = $this->getDoctrine()->getRepository(User::class)->find($reqData['author_uuid']);
            $post = $this->getDoctrine()->getRepository(Post::class)->find($reqData['post_uuid']);
            $comment->setAuthor($author);
            $comment->setPost($post);
            $comment->setText($reqData['text']);
            $comment->setCreatedAt(new DateTime("now"));
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($comment, "json",['ignored_attributes' => ['usPosts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username","gender","post"]]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return JsonResponse::fromJsonString($resData, 201);
        }
        else{
            dump($result->getFirstError()->keyword());
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
                        case "post_uuid":
                            return new JsonResponse(["error"=>"post uuid is missing"], 400);
                            break;
                    }
                    break;
            }
        }
    }
    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function edit(Request $request, string $id):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../Schemas/commentEditSchema.json'));
        $validator = new Validator();
        $object = (object)$reqData;
        $result = $validator->schemaValidation((object)$object, $schema);
        if($result->isValid()){
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository(Comment::class)->find($id);
            $comment->setText($reqData['text']);
            $em->persist($comment);
            $em->flush();
            return new JsonResponse(["message"=>"Post has been edited"], 200);
        }
        else{
            switch($result->getFirstError()->keyword()){
                case "maxLength":
                    switch($result->getFirstError()->dataPointer()[0]){
                        case "text":
                            return new JsonResponse(["error"=>"text is too long"], 400);
                    }
            }
        }
    }
}
