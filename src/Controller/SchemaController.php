<?php 
namespace App\Controller;

use DateTime;
use Opis\JsonSchema\{
    Validator, Schema
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SchemaController extends AbstractController
{
    public function validateSchema(string $pathToSchema, object $data){
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.$pathToSchema));
        $validator = new Validator();
        $result = $validator->schemaValidation($data, $schema);
        if($result->isValid()){
            return true;
        }
        else{
            return $this->getErrorMessage($result);
        }
    }
    static public function verifyDate($date, $strict = true) : bool{
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if ($strict) {
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count'])) {
            return false;
        }
    }
    return $dateTime !== false;
    }
    private function getErrorMessage(object $result) : JsonResponse {
        switch($result->getFirstError()->keyword()){
            case "format":
                return new JsonResponse(["error"=>"invalid email format"], 400);
                break;
            case "type":
                switch($result->getFirstError()->dataPointer()[0]){
                    case "text":
                        return new JsonResponse(["error"=>"text has invalid type"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error"=>"password has invalid type"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error"=>"date has invalid types wrong format"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error"=>"email has invalid type"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error"=>"name has invalid type"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error"=>"surname has invalid type"], 400);
                        break;
                    case "img":
                        return new JsonResponse(["error"=>"image has invalid type"], 400);
                        break;
                }
                break;
            case "maxLength":
                switch($result->getFirstError()->dataPointer()[0]){
                    case "text":
                        return new JsonResponse(["error"=>"text is too long"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error"=>"password is too long"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error"=>"date of birth has wrong format"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error"=>"email is too long"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error"=>"name is too long"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error"=>"surname is too long"], 400);
                        break;
                }
                break;
            case "minLength":
                switch($result->getFirstError()->dataPointer()[0]){
                    case "password":
                        return new JsonResponse(["error"=>"password is too short"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error"=>"date of birth has wrong format"], 400);
                        break;
                    case "author_uuid":
                        return new JsonResponse(["error"=>"author uuid has wrong fromat"], 400);
                        break;
                    }
                break;
            case "required":
                switch ($result->getFirstError()->keywordArgs()["missing"]) {
                    case "author_uuid":
                        return new JsonResponse(["error"=>"author uuid is missing"], 400);
                        break;
                    case "post_uuid":
                        return new JsonResponse(["error"=>"post uuid is missing"], 400);
                        break;
                    case "password":
                        return new JsonResponse(["error"=>"password is too long"], 400);
                        break;
                    case "date_of_birth":
                        return new JsonResponse(["error"=>"date of birth has wrong format"], 400);
                        break;
                    case "email":
                        return new JsonResponse(["error"=>"email is too long"], 400);
                        break;
                    case "name":
                        return new JsonResponse(["error"=>"name is too long"], 400);
                        break;
                    case "surname":
                        return new JsonResponse(["error"=>"surname is too long"], 400);
                        break;
                }
                break;
        }
    }   
}
?>