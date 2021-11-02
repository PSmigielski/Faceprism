<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Page;
use App\Entity\PageModeration;
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
use App\Service\ValidatorService;
use App\Service\UUIDService;
use App\Service\PaginationService;

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
     * @Route("", name="add_or_edit_post", methods={"POST"})
     */
    public function create_or_edit(Request $request, ImageUploader $imageUploader, ValidatorService $ValidatorService): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $pageID = $request->query->get("p", null);
        $payload = $request->attributes->get("payload");
        $postID = $request->query->get("post", null);
        $isEdited = false;
        if (!is_null($postID)) {
            $isEdited = true;
        }
        if ($isEdited) {
            $post = $em->getRepository(Post::class)->find(UUIDService::encodeUUID($postID));
            if (is_null($post)) {
                return new JsonResponse(["error" => "post with this id does not exist!"], 404);
            }
            if (!is_null($post->getPage())) {
                $pageModeration = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_page" => $post->getPage()->getId()]);
                $flag = false;
                foreach ($pageModeration as $value) {
                    if ($value->getUser()->getId() == UUIDService::encodeUUID($payload["user_id"])) {
                        $flag = true;
                    }
                }
                if (!$flag) {
                    return new JsonResponse(["error" => "You can't modify post from this page!"], 403);
                }
            }
            if (is_null($post->getPage()) && $post->getAuthor()->getId() != UUIDService::encodeUUID($payload['user_id'])) {
                return new JsonResponse(["error" => "This post does not belong to you"], 402);
            }
            $post->setEditedAt(new DateTime("now"));
        } else {
            $post = new Post();
            $author = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
            if (!$author) {
                return new JsonResponse(["error" => "user with this id does not exist!"], 404);
            }
            if (!is_null($pageID)) {
                $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageID));
                if (!$page) {
                    return new JsonResponse(["error" => "page with this id does not exist!"], 404);
                } else {
                    $pageModeration = $em->getRepository(PageModeration::class)->findBy(["pm_page" => UUIDService::encodeUUID($pageID)]);
                    $flag = false;
                    foreach ($pageModeration as $value) {
                        if ($value->getUser()->getId() == UUIDService::encodeUUID($payload["user_id"])) {
                            $flag = true;
                        }
                    }
                    if (!$flag) {
                        return new JsonResponse(["error" => "You can't add content to this page!"], 403);
                    }
                    $post->setPage($page);
                }
            }
            $post->setAuthor($author);
            $post->setCreatedAt(new DateTime("now"));
            $post->setLikeCount(0);
            $post->setCommentCount(0);
        }
        $text = $request->request->get("text", null);
        $file = $request->files->get("file", null);
        $data = [];
        if (is_null($text) && is_null($file)) {
            return new JsonResponse(["error" => "text or file is required!"], 400);
        } else {
            if (!is_null($text)) {
                $data["text"] = $text;
            }
            if (!is_null($file)) {
                $data["file"] = $file;
                if (strpos($file->getMimeType(), "image") !== false) {
                    $data["fileType"] = "image";
                }
                if (strpos($file->getMimeType(), "video") !== false) {
                    $data["fileType"] = "video";
                }
            }
            $result = $ValidatorService->validateFormData($data);
            if ($result !== true) {
                return $result;
            } else {
                foreach ($data as $key => $value) {
                    match ($key) {
                        "text" => $post->setText($value),
                        "file" => $post->setFileUrl($imageUploader->uploadFileToCloudinary($value)),
                        "fileType" => $post->setFileType($value)
                    };
                }
            }
        }
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $em->persist($post);
        $em->flush();
        $resData = $serializer->serialize($post, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "website", "bannerUrl", "bio", "verified", "dateOfBirth", "poComments"]]);
        $tmp = json_decode($resData, true);
        $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
        $tmp["author"]["id"] = UUIDService::decodeUUID($tmp["author"]["id"]);
        if (!is_null($tmp["page"])) {
            $tmp["page"]["id"] = UUIDService::decodeUUID($tmp["page"]["id"]);
            unset($tmp["page"]["owner"]);
        }
        return $isEdited ? new JsonResponse(["message" => "Post has been edited", "post" => $tmp], 202) : new JsonResponse($tmp, 201);
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
        if (!is_null($post->getPage())) {
            $pageModeration = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_page" => $post->getPage()->getId()]);
            $flag = false;
            foreach ($pageModeration as $value) {
                if ($value->getUser()->getId() == UUIDService::encodeUUID($payload["user_id"])) {
                    $flag = true;
                }
            }
            if (!$flag) {
                return new JsonResponse(["error" => "You can't delete content from this page!"], 403);
            }
        }
        if (is_null($post->getPage()) && $post->getAuthor()->getId() != UUIDService::encodeUUID($payload['user_id'])) {
            return new JsonResponse(["error" => "This post does not belong to you"], 402);
        }
        $em->remove($post);
        $em->flush();
        return new JsonResponse(["message" => "Post has been deleted"], 200);
    }
}
