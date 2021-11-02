<?php

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class ProfileControllerTest extends ApiTestCase
{
    public function testCanShowProfile(): void
    {
        $client = self::createClient();
        $data = [
            "email" => "adsa@gmail.com",
            "password" => "StrongPassword"
        ];
        $cookies = $client->request("POST", "http://localhost:8000/v1/api/auth/login", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ])->getHeaders()["set-cookie"];
        dump($cookies);
        $id = "a16452b63ba911ec8778c2c48d75a3d3";
        $client->request(
            "GET",
            "http://localhost:8000/v1/api/profile/" . $id,
            [
                "headers" => [
                    "Content-Type" => 'application/json',
                    "set-cookie" => $cookies
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertBrowserHasCookie("BEARER");
        $this->assertBrowserHasCookie("REFRESH_TOKEN");
        $this->assertJsonEquals(
            [
                "name" => "Jonh",
                "surname" => "Lech",
                "dateOfBirth" => [
                    "offset" => 0,
                    "timestamp" => 1039651200
                ],
                "gender" => "male",
                "profilePicUrl" => "https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png",
                "bannerUrl" => null,
                "bio" => "oh my gott",
                "tag" => "@liechu"
            ]
        );
    }
    public function testCannotShowProfileWithoutCookies(): void
    {
        $id = "a16452b63ba911ec8778c2c48d75a3d3";
        $response = self::createClient()->request("GET", "http://localhost:8000/v1/api/profile/" . $id);
        $this->assertJsonEquals([
            "error" => "JWT Token not found"
        ]);
        $this->assertResponseStatusCodeSame(401);
        $this->assertBrowserNotHasCookie("BEARER");
        $this->assertBrowserNotHasCookie("REFRESH_TOKEN");
    }
}
