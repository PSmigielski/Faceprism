<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/api/friend", defaults={"_is_api": true})
 */
class FriendController extends AbstractController
{
    /**
     * @Route("/{userID}", methods={"GET"})
     */
    public function index() : JsonResponse
    {
        return new JsonResponse(["message"]);
    }
    /**
     * @Route("", methods={"POST"})
     */
    public function add() : JsonResponse
    {
        return new JsonResponse(["message"]);
    }
    /**
     * @Route("/{userID}/{friendID}", methods={"DELETE"})
     */
    public function remove(string $userID, string $friendID) : JsonResponse
    {
        return new JsonResponse(["message"]);
    }
}
