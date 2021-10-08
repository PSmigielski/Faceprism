<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/v1/api/pages/moderation",name="name",defaults={"_is_api": true},requirements={"pageId"="[0-9a-f]{32}"})
*/
class PageModerationController extends AbstractController
{
    static private array $PAGE_ROLES = ["OWNER", "MODERATOR"];
    
    /**
     * @Route("/{pageID}",name="get_administration")
     */
    public function index(string $pageId):JsonResponse 
    {
        return new JsonResponse();
    }
    public function add(string $pageId):JsonResponse
    {
        return new JsonResponse();
    }
}
