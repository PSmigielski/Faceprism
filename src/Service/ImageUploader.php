<?php
    namespace App\Service;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
 
    class ImageUploader
    {
        public function uploadFileToCloudinary(UploadedFile $file, int $width = 0, int $height = 0, string $type = "default")
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
            switch($type){
                case "profile_pic":
                    $config = [
                        "width" => $width, 
                        "height" => $height, 
                        "crop" => "fill",
                        "folder" => "profile_pics",
                        "gravity" => "face"
                    ];
                    break;
                case "banner":
                    $config = [
                        "width" => $width, 
                        "height" => $height, 
                        "crop" => "fill",
                        "folder" => "banners",
                        "gravity" => "face"
                    ];
                    break;
                case "default": 
                    $config = [
                        "folder" => "post_files",
                        "resource_type" => "auto"
                    ];
                    break;
            }
            $imageUploaded = (new UploadApi($cloudinary))->upload($fileName, $config);
            return $imageUploaded['secure_url'];
        }
    }
?>