<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Controller\SchemaController;
use App\Repository\CommentRepository;
use DateTime;
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
     * @Route("", name="create_comment",methods={"POST"})
     */
    public function create(Request $request, SchemaController $schemaController):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/commentSchema.json', (object)$reqData);
        if($result===true){
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
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="edit_comment",methods={"PUT"})
     */
    public function edit(Request $request, string $id, SchemaController $schemaController):JsonResponse
    {
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/commentEditSchema.json', (object)$reqData);
        if($result === true){
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository(Comment::class)->find($id);
            if(!$comment){
                return new JsonResponse(["error"=>"comment does not exist!"], 404);
            }
            $comment->setText($reqData['text']);
            $em->persist($comment);
            $em->flush();
            return new JsonResponse(["message"=>"Post has been edited"], 200);
        }
        else{
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function remove(string $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find($id);
        if(!$comment){
            dump($comment);
            return new JsonResponse(["message"=>"Comment does not exist"], 404);
        }
        $em->remove($comment);
        $em->flush();
        return new JsonResponse(["message"=>"Comment has been deleted"], 200);
    }
}
