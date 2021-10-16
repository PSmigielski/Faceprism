<?php 

namespace App\Service;

class UUIDService
{
    static public function decodeUUID(string $uuid):string
    {
        return str_replace("-","",$uuid);
    }
    static public function encodeUUID(string $uuid):string
    {
        return substr($uuid, 0, 8)."-".substr($uuid, 8, 4)."-".substr($uuid, 12, 4)."-".substr($uuid, 16,4)."-".substr($uuid, 20);
    }
}
?>