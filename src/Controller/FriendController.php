<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Repository\FriendRepository;
use App\Service\UUIDService;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/friend", defaults={"_is_api": true})
 */
class FriendController extends AbstractController
{
    /**
     * @Route("",name="get_friends", methods={"GET"})
     */
    public function index(FriendRepository $repo, Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get('page', 1);
        $qb = $repo->createGetAllFriends(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page, $qb, "friends");
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "user"]]);
            $tmp = json_decode($resData, true);
            $tmpComments = [];
            foreach ($tmp["friends"] as $f) {
                $f["id"] = UUIDService::decodeUUID($f["id"]);
                $f["friend"]["id"] = UUIDService::decodeUUID($f["friend"]["id"]);
                array_push($tmpComments, $f);
            }
            $tmp["friends"] = $tmpComments;
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
    /**
     * @Route("/blocklist/{friendID}",name="get_blocked_friends", methods={"PUT"})
     */
    public function block(string $friendID): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $friend = $em->getRepository(Friend::class)->find(UUIDService::encodeUUID($friendID));
        if (is_null($friend)) {
            return new JsonResponse(["error" => "this relation does not exist!"], 404);
        } else {
            if ($friend->getIsBlocked()) {
                $friend->setIsBlocked(false);
                $em->persist($friend);
                $em->flush();
                return new JsonResponse(["message" => "friend has been unblocked successfully!"]);
            } else {
                $friend->setIsBlocked(true);
                $em->persist($friend);
                $em->flush();
                return new JsonResponse(["message" => "friend has been blocked successfully!"]);
            }
        }
    }
    /**
     * @Route("/{id}",name="delete_friend", methods={"DELETE"},requirements={"id"="[0-9a-f]{32}"})
     */
    public function remove(Request $req, string $id): JsonResponse
    {
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $friend1 = $em->getRepository(Friend::class)->findBy(["fr_user" => UUIDService::encodeUUID($payload["user_id"]), "fr_friend" => UUIDService::encodeUUID($id)]);
        $friend2 = $em->getRepository(Friend::class)->findBy(["fr_user" => UUIDService::encodeUUID($id), "fr_friend" => UUIDService::encodeUUID($payload["user_id"])]);
        if (!$friend1 || !$friend1) {
            return new JsonResponse(["error" => "this relation does not exist!"], 404);
        } else {
            $em->remove($friend1[0]);
            $em->remove($friend2[0]);
            $em->flush();
            return new JsonResponse(["message" => "friend has been removed successfully!"]);
        }
    }
    /**
     * @Route("/blocklist",name="block_friend", methods={"GET"})
     */
    public function get_blocked(FriendRepository $repo, Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get('page', 1);
        $qb = $repo->createGetAllBlockedFriends(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page, $qb, "blocked");
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username", "roles", "gender", "salt", "post", "user"]]);
            $tmp = json_decode($resData, true);
            $tmpComments = [];
            foreach ($tmp["blocked"] as $f) {
                $f["id"] = UUIDService::decodeUUID($f["id"]);
                $f["friend"]["id"] = UUIDService::decodeUUID($f["friend"]["id"]);
                array_push($tmpComments, $f);
            }
            $tmp["blocked"] = $tmpComments;
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
}
