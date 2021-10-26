<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class JsonDecoder
{
    public function decode(Request $request): array
    {
        $reqData = [];
        if ($content = $request->getContent()) {
            $reqData = json_decode($content, true);
        }
        return $reqData;
    }
}
