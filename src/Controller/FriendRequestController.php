<?php

namespace App\Controller;

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
     * @Route("/{userID}", methods={"GET"})
     */
    public function index(FriendRequestRepository $repo, Request $request, string $userID): JsonResponse
    {
        try{
            $page = $request->query->get('page', 1);
            $qb = $repo->createGetAllFriendRequests($userID);
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
     * @Route("", methods={"POST"})
     */
    public function add(Request $req, SchemaController $schemaController){
        $reqData = [];
        if($content = $req->getContent()){
            $reqData = json_decode($content, true);
        }
        $result = $schemaController->validateSchema('/../Schemas/friendRequestSchema.json', (object) $reqData);
        if($result === true){
            $em = $this->getDoctrine()->getManager();
            $fr_req = new FriendRequest();
            $user = $em->getRepository(User::class)->find($reqData['userID']);
            $friend = $em->getRepository(User::class)->find($reqData['friendID']);
            if(!$user || !$friend){
                return new JsonResponse("User with this id does not exist!", 404);
            }
            $fr_req->setUser($user);
            $fr_req->setFriend($friend);
            $fr_req->setRequestDate(new DateTime("now"));
            $fr_req->setAccepted(false);
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($fr_req, "json",['ignored_attributes' => ['usPosts', "transitions", "password", "salt", "dateOfBirth", "roles"]]);
            $em->persist($fr_req);
            $em->flush();
            return JsonResponse::fromJsonString($resData, 201);
        }
    }
}
