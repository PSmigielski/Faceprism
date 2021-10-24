<?php

namespace App\Service;

use DateTime;
use Opis\JsonSchema\{
    Validator,
    Schema
};
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Return_;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class SchemaValidator
{
    public function validateSchema(string $pathToSchema, object $data)
    {
        $schema = Schema::fromJsonString(file_get_contents(__DIR__ . $pathToSchema));
        $validator = new Validator();
        $result = $validator->schemaValidation($data, $schema);
        if ($result->isValid()) {
            return true;
        } else {
            return $this->getErrorMessage($result);
        }
    }
    static public function verifyDate($date, $strict = true): bool
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        if ($strict) {
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return $dateTime !== false;
    }
    public function validateFormData(array $data): JsonResponse | bool
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case "email":
                    if (preg_match("/^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $value) === 0) {
                        return new JsonResponse(["error" => "wrong email format"], 400);
                    }
                    break;
                case "website":
                    if (preg_match("/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})$/", $value) === 0) {
                        return new JsonResponse(["error" => "wrong link format"], 400);
                    }
                    break;
                case "profile_pic":
                case "banner":
                    if (strpos($value->getMimeType(), 'image') === false) {
                        return new JsonResponse(["error" => "wrong file format"], 400);
                    }
                    break;
                case "bio":
                    if (strlen($value) > 256) {
                        return new JsonResponse(["error" => "bio is too long"], 400);
                    }
                    if (strlen($value) == 0) {
                        return new JsonResponse(["error" => "bio is empty"], 400);
                    }
                    break;
                case "text":
                    if ($value == "") {
                        return new JsonResponse(["error" => "text cannot be empty"], 400);
                    }
                    break;
                case "file":
                    if ($value->getError() == 1) {
                        return new JsonResponse(["error" => "something went wrong with reading file! it might be corrupted"], 500);
                    } else {
                        if (strpos($value->getMimeType(), 'image') !== false || strpos($value->getMimeType(), 'video') !== false) {
                            if ($value->getSize() > 1024 * 1024 * 10) {
                                return new JsonResponse(["error" => "file is too big"], 400);
                            }
                        } else {
                            return new JsonResponse(["error" => "wrong file type"], 400);
                        }
                    }
                    break;
            }
        }
        return true;
    }
    private function getErrorMessage(object $result): JsonResponse
    {
        switch ($result->getFirstError()->keyword()) {
            case "format":
                return new JsonResponse(["error" => "invalid email format"], 400);
                break;
            case "type":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "text":
                        return new JsonResponse(["error" => "text has invalid type"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error" => "password has invalid type"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error" => "date has invalid types wrong format"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error" => "email has invalid type"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error" => "name has invalid type"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error" => "surname has invalid type"], 400);
                        break;
                    case "img":
                        return new JsonResponse(["error" => "image has invalid type"], 400);
                        break;
                }
                break;
            case "maxLength":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "text":
                        return new JsonResponse(["error" => "text is too long"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error" => "password is too long"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error" => "date of birth has wrong format"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error" => "email is too long"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error" => "name is too long"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error" => "surname is too long"], 400);
                        break;
                    case "bio":
                        return new JsonResponse(["error" => "surname is too long"], 400);
                        break;
                }
                break;
            case "minLength":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "password":
                        return new JsonResponse(["error" => "password is too short"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error" => "date of birth has wrong format"], 400);
                        break;
                    case "author_uuid":
                        return new JsonResponse(["error" => "author uuid has wrong fromat"], 400);
                        break;
                    case "bio":
                        return new JsonResponse(["error" => "surname is too short"], 400);
                        break;
                }
                break;
            case "required":
                switch ($result->getFirstError()->keywordArgs()["missing"]) {
                    case "author_uuid":
                        return new JsonResponse(["error" => "author uuid is missing"], 400);
                        break;
                    case "post_uuid":
                        return new JsonResponse(["error" => "post uuid is missing"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error" => "password is missing"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error" => "date of birth is missing"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error" => "email is missing"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error" => "name is missing"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error" => "surname is missing"], 400);
                        break;
                    case "text":
                        return new JsonResponse(["error" => "text is missing"], 400);
                        break;
                }
                break;
            case "minProperties":
                return new JsonResponse(["error" => "given properties: " . $result->getFirstError()->keywordArgs()["count"] . "; minimum quantity of properties: " . $result->getFirstError()->keywordArgs()["min"]], 400);
                break;
            case "additionalProperties":
                return new JsonResponse(["error" => "no additional properties allowed"], 400);
                break;
        }
    }
}
