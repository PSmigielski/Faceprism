<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ImageUploader;
use App\Service\JsonDecoder;
use App\Service\ValidatorService;
use App\Service\UUIDService;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/v1/api/profile", defaults={"_is_api": true}, requirements={"id"="[0-9a-f]{32}"})
 */
class ProfileController extends AbstractController
{
    private ValidatorService $validator;
    private JsonDecoder $jsonDecoder;
    private EntityManagerInterface $em;
    private Serializer $serializer;
    public function __construct(
        JsonDecoder $jsonDecoder,
        ValidatorService $validator,
        EntityManagerInterface $em
    ) {
        $this->validator = $validator;
        $this->jsonDecoder = $jsonDecoder;
        $this->em = $em;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route("/{id}", name="get_profile",methods={"GET"})
     */
    public function show(string $id): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($id));
        if (is_null($user)) {
            return new JsonResponse(["error" => "user with this id does not exist!"], 404);
        } else {
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $resData = $serializer->serialize($user, "json", ['ignored_attributes' => ['posts', "transitions", "timezone", "roles", "email", "verified", "username", "password", "salt", "post", "user", "id"]]);
            return JsonResponse::fromJsonString($resData, 200);
        }
    }
    /**
     * @Route("/{imageType}", name="change_image",methods={"POST"})
     */
    public function updateProfilePic(Request $req, ImageUploader $imageUploader, string $imageType): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $payload = $req->attributes->get("payload");
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (is_null($user)) {
            return new JsonResponse(["error" => "user with this id does not exist!"], 404);
        } else {
            $width = $imageType == "banner" ? 820 : 200;
            $height = $imageType == "banner" ? 312 : 200;
            if (!is_null($req->files->get("image"))) {
                if (strpos($req->files->get("image")->getMimeType(), 'image') !== false) {
                    switch ($imageType) {
                        case "banner":
                            $user->setBannerUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), $width, $height, $imageType));
                            break;
                        case "profile_pic":
                            $user->setProfilePicUrl($imageUploader->uploadFileToCloudinary($req->files->get("image"), $width, $height, $imageType));
                            break;
                    }
                    $em->persist($user);
                    $em->flush();
                    switch ($imageType) {
                        case "banner":
                            return new JsonResponse(["message" => $imageType . " has been updated", "banner" => $user->getBannerUrl()], 201);
                            break;
                        case "profile_pic":
                            return new JsonResponse(["message" => $imageType . " has been updated", "profile_pic" => $user->getProfilePicUrl()], 201);
                            break;
                    }
                } else {
                    return new JsonResponse(["error" => "Wrong file format"], 415);
                }
            } else {
                return new JsonResponse(["error" => "Image not found"], 404);
            }
        }
    }
    /**
     * @Route("/bio", name="change_bio",methods={"PUT"})
     */
    public function updateBio(Request $req, ValidatorService $ValidatorService): JsonResponse
    {
        $payload = $req->attributes->get("payload");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (is_null($user)) {
            return new JsonResponse(["error" => "user with this id does not exist!"], 404);
        } else {
            $reqData = [];
            if ($content = $req->getContent()) {
                $reqData = json_decode($content, true);
            }
            $result = $ValidatorService->validateSchema('/../Schemas/profileUpdateBioSchema.json', (object)$reqData);
            if ($result === true) {
                $user->setBio($reqData["bio"]);
                $em->persist($user);
                $em->flush();
                return new JsonResponse(["message" => "bio has been updated", "bio" => $user->getBio()], 201);
            } else {
                return $result;
            }
        }
    }
    private function checkTag(User $user, string $tag)
    {
        $tmp = $this->em->getRepository(User::class)->findOneBy(["us_tag" => $tag]);
        if (is_null($tmp)) {
            $user->setTag($tag);
        } else {
            if ($tmp->getTag() === $user->getTag() && $tmp->getId() === $user->getId()) {
                throw new ErrorException("You have this tag already", 400);
            } else {
                throw new ErrorException("This tag is occupied", 400);
            }
        }
    }
    /**
     * @Route("/account", name="update_account", methods={"PUT"})
     */
    public function updateAccount(Request $request): JsonResponse
    {
        $payload = $request->attributes->get("payload");
        $requestData = $this->jsonDecoder->decode($request);
        $this->validator->validateSchema('/../Schemas/editAccountDataSchema.json', (object)$requestData);
        $user = $this->em->getRepository(User::class)->find(UUIDService::encodeUUID($payload["user_id"]));
        if (!$user) {
            return new ErrorException("User with this id does not exist!", 404);
        }
        foreach ($requestData as $key => $value) {
            match ($key) {
                "name" => $user->setName($value),
                "surname" => $user->setSurname($value),
                "date_of_birth" => $user->setDateOfBirth($value),
                "gender" => $user->setGender($value),
                "tag" => $this->checkTag($user, $value)
            };
        }
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse(["message" => "Account data has been modified"], 201);
    }
}
