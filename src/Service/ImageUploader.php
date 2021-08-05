<?php
    namespace App\Service;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
 
    class ImageUploader
    {
        public function uploadFileToCloudinary(UploadedFile $file, int $width, int $height, string $type)
        {
            $cloudinary = Configuration::instance([
                'cloud' => [
                  'cloud_name' => $_ENV["CLOUDINARY_CLOUD_NAME"], 
                  'api_key' => $_ENV["CLOUDINARY_API_KEY"], 
                  'api_secret' => $_ENV["CLOUDINARY_API_SECRET"]
                ],
                'url' => [
                  'secure' => true]]);
              
            $fileName = $file->getRealPath();  
            $config = [
                "width" => $width, 
                "height" => $height, 
                "crop" => "fill"
            ];
            switch($type){
                case "profile_pic":
                    $config["folder"] = "profile_pics";
                    $config["gravity"] = "face";
                    break;
                case "banner":
                    $config["folder"] = "banners";
                    break;
            }
            $imageUploaded = (new UploadApi($cloudinary))->upload($fileName, $config);
            return $imageUploaded['secure_url'];
        }
    }
?>