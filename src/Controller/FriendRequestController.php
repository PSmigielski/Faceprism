<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\FriendRequest;
use App\Entity\User;
use App\Repository\FriendRequestRepository;
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

/**
 * @Route("/v1/api/friendRequest", defaults={"_is_api": true})
 */
class FriendRequestController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function index(FriendRequestRepository $repo, Request $request): JsonResponse
    {
        try{
            $payload = $request->attributes->get("payload");
            $page = $request->query->get('page', 1);
            $qb = $repo->createGetAllFriendRequests($payload["user_id"]);
            $adapter = new QueryAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(25);
            $pagerfanta->setCurrentPage($page);
            $requests = array();
            foreach($pagerfanta->getCurrentPageResults() as $req){
                $requests[] = $req;
            } 
            $data = [
                "page"=> $page,
                "totalPages" => $pagerfanta->getNbPages(),
                "count" => $pagerfanta->getNbResults(),
                "requests"=> $requests
            ];
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['usPosts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post"]]);
            return JsonResponse::fromJsonString($resData, 200);
        }
        catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }
    }
    /**
     * @Route("/{friendID}", methods={"POST"})
     */
    public function add(Request $req, string $friendID):JsonResponse
    {
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $fr_req = new FriendRequest();
        if($payload["user_id"]===$friendID){
            return new JsonResponse(["error" => "You can't request yourself!"],400);
        }
        $user = $em->getRepository(User::class)->find($payload["user_id"]);
        $friend = $em->getRepository(User::class)->find($friendID);
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
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($fr_req, "json",['ignored_attributes' => ['usPosts', "transitions", "password", "salt", "dateOfBirth", "roles"]]);
            $em->persist($fr_req);
            $em->flush();
            return JsonResponse::fromJsonString($resData, 201);
        }else{
            return new JsonResponse(["error" => "Pending request with this friend exist!"], 400);
        }
    }
    /**
     * @Route("/accept/{requestID}", methods={"POST"})
     */
    public function accept(string $requestID) :JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $fr_req = $em->getRepository(FriendRequest::class)->find($requestID);
        if($fr_req){
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
            return new JsonResponse(["message" => "friend added successfully!"], 201);
        }else{
            return new JsonResponse(["error" => "request with this id does not exist!"], 404);
        }
    }
    /**
     * @Route("/reject/{requestID}", methods={"DELETE"})
     */
    public function reject(string $requestID) :JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $fr_req = $em->getRepository(FriendRequest::class)->find($requestID);
        if($fr_req){
            $em->remove($fr_req);
            $em->flush();
            return new JsonResponse(["message" => "friend request rejected successfully!"], 201);
        }else{
            return new JsonResponse(["error" => "request with this id does not exist!"], 404);
        }
    }
}
