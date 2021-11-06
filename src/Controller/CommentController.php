<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Service\ValidatorService;
use App\Service\UUIDService;
use DateTime;
use App\Service\PaginationService;
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
    private SerializerInterface $serializer;
    private CommentRepository $commentRepository;
    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }
    /**
     * @Route("/{postId}", name="get_comments", methods={"GET"})
     */
    public function index(Request $request, string $postId): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $qb = $this->commentRepository->findAllComments(UUIDService::encodeUUID($postId));
        $data = PaginationService::paginate($page, $qb, "comments");
        $responseData = $this->serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post"]]);
        $decodedData = json_decode($responseData, true);
        $comments = UUIDService::decodeUUIDsInArray($decodedData["comments"]);
        $decodedData["comments"] = $comments;
        return new JsonResponse($decodedData, 200);
    }
    /**
     * @Route("/{postId}", name="create_comment",methods={"POST"})
     */
    public function create(Request $request, string $postId, ValidatorService $ValidatorService): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $reply_to = $request->query->get('r');
        $reqData = [];
        if ($content = $request->getContent()) {
            $reqData = json_decode($content, true);
        }
        $ValidatorService->validateSchema('/../Schemas/commentSchema.json', (object)$reqData);
        $comment = new Comment();
        $author = $this->getDoctrine()->getRepository(User::class)->find(UUIDService::encodeUUID($payload['user_id']));
        $post = $this->getDoctrine()->getRepository(Post::class)->find(UUIDService::encodeUUID($postId));
        if (!$author) {
            return new JsonResponse(["error" => "user with this id does not exist!"], 404);
        }
        if (!$post) {
            return new JsonResponse(["message" => "Post does not exist"], 404);
        }
        $comment->setAuthor($author);
        $comment->setPost($post);
        $comment->setText($reqData['text']);
        $comment->setCreatedAt(new DateTime("now"));
        $comment->setRepliesCount(0);
        $post->setCommentCount($post->getCommentCount() + 1);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $em = $this->getDoctrine()->getManager();
        if (!is_null($reply_to)) {
            $c = $em->getRepository(Comment::class)->find(UUIDService::encodeUUID($reply_to));
            if (!is_null($c)) {
                $comment->setReplyTo($c);
                $c->setRepliesCount($c->getRepliesCount() + 1);
                $em->persist($c);
            } else {
                return new JsonResponse(["error" => "You can't reply to this comment"]);
            }
        }
        $em->persist($comment);
        $em->persist($post);
        $em->flush();
        $resData = $serializer->serialize($comment, "json", ['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username", "gender", "post", "verified", "bannerUrl", "bio", "timezone"]]);
        $tmp = json_decode($resData, true);
        $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
        $tmp["author"]["id"] = UUIDService::decodeUUID($tmp["author"]["id"]);
        $tmp["replyTo"]["author"]["id"] = UUIDService::decodeUUID($tmp["replyTo"]["author"]["id"]);
        $tmp["replyTo"]["id"] = UUIDService::decodeUUID($tmp["replyTo"]["id"]);
        return new JsonResponse($tmp, 201);
    }
    /**
     * @Route("/{id}",name="edit_comment",  methods={"PUT"})
     */
    public function edit(Request $request, string $id, ValidatorService $ValidatorService): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $reqData = [];
        if ($content = $request->getContent()) {
            $reqData = json_decode($content, true);
        }
        $result = $ValidatorService->validateSchema('/../Schemas/commentSchema.json', (object)$reqData);
        if ($result === true) {
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository(Comment::class)->find(UUIDService::encodeUUID($id));
            if ($comment->getAuthor()->getId() == UUIDService::encodeUUID($payload['user_id'])) {
                if (!$comment) {
                    return new JsonResponse(["error" => "comment does not exist!"], 404);
                }
                $comment->setText($reqData['text']);
                $comment->setEditedAt(new DateTime("now"));
                $em->persist($comment);
                $em->flush();
                $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
                $resData = $serializer->serialize($comment, "json", ['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username", "gender", "post", "verified", "bannerUrl", "bio", "timezone"]]);
                $tmp = json_decode($resData, true);
                $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
                $tmp["author"]["id"] = UUIDService::decodeUUID($tmp["author"]["id"]);
                $tmp["replyTo"]["author"]["id"] = UUIDService::decodeUUID($tmp["replyTo"]["author"]["id"]);
                $tmp["replyTo"]["id"] = UUIDService::decodeUUID($tmp["replyTo"]["id"]);
                return new JsonResponse(["message" => "comment has been edited", "comment" => $tmp], 200);
            } else {
                return new JsonResponse(["error" => "This comment does not belong to you"], 403);
            }
        } else {
            return $result;
        }
    }
    /**
     * @Route("/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function remove(Request $request, string $id): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find(UUIDService::encodeUUID($id));
        if (!$comment) {
            dump($comment);
            return new JsonResponse(["message" => "Comment does not exist"], 404);
        }
        if ($comment->getAuthor()->getId() == UUIDService::encodeUUID($payload['user_id'])) {
            $post = $comment->getPost();
            if (!is_null($comment->getReplyTo())) {
                $c = $em->getRepository(Comment::class)->find($comment->getReplyTo()->getId());
                $c->setRepliesCount($c->getRepliesCount() - 1);
                $post->setCommentCount($post->getCommentCount() - 1);
                $em->persist($c);
            } else {
                $post->setCommentCount($post->getCommentCount() - $comment->getRepliesCount() - 1);
            }
            $em->remove($comment);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(["message" => "Comment has been deleted"], 200);
        } else {
            return new JsonResponse(["error" => "This comment does not belong to you"], 403);
        }
    }
    /**
     * @Route("/{postId}/{commentId}", name="get_replies", methods={"GET"})
     */
    public function get_replies(CommentRepository $repo, Request $request, SerializerInterface $serializer, string $postId, string $commentId): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $qb = $repo->findAllReplies(UUIDService::encodeUUID($postId), UUIDService::encodeUUID($commentId));
        $data = PaginationService::paginate($page, $qb, "replies");
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "replyTo"]]);
            $tmp = json_decode($resData, true);
            $tmpRelpies = [];
            foreach ($tmp["replies"] as $c) {
                $c["id"] = UUIDService::decodeUUID($c["id"]);
                $c["author"]["id"] = UUIDService::decodeUUID($c["author"]["id"]);
                array_push($tmpRelpies, $c);
            }
            $tmp["replies"] = $tmpRelpies;
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
}
