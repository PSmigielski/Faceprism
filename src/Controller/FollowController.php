<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\Page;
use App\Entity\User;
use App\Service\UUIDService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/api/follow", defaults={"_is_api": true}, requirements={"id"="[0-9a-f]{32}"})
 */
class FollowController extends AbstractController
{
    /**
     * @Route("/{id}", name="follow_page", methods={"POST"})
     */
    public function index(string $id, HttpFoundationRequest $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($id));
        if (!$user) {
            return new JsonResponse(["error" => "user with this id doesn't exist!"], 400);
        }
        if (!$page) {
            return new JsonResponse(["error" => "page with this id doesn't exist!"], 400);
        }
        $follow = $em->getRepository(Follow::class)->findBy(["fo_page" => UUIDService::encodeUUID($id), "fo_user" => UUIDService::encodeUUID($payload["user_id"])]);
        if (!empty($follow)) {
            $em->remove($follow[0]);
            $page->setFollowCount($page->getFollowCount() - 1);
            $em->persist($page);
            $em->flush();
            return new JsonResponse(["message" => "follow removed successfully"], 201);
        }
        $like = new Follow();
        $like->setPage($page);
        $like->setUser($user);
        $page->setFollowCount($page->getFollowCount() + 1);
        $em->persist($like);
        $em->persist($page);
        $em->flush();
        return new JsonResponse(["message" => "follow added successfully"], 201);
    }
}
