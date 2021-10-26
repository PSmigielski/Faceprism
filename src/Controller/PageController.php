<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\PageModeration;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Service\ImageUploader;
use App\Service\ValidatorService;
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
 * @Route("/v1/api/pages", name="page", defaults={"_is_api": true}, requirements={"pageId"="[0-9a-f]{32}"})
 */
class PageController extends AbstractController
{

    /**
     * @Route("", name="get_pages_for_user", methods={"GET"})
     */
    public function index(Request $request, PageRepository $repo): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $page = $request->query->get("page", 1);
        $qb = $repo->getAllPagesForUser(UUIDService::encodeUUID($payload["user_id"]));
        $data = PaginationService::paginate($page, $qb, "pages");
        if (gettype($data) == "array") {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $tmpPages = [];
            foreach ($data["pages"] as $value) {
                $res = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_user" => UUIDService::encodeUUID($payload["user_id"]), "pm_page" => $value->getId()]);
                $resData = $serializer->serialize($value, "json", ['ignored_attributes' => ["owner"]]);;
                $tmp = json_decode($resData, true);
                $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
                $tmp["role"] = $res[0]->getPageRole();
                array_push($tmpPages, $tmp);
            }
            $data["pages"] = $tmpPages;
            return new JsonResponse($data, 200);
        } else {
            return $data;
        }
    }
    /**
     * @Route("/{pageId}",name="get_page", methods={"GET"})
     */
    public function show(string $pageId): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if (is_null($page)) {
            return new JsonResponse(["error" => "Page with this id does not exist"], 404);
        } else {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($page, "json", ['ignored_attributes' => ["owner"]]);
            $tmp = json_decode($resData, true);
            $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
            return new JsonResponse($tmp, 200);
        }
    }
    /**
     * @Route("",name="create_or_edit_page", methods={"POST"})
     */
    public function create(Request $request, ImageUploader $imageUploader, ValidatorService $ValidatorService): JsonResponse
    {
        $pageID = $request->query->get("p", null);
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $name = $request->request->get("name", null);
        $email = $request->request->get("email", null);
        $bio = $request->request->get("bio", null);
        $website = $request->request->get("website", null);
        $profile_pic = $request->files->get("profile_pic", null);
        $banner = $request->files->get("banner", null);
        $data = [];
        $isEdited = false;


        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (is_null($pageID)) {
            $page = new Page();
        } else {
            $isEdited = true;
            $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageID));
            if (is_null($page)) {
                return new JsonResponse(["erorr" => "Page with this id does not exist"], 404);
            } else {
                $pageModeration = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_page" => UUIDService::encodeUUID($pageID)]);
                $flag = false;
                foreach ($pageModeration as $value) {
                    if ($value->getUser()->getId() == UUIDService::encodeUUID($payload["user_id"]) && $value->getPageRole() == "OWNER") {
                        $flag = true;
                    }
                }
                if (!$flag) {
                    return new JsonResponse(["error" => "You can't edit data of this page!"], 403);
                }
            }
        }
        if (is_null($name) && $isEdited !== true) {
            return new JsonResponse(["error" => "Page name is required"], 400);
        }
        if ($isEdited) {
            $page->setName($page->getName());
        } else {
            $page->setName($name);
        }
        if (!is_null($bio)) {
            $data["bio"] = $bio;
        }
        if (!is_null($email)) {
            $data["email"] = $email;
        }
        if (!is_null($website)) {
            $data["website"] = $website;
        }
        if (!is_null($profile_pic)) {
            $data["profile_pic"] = $profile_pic;
        } else {
            $page->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
        }
        if (!is_null($bio)) {
            $data["bio"] = $bio;
        }
        if (!is_null($banner)) {
            $data["banner"] = $banner;
        }
        if ($ValidatorService->validateFormData($data) !== true) {
            return $ValidatorService->validateFormData($data);
        } else {
            foreach ($data as $key => $value) {
                match ($key) {
                    "email" => $page->setEmail($value),
                    "banner" => $page->setBannerUrl($imageUploader->uploadFileToCloudinary($value, 820, 312, "banner")),
                    "bio" => $page->setBio($value),
                    "profile_pic" => $page->setProfilePicUrl($imageUploader->uploadFileToCloudinary($value, 200, 200, "profile_pic")),
                    "website" => $page->setWebsite($value)
                };
            }
        }
        $page->setFollowCount(0);
        $em->persist($page);
        $em->flush();
        if (!$isEdited) {
            $moderation = new PageModeration();
            $moderation->setUser($user);
            $moderation->setPage($page);
            $moderation->setPageRole("OWNER");
            $em->persist($moderation);
            $em->flush();
        }
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $data = $serializer->serialize($page, "json", ["ignored_attributes" => ["owner"]]);
        $tmp = json_decode($data, true);
        $tmp["id"] = UUIDService::decodeUUID($tmp["id"]);
        if ($isEdited) {
            $tmp1 = [];
            $tmp1["page"] = $tmp;
            $tmp1["message"] = "page has been edited successfully!";
            return new JsonResponse($tmp1, 201);
        }
        return new JsonResponse($tmp, 201);
    }
    /**
     * @Route("/{pageId}",name="remove_page", methods={"DELETE"})
     */
    public function remove(Request $request, string $pageId): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->find(UUIDService::encodeUUID($pageId));
        if ($page) {
            $pageModeration = $this->getDoctrine()->getRepository(PageModeration::class)->findBy(["pm_page" => UUIDService::encodeUUID($pageId)]);
            $flag = false;
            foreach ($pageModeration as $value) {
                if ($value->getUser()->getId() == UUIDService::encodeUUID($payload["user_id"]) && $value->getPageRole() == "OWNER") {
                    $flag = true;
                }
            }
            if (!$flag) {
                return new JsonResponse(["error" => "You can't delete this page!"], 403);
            }
            $em->remove($page);
            $em->flush();
            return new JsonResponse(["message" => "Page has been deleted"], 200);
        } else {
            return new JsonResponse(["error" => "Page with this id does not exist!"], 404);
        }
    }
}
