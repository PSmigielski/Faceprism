<?php

namespace App\Controller;
use App\Repository\PageRepository;
use App\Service\UUIDService;
use PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/pages", name="page", defaults={"_is_api": true})
 */
class PageController extends AbstractController
{
    /**
     * @Route("", name="get_pages_for_user", defaults={"_is_api": true})
     */
    public function index(Request $request, PageRepository $repo):JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get("page", 1);
        $qb = $repo->getAllPagesForUser(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page, $qb, "pages");
        if(gettype($data) == "array"){
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $tmpPages = [];
            foreach ($data["pages"] as $value) {
                $resData = $serializer->serialize($value, "json",['ignored_attributes' => ["owner"]]);
                $resDataUser = $serializer->serialize($value->getOwner(), "json",['ignored_attributes' => ['posts', "dateOfBirth", "password", "email", "username","roles","gender", "salt", "post","verified", "bio", "bannerUrl"]]);
                $tmpuser = json_decode($resDataUser, true);
                $tmp = json_decode($resData, true);
                $tmpuser["id"] = UUIDService::decodeUUID($tmpuser["id"]);
                $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
                $tmp["owner"] = $tmpuser;
                array_push($tmpPages,$tmp);
            }
            $data["pages"] = $tmpPages;
            return new JsonResponse($data, 200);
        } else{
            return $data;
        }
    }
}
