<?php

namespace App\Service;

use ErrorException;
use Opis\JsonSchema\{
    Validator,
    Schema
};


class ValidatorService
{
    public function validateSchema(string $pathToSchema, object $data)
    {
        $schema = Schema::fromJsonString(file_get_contents(__DIR__ . $pathToSchema));
        $validator = new Validator();
        $result = $validator->schemaValidation($data, $schema);
        if (!$result->isValid()) {
            return $this->getErrorMessage($result);
        }
    }
    public function validateFormData(array $data)
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case "email":
                    if (preg_match("/^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $value) === 0) {
                        throw new ErrorException("wrong email format", 400);
                    }
                    break;
                case "website":
                    if (preg_match("/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})$/", $value) === 0) {
                        throw new ErrorException("wrong link format", 400);
                    }
                    break;
                case "profile_pic":
                case "banner":
                    if (strpos($value->getMimeType(), 'image') === false) {
                        throw new ErrorException("wrong file format", 400);
                    }
                    break;
                case "bio":
                    if (strlen($value) > 256) {
                        throw new ErrorException("bio is too long", 400);
                    }
                    if (strlen($value) == 0) {
                        throw new ErrorException("bio is empty", 400);
                    }
                    break;
                case "text":
                    if ($value == "") {
                        throw new ErrorException("text cannot be empty", 400);
                    }
                    break;
                case "file":
                    if ($value->getError() == 1) {
                        throw new ErrorException("something went wrong with reading file! it might be corrupted", 500);
                    } else {
                        if (strpos($value->getMimeType(), 'image') !== false || strpos($value->getMimeType(), 'video') !== false) {
                            if ($value->getSize() > 1024 * 1024 * 10) {
                                throw new ErrorException("file is too big", 400);
                            }
                        } else {
                            throw new ErrorException("wrong file type", 400);
                        }
                    }
                    break;
            }
        }
        return true;
    }
    private function getErrorMessage(object $result)
    {
        switch ($result->getFirstError()->keyword()) {
            case "enum":
                throw new ErrorException("expected male or female", 400);
                break;
            case "format":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "email":
                        throw new ErrorException("invalid email format", 400);
                        break;
                    case "date_of_birth":
                        throw new ErrorException("invalid date format YYYY-MM-DD required", 400);
                        break;
                }
                break;
            case "type":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "text":
                        throw new ErrorException("text has invalid type", 400);
                        break;
                    case "password":
                        throw new ErrorException("password has invalid type", 400);
                        break;
                    case "date_of_birth":
                        throw new ErrorException("date has invalid types wrong format", 400);
                        break;
                    case "email":
                        throw new ErrorException("email has invalid type", 400);
                        break;
                    case "name":
                        throw new ErrorException("name has invalid type", 400);
                        break;
                    case "surname":
                        throw new ErrorException("surname has invalid type", 400);
                        break;
                    case "img":
                        throw new ErrorException("image has invalid type", 400);
                        break;
                }
                break;
            case "maxLength":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "text":
                        throw new ErrorException("text is too long", 400);
                        break;
                    case "password":
                        throw new ErrorException("password is too long", 400);
                        break;
                    case "date_of_birth":
                        throw new ErrorException("date of birth has wrong format", 400);
                        break;
                    case "email":
                        throw new ErrorException("email is too long", 400);
                        break;
                    case "name":
                        throw new ErrorException("name is too long", 400);
                        break;
                    case "surname":
                        throw new ErrorException("surname is too long", 400);
                        break;
                    case "bio":
                        throw new ErrorException("surname is too long", 400);
                        break;
                }
                break;
            case "minLength":
                switch ($result->getFirstError()->dataPointer()[0]) {
                    case "password":
                        throw new ErrorException("password is too short", 400);
                        break;
                    case "bio":
                        throw new ErrorException("surname is too short", 400);
                        break;
                }
                break;
            case "required":
                switch ($result->getFirstError()->keywordArgs()["missing"]) {
                    case "password":
                        throw new ErrorException("password is missing", 400);
                        break;
                    case "date_of_birth":
                        throw new ErrorException("date of birth is missing", 400);
                        break;
                    case "email":
                        throw new ErrorException("email is missing", 400);
                        break;
                    case "name":
                        throw new ErrorException("name is missing", 400);
                        break;
                    case "surname":
                        throw new ErrorException("surname is missing", 400);
                        break;
                    case "text":
                        throw new ErrorException("text is missing", 400);
                        break;
                    case "gender":
                        throw new ErrorException("gender is missing", 400);
                        break;
                }
                break;
            case "minProperties":
                throw new ErrorException("given properties: " . $result->getFirstError()->keywordArgs()["count"] . "; minimum quantity of properties: " . $result->getFirstError()->keywordArgs()["min"], 400);
                break;
            case "additionalProperties":
                throw new ErrorException("no additional properties allowed", 400);
                break;
        }
    }
}
