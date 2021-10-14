<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\PageModeration;
use App\Repository\PageModerationRepository;
use App\Service\UUIDService;
use Opis\JsonSchema\MediaTypes\Json;
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
        $payload = $request->attributes->get("payload");
        $qPage = $this->getDoctrine()->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if (is_null($qPage)) {
            return new JsonResponse(["error" => "page with this id does not exist"], 404);
        }
        $pageOwner = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_page" => UUIDService::encodeUUID($pageId), "pm_user" => UUIDService::encodeUUID($payload["user_id"]), "pm_page_role" => $this->PAGE_ROLES[0]]);
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
}
