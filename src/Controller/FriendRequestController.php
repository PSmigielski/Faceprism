<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\FriendRequest;
use App\Entity\User;
use App\Repository\FriendRequestRepository;
use App\Service\UUIDService;
use DateTime;
use PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/friendRequest", defaults={"_is_api": true})
 */
class FriendRequestController extends AbstractController
{
    /**
     * @Route("",name="get_friend_requests", methods={"GET"})
     */
    public function index(FriendRequestRepository $repo, Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get('page', 1);
        $qb = $repo->createGetAllFriendRequests(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page,$qb,"requests");
        if(gettype($data)=="array"){
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['posts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            $tmp = json_decode($resData, true);
            $tmpRequests = [];
            foreach($tmp["requests"] as $r){
                $r["id"] = UUIDService::decodeUUID($r["id"]);
                $r["user"]["id"] = UUIDService::decodeUUID($r["user"]["id"]);
                $r["friend"]["id"] = UUIDService::decodeUUID($r["friend"]["id"]);
                array_push($tmpRequests, $r);
            }
            $tmp["requests"] = $tmpRequests;
            return new JsonResponse($tmp, 200);
        } else {
            return $data;
        }
    }
    /**
     * @Route("/{friendID}",name="add_friend_request", methods={"POST"})
     */
    public function add(Request $req, string $friendID):JsonResponse
    {
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $fr_req = new FriendRequest();
        if($payload["user_id"]===$friendID){
            return new JsonResponse(["error" => "You can't request yourself!"],400);
        }
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        $friend = $em->getRepository(User::class)->find(UUIDService::encodeUUID($friendID));
        if(!$user || !$friend){
            return new JsonResponse(["error" => "User with this id does not exist!"], 404);
        }
        $tempFriend = $em->getRepository(Friend::class)->findBy([
            "fr_user" => $user,
            "fr_friend" => $friend
        ]);
        $tempReq = $em->getRepository(FriendRequest::class)->findBy([
            "fr_req_user" => $user,
            "fr_req_friend" => $friend
        ]);
        if($tempFriend){
            return new JsonResponse(["error" => "You have this person in friends!"], 400);
        }
        if(!$tempReq){
            $fr_req->setUser($user);
            $fr_req->setFriend($friend);
            $fr_req->setRequestDate(new DateTime("now"));
            $fr_req->setAccepted(false);
            $em->persist($fr_req);
            $em->flush();
            return new JsonResponse(["message" => "Friend Request has been sent"], 200);
        }else{
            return new JsonResponse(["error" => "Pending request with this friend exist!"], 400);
        }
    }
    /**
     * @Route("/accept/{requestID}",name="accept_friend_request" , methods={"POST"})
     */
    public function accept(Request $req, string $requestID) :JsonResponse
    { 
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $fr_req = $em->getRepository(FriendRequest::class)->find(UUIDService::encodeUUID($requestID));
        if($fr_req){
            if($payload["user_id"] == UUIDService::decodeUUID($fr_req->getFriend()->getId())){
                $friend1 = new Friend();
                $friend1->setFriend($fr_req->getFriend());
                $friend1->setUser($fr_req->getUser());
                $friend1->setAcceptDate(new Datetime('now'));
                $friend1->setIsBlocked(false);
                $friend2 = new Friend();
                $friend2->setFriend($fr_req->getUser());
                $friend2->setUser($fr_req->getFriend());
                $friend2->setAcceptDate(new Datetime('now'));
                $friend2->setIsBlocked(false);
                $em->remove($fr_req);
                $em->persist($friend1);
                $em->persist($friend2);
                $em->flush();
            }else{
                return new JsonResponse(["error"=>"You can't accept this request"],403);
            }
            return new JsonResponse(["message" => "Friend added successfully!"], 201);
        }else{
            return new JsonResponse(["error" => "Request with this id does not exist!"], 404);
        }
    }
    /**
     * @Route("/reject/{requestID}",name="reject_friend_request" , methods={"DELETE"})
     */
    public function reject(string $requestID) :JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $fr_req = $em->getRepository(FriendRequest::class)->find(UUIDService::encodeUUID($requestID));
        if($fr_req){
            $em->remove($fr_req);
            $em->flush();
            return new JsonResponse(["message" => "Friend request rejected successfully!"], 201);
        }else{
            return new JsonResponse(["error" => "Request with this id does not exist!"], 404);
        }
    }
}
