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
    public function testCantLoginWithWrongCredentials(): void
    {
        $client = self::createClient();
        $data = [
            "email" => "adsasda@gmail.com",
            "password" => "StrasdongPassword"
        ];;
        $client->request("POST", "http://localhost:8000/v1/api/auth/login", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertBrowserNotHasCookie("BEARER");
        $this->assertBrowserNotHasCookie("REFRESH_TOKEN");
        $data = json_decode($client->getResponse()->getContent(false), true);
        $this->assertArrayHasKey("error", $data);
        $this->assertEquals("Bad credentials.", $data["error"]);
        $data = ["password" => ""];
        $client->request("POST", "http://localhost:8000/v1/api/auth/login", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = json_decode($client->getResponse()->getContent(false), true);
        $this->assertArrayHasKey("error", $data);
        $this->assertEquals("The key \"email\" must be provided.", $data["error"]);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertBrowserNotHasCookie("BEARER");
        $this->assertBrowserNotHasCookie("REFRESH_TOKEN");
        $data = ["email" => ""];
        $client->request("POST", "http://localhost:8000/v1/api/auth/login", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = json_decode($client->getResponse()->getContent(false), true);
        $this->assertArrayHasKey("error", $data);
        $this->assertEquals("The key \"password\" must be provided.", $data["error"]);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertBrowserNotHasCookie("BEARER");
        $this->assertBrowserNotHasCookie("REFRESH_TOKEN");
    }
    //TODO: add tests for create method
}
