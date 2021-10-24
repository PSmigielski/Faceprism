<?php

namespace App\Tests\Service;

use App\Service\UUIDService;
use PHPUnit\Framework\TestCase;

class UUIDServiceTest extends TestCase
{
    public function testCanDecodeUuid(): void
    {
        $uuid = "bddb17f8-34ed-11ec-bc16-1c1b0da97ebc";
        $result = UUIDService::decodeUUID($uuid);
        $this->assertEquals("bddb17f834ed11ecbc161c1b0da97ebc", $result);
    }
    public function testCanEncodeUuid(): void
    {
        $uuid = "bddb17f834ed11ecbc161c1b0da97ebc";
        $result = UUIDService::encodeUUID($uuid);
        $this->assertEquals("bddb17f8-34ed-11ec-bc16-1c1b0da97ebc", $result);
    }
}
