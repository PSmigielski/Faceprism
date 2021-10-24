<?php

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AuthControllerTest extends ApiTestCase
{
    public function testCanLogin(): void
    {
        $client = self::createClient();
        $data = [
            "email" => "adsa@gmail.com",
            "password" => "StrongPassword"
        ];
        $client->request("POST", "http://localhost:8000/v1/api/auth/login", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertBrowserHasCookie("BEARER");
        $this->assertBrowserHasCookie("REFRESH_TOKEN");
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $data);
        $this->assertArrayHasKey("roles", $data);
        $this->assertArrayHasKey("profile_pic", $data);
        $this->assertArrayHasKey("email", $data);
    }
}
