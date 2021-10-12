<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class FollowController extends AbstractController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(["message" => "gitgut"], 200);
    }
}
