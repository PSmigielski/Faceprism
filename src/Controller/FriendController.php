<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Repository\FriendRepository;
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
 * @Route("/v1/api/friend", defaults={"_is_api": true})
 */
class FriendController extends AbstractController
{
    /**
     * @Route("/{userID}", methods={"GET"})
     */
    public function index(FriendRepository $repo, Request $request, string $userID) : JsonResponse
    {
        try{
            $page = $request->query->get('page', 1);
            $qb = $repo->createGetAllFriends($userID);
            $adapter = new QueryAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(30);
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
            $resData = $serializer->serialize($data, "json",['ignored_attributes' => ['usPosts', "transitions", "timezone", "password", "email", "username","roles","gender", "salt", "post", "user", "id"]]);
            return JsonResponse::fromJsonString($resData, 200);
        }
        catch(OutOfRangeCurrentPageException $e){
            return new JsonResponse(["message"=>"Page not found"], 404);
        }
    }
    /**
     * @Route("/{userID}/{friendID}", methods={"DELETE"})
     */
    public function remove(string $userID, string $friendID) : JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $friend1 = $em->getRepository(Friend::class)->findBy(["fr_user"=>$userID, "fr_friend"=>$friendID]);
        $friend2 = $em->getRepository(Friend::class)->findBy(["fr_user"=>$friendID, "fr_friend"=>$userID]);
        if(!$friend1 || !$friend1){
            return new JsonResponse(["error"=>"this relation does not exist!"], 404);
        }else{
            $em->remove($friend1[0]);
            $em->remove($friend2[0]);
            $em->flush();
            return new JsonResponse(["friend has been removed successfully!"]);
        }
    }
}
