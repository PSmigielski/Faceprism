<?php
    namespace App\Service;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Uploader;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
 
    class ImageUploader
    {
        public function uploadFileToCloudinary(UploadedFile $file)
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
            $imageUploaded = (new UploadApi($cloudinary))->upload($fileName, [
                'folder' => 'profile_pics',
                'width' => 200,
                'height' => 200
            ]);
            return $imageUploaded['secure_url'];
        }
    }
?>