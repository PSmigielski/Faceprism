<?php 

namespace App\Service;

class UUIDService
{
    public function decodeUUID(string $uuid):string
    {
        return str_replace("-","",$uuid);
    }
    public function encodeUUID(string $uuid):string
    {
        return $str = substr($uuid, 0, 8)."-".substr($uuid, 8, 4)."-".substr($uuid, 12, 4)."-".substr($uuid, 16,4)."-".substr($uuid, 20);
    }
}
?>