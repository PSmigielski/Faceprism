<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use DateTime;
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
use PaginationService;

/**
 * @Route("/v1/api/posts", defaults={"_is_api": true}, requirements={"id"="[0-9a-f]{32}"})
 * 
 */
class PostController extends AbstractController
{
    /**
     * @Route("", name="get_posts", methods={"GET"})
     */
    public function index(PostRepository $repo, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $userID = $request->query->get("u", null);
        $pageID = $request->query->get("p", null);
        $payload = $request->attributes->get("payload");
        $page = $request->query->get('page', 1);
        if (is_null($pageID) && is_null($userID)) {
            $qb = $repo->createFindAllQuery(UUIDService::encodeUUID($payload["user_id"]));
            $data = PaginationService::paginate($page, $qb, "posts");
        } else {
            if (!is_null($pageID)) {
                $pageEntity = $this->getDoctrine()->getRepository(Page::class)->find(UUIDService::encodeUUID($pageID));
                if (!$pageEntity) {
                    return new JsonResponse(["error" => "page with this ID does not exist!"], 404);
                }
                $qb = $repo->createFindAllPostsForPage(UUIDService::encodeUUID($pageID));
                $data = PaginationService::paginate($page, $qb, "posts");
            }
            if (!is_null($userID)) {
                $user = $this->getDoctrine()->getRepository(User::class)->find(UUIDService::encodeUUID($userID));
                if (!$user) {
                    return new JsonResponse(["error" => "user with this ID does not exist!"], 404);
                }
                $qb = $repo->createFindAllPostsForUser(UUIDService::encodeUUID($userID));
                $data = PaginationService::paginate($page, $qb, "posts");
            }
        }
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "website", "bannerUrl", "bio", "verified", "dateOfBirth", "poComments"]]);
            $tmp = json_decode($resData, true);
            $tmpPosts = [];
            foreach ($tmp["posts"] as $p) {
                $like = $this->getDoctrine()->getRepository(Like::class)->findBy(["li_post" => $p["id"], "li_user" => UUIDService::encodeUUID($payload["user_id"])]);
                !empty($like) ? $p["isLiked"] = true : $p["isLiked"] = false;
                $p["id"] = UUIDService::decodeUUID($p["id"]);
                $p["author"]["id"] = UUIDService::decodeUUID($p["author"]["id"]);
                if (!is_null($p["page"])) {
                    $p["page"]["id"] = UUIDService::decodeUUID($p["page"]["id"]);
                    unset($p["page"]["owner"]);
                }
                array_push($tmpPosts, $p);
            }
            $tmp["posts"] = $tmpPosts;
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
    /**
     * @Route("/{id}", name="get_post", methods={"GET"})
     */
    public function show(string $id, SerializerInterface $serializer): JsonResponse
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find(UUIDService::encodeUUID($id));
        if (!$post) {
            return new JsonResponse(["error" => "post with this id does not exist"], 404);
        }
        $resData = $serializer->serialize($post, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post"]]);
        $tmp = json_decode($resData, true);
        $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
        return new JsonResponse($tmp, 200);
    }
    /**
     * @Route("", name="add_post", methods={"POST"})
     */
    public function create(Request $request, ImageUploader $imageUploader): JsonResponse
    {
        $pageID = $request->query->get("p", null);
        $payload = $request->attributes->get("payload");
        $post = new Post();
        $author = $this->getDoctrine()->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (!$author) {
            return new JsonResponse(["error" => "user with this id does not exist!"], 404);
        }
        if (!is_null($pageID)) {
            $page = $this->getDoctrine()->getRepository(Page::class)->find(UUIDService::encodeUUID($pageID));
            if (!$page) {
                return new JsonResponse(["error" => "page with this id does not exist!"], 404);
            } else {
                if ($page->getOwner()->getId() != UUIDService::encodeUUID($payload["user_id"])) {
                    return new JsonResponse(["error" => "this page does not belong to you!"], 403);
                }
                $post->setPage($page);
            }
        }
        if ($request->request->get("text") != "" && !is_null($request->request->get("text"))) {
            $post->setText($request->request->get("text"));
        } else {
            return new JsonResponse(["error" => "text cannot be empty"], 400);
        }
        if (!is_null($request->files->get("file"))) {
            if ($request->files->get("file")->getError() == 1) {
                return new JsonResponse(["error" => "something went wrong with reading file! it might be corrupted"], 500);
            } else {
                if (strpos($request->files->get("file")->getMimeType(), 'image') !== false || strpos($request->files->get("file")->getMimeType(), 'video') !== false) {
                    if ($request->files->get("file")->getSize() < 20480000) {
                        $post->setFileUrl($imageUploader->uploadFileToCloudinary($request->files->get("file")));
                    } else {
                        return new JsonResponse(["error" => "file is too big"], 400);
                    }
                } else {
                    return new JsonResponse(["error" => "wrong file type"], 400);
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
        $resData = $serializer->serialize($post, "json", ['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username", "gender", "post", "verified", "bannerUrl", "bio", "timezone"]]);
        $tmp = json_decode($resData, true);
        $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
        $tmp["author"]["id"] = UUIDService::decodeUUID($tmp["author"]["id"]);
        if (!is_null($tmp["page"])) {
            $tmp["page"]["id"] = UUIDService::decodeUUID($tmp["page"]["id"]);
            $tmp["page"]["owner"]["id"] = UUIDService::decodeUUID($tmp["page"]["owner"]["id"]);
        }
        return new JsonResponse($tmp, 201);
    }
    /**
     * @Route("/{id}", name="edit_post", methods={"POST"})
     */
    public function edit(Request $request, string $id, ImageUploader $imageUploader): JsonResponse
    {

        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find(UUIDService::encodeUUID($id));
        if (!$post) {
            return new JsonResponse(["error" => "Post does not exist"], 404);
        }
        if ($post->getAuthor()->getId() == UUIDService::encodeUUID($payload['user_id'])) {
            if ($request->request->get("text") != "" && !is_null($request->request->get("text"))) {
                $post->setText($request->request->get("text"));
            } else {
                return new JsonResponse(["error" => "text cannot be empty"], 400);
            }
            if (!is_null($request->files->get("file"))) {
                if ($request->files->get("file")->getError() == 1) {
                    return new JsonResponse(["error" => "something went wrong with reading file! it might be corrupted"], 500);
                } else {
                    if (strpos($request->files->get("file")->getMimeType(), 'image') !== false || strpos($request->files->get("file")->getMimeType(), 'video') !== false) {
                        if ($request->files->get("file")->getSize() < 204800) {
                            $post->setFileUrl($imageUploader->uploadFileToCloudinary($request->files->get("file")));
                        } else {
                            return new JsonResponse(["error" => "file is too big"], 400);
                        }
                    } else {
                        return new JsonResponse(["error" => "wrong file type"], 400);
                    }
                }
            } else {
                $post->setFileUrl(null);
            }
            $post->setEditedAt(new DateTime("now"));
            $em->persist($post);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($post, "json", ['ignored_attributes' => ['posts', "transitions", "password", "salt", "dateOfBirth", "roles", "email", "username", "gender", "post", "verified", "bannerUrl", "bio", "timezone", "poComments"]]);
            $tmp = json_decode($resData, true);
            $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
            $tmp["author"]["id"] = UUIDService::decodeUUID($tmp["author"]["id"]);
            return new JsonResponse(["message" => "Post has been edited", "post" => $tmp], 200);
        } else {
            return new JsonResponse(["error" => "This post does not belong to you"], 403);
        }
    }
    /**
     * @Route("/{id}", name="delete_post", methods={"DELETE"})
     */
    public function remove(Request $request, string $id): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find(UUIDService::encodeUUID($id));
        if (!$post) {
            return new JsonResponse(["error" => "Post does not exist"], 404);
        }
        if ($post->getAuthor()->getId() == UUIDService::encodeUUID($payload['user_id'])) {
            $em->remove($post);
            $em->flush();
            return new JsonResponse(["message" => "Post has been deleted"], 200);
        } else {
            return new JsonResponse(["error" => "This post does not belong to you"], 403);
        }
    }
}
