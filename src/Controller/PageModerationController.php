<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\PageModeration;
use App\Entity\User;
use App\Repository\PageModerationRepository;
use App\Service\UUIDService;
use PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/pages/moderation",name="name",defaults={"_is_api": true},requirements={"pageId"="[0-9a-f]{32}"})
 */
class PageModerationController extends AbstractController
{
    private array $PAGE_ROLES = ["OWNER", "MODERATOR"];

    /**
     * @Route("/{pageId}",name="get_administration", methods={"GET"})
     */
    public function index(string $pageId, Request $request, PageModerationRepository $repo): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $payload = $request->attributes->get("payload");
        $qPage = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if (is_null($qPage)) {
            return new JsonResponse(["error" => "page with this id does not exist"], 404);
        }
        $pageOwner = $em->getRepository(PageModeration::class)->findBy(["pm_page" => UUIDService::encodeUUID($pageId), "pm_user" => UUIDService::encodeUUID($payload["user_id"]), "pm_page_role" => $this->PAGE_ROLES[0]]);
        if (empty($pageOwner)) {
            return new JsonResponse(["error" => "You do not have permission to see this"], 403);
        }
        $page = $request->query->get("p", 1);
        $qb = $repo->getAllAdministrationQuery(UUIDService::encodeUUID($pageId));
        $data = PaginationService::paginate($page, $qb, "administration");
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $jsonData = $serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "page", "verified", "bannerUrl", "bio", "dateOfBirth"]]);
            $tmp = json_decode($jsonData, true);
            foreach ($tmp["administration"] as $key => $value) {
                $value["id"] = UUIDService::decodeUUID($value["id"]);
                $value["user"]["id"] = UUIDService::decodeUUID($value["user"]["id"]);
                $tmp["administration"][$key] = $value;
            }
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
    /**
     * @Route("/{pageId}",name="add_moderator", methods={"POST"})
     */
    public function add(string $pageId, Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $payload = $request->attributes->get("payload");
        $userId = $request->query->get("u", null);
        $role = $request->query->get("role", null);
        $approve = $request->query->get("a", false);
        if (is_null($userId)) {
            return new JsonResponse(["error" => "no user id provided"], 400);
        }
        if (is_null($role)) {
            return new JsonResponse(["error" => "no role given"], 400);
        }
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($userId));
        if (is_null($user)) {
            return new JsonResponse(["error" => "user with this id does not exist"], 404);
        }
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if (is_null($page)) {
            return new JsonResponse(["error" => "page with this id does not exist"], 404);
        }
        $permissions = $em->getRepository(PageModeration::class)->findOneBy(["pm_user" => UUIDService::encodeUUID($payload["user_id"]), "pm_page" => UUIDService::encodeUUID($pageId)]);
        if ($permissions->getPageRole() != $this->PAGE_ROLES[0]) {
            return new JsonResponse(["error" => "you don't have permission to give roles"], 403);
        }
        if ($userId == $payload["user_id"]) {
            return new JsonResponse(["error" => "you can't give the role to yourself"], 400);
        }
        //$owner = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        $moderator = $em->getRepository(PageModeration::class)->findOneBy(["pm_user" => UUIDService::encodeUUID($userId), "pm_page" => UUIDService::encodeUUID($pageId)]);
        if ($role == $this->PAGE_ROLES[0]) {
            switch ($approve) {
                case true:
                    if (is_null($moderator)) {
                        return new JsonResponse(["error" => "you can't transfer ownership to this user!"], 403);
                    }
                    $moderator->setPageRole($this->PAGE_ROLES[0]);
                    $permissions->setPageRole($this->PAGE_ROLES[1]);
                    $em->persist($moderator);
                    $em->persist($permissions);
                    break;
                case false:
                    return new JsonResponse(["error" => "you have not accepted the transfer of ownership for a user!"], 400);
                    break;
            }
        } else {
            if ($role != $this->PAGE_ROLES[1]) {
                return new JsonResponse(["error" => "invalid role given"], 400);
            }
            if (!is_null($moderator) && $moderator->getPageRole() == $role) {
                return new JsonResponse(["error" => "this user has this role already"], 400);
            }
            $newModerator = new PageModeration();
            $newModerator->setPage($page);
            $newModerator->setUser($user);
            $newModerator->setPageRole($role);
            $em->persist($newModerator);
        }
        $em->flush();
        $tag = $user->getTag();
        return new JsonResponse(["message" => "$role has been given to $tag"]);
    }
    /**
     * @Route("/{pageId}",name="remove_moderator", methods={"DELETE"})
     */
    public function remove(string $pageId, Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $payload = $request->attributes->get("payload");
        $userId = $request->query->get("u", null);
        if (is_null($userId)) {
            return new JsonResponse(["error" => "no user id provided"], 400);
        }
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($userId));
        if (is_null($user)) {
            return new JsonResponse(["error" => "user with this id does not exist"], 404);
        }
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if (is_null($page)) {
            return new JsonResponse(["error" => "page with this id does not exist"], 404);
        }
        $permissions = $em->getRepository(PageModeration::class)->findOneBy(["pm_user" => UUIDService::encodeUUID($payload["user_id"]), "pm_page" => UUIDService::encodeUUID($pageId)]);
        if ($permissions->getPageRole() != $this->PAGE_ROLES[0]) {
            return new JsonResponse(["error" => "you don't have permission to remove users from page moderation"], 403);
        }
        if ($userId == $payload["user_id"]) {
            return new JsonResponse(["error" => "you can't remove yourself"], 400);
        }
        $moderator = $em->getRepository(PageModeration::class)->findOneBy(["pm_user" => UUIDService::encodeUUID($userId), "pm_page" => UUIDService::encodeUUID($pageId)]);
        if (is_null($moderator)) {
            return new JsonResponse(["error" => "this user is not in page moderation!"], 404);
        }
        $em->remove($moderator);
        $em->flush();
        return new JsonResponse(["message" => "user has been deleted from page moderation"], 202);
    }
}
