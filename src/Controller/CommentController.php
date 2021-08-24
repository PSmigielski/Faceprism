<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Service\SchemaValidator;
use App\Service\UUIDService;
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
 * @Route("/v1/api/comments", defaults={"_is_api": true})
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/{postId}", name="get_comments", methods={"GET"})
     */
    public function index(CommentRepository $repo, Request $request,UUIDService $UUIDService, SerializerInterface $serializer, string $postId): JsonResponse
    {
        try{
            $page = $request->query->get('page', 1);
            $qb = $repo->findAllComments($UUIDService->encodeUUID($postId));
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
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            $tmp = json_decode($resData, true);
            $tmpComments = [];
            foreach($tmp["comments"] as $c){
                $c["id"] = $UUIDService->decodeUUID($c["id"]);
                $c["author"]["id"] = $UUIDService->decodeUUID($c["author"]["id"]);
                array_push($tmpComments, $c);
            }
            $tmp["comments"] = $tmpComments;
            return new JsonResponse($tmp, 200);
        }catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }

    }
    /**
     * @Route("/{postId}", name="create_comment",methods={"POST"})
     */
    public function create(Request $request, string $postId,SchemaValidator $schemaValidator, UUIDService $UUIDService):JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaValidator->validateSchema('/../Schemas/commentSchema.json', (object)$reqData);
        if($result===true){
            $comment = new Comment();
            $author = $this->getDoctrine()->getRepository(User::class)->find($UUIDService->encodeUUID($payload['user_id']));
            $post = $this->getDoctrine()->getRepository(Post::class)->find($UUIDService->encodeUUID($postId));
            if(!$author){
                return new JsonResponse(["error"=> "user with this id does not exist!"], 404);
            }
            if(!$post){
                return new JsonResponse(["message"=>"Post does not exist"], 404);
            }
            $comment->setAuthor($author);
            $comment->setPost($post);
            $comment->setText($reqData['text']);
            $comment->setCreatedAt(new DateTime("now"));
            $post->setCommentCount($post->getCommentCount()+1);
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->persist($post);
            $em->flush();
            $resData = $serializer->serialize($comment, "json",['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username","gender","post", "verified", "bannerUrl", "bio", "timezone"]]);
            $tmp = json_decode($resData, true);
            $tmp["id"] = $UUIDService->decodeUUID($tmp["id"]);
            $tmp["author"]["id"] = $UUIDService->decodeUUID($tmp["author"]["id"]);
            return new JsonResponse($tmp, 201);
        }
        else{
            return $result;
        }
    }
    /**
     * @Route("/{id}",  methods={"PUT"})
     */
    public function edit(Request $request, string $id, SchemaValidator $schemaValidator, UUIDService $UUIDService):JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $reqData = [];
        if($content = $request->getContent()){
            $reqData=json_decode($content, true);
        }
        $result = $schemaValidator->validateSchema('/../Schemas/commentEditSchema.json', (object)$reqData);
        if($result === true){
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository(Comment::class)->find($UUIDService->encodeUUID($id));
            if($comment->getAuthor()->getId() == $UUIDService->encodeUUID($payload['user_id'])){
                if(!$comment){
                    return new JsonResponse(["error"=>"comment does not exist!"], 404);
                }
                $comment->setText($reqData['text']);
                $comment->setEditedAt(new DateTime("now"));
                $em->persist($comment);
                $em->flush();
                $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
                $resData = $serializer->serialize($comment, "json",['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username","gender","post", "verified", "bannerUrl", "bio", "timezone"]]);
                $tmp = json_decode($resData, true);
                $tmp["id"] = $UUIDService->decodeUUID($tmp["id"]);
                $tmp["author"]["id"] = $UUIDService->decodeUUID($tmp["author"]["id"]);
                return new JsonResponse(["message"=>"comment has been edited","comment" => $tmp], 200);
            } else {
                return new JsonResponse(["error"=>"This comment does not belong to you"], 403);
            }
        } else {
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function remove(Request $request, string $id, UUIDService $UUIDService) :JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find($UUIDService->encodeUUID($id));
        if(!$comment){
            dump($comment);
            return new JsonResponse(["message"=>"Comment does not exist"], 404);
        }
        if($comment->getAuthor()->getId() == $UUIDService->encodeUUID($payload['user_id'])){
            $post = $comment->getPost();
            $post->setCommentCount($post->getCommentCount()-1);
            $em->remove($comment);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(["message"=>"Comment has been deleted"], 200);
        } else {
            return new JsonResponse(["error"=>"This comment does not belong to you"], 403);
        }
    }
}
