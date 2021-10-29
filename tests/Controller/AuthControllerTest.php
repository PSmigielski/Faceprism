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
    public function testCannotLoginWithWrongCredentials(): void
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
    }
    public function testCannotLoginWithoutEmail(): void
    {
        $client = self::createClient();
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
    }
    public function testCannotLoginWithoutPassword(): void
    {
        $client = self::createClient();
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
    public function testCreateUserWithValidData(): void
    {
        $data = [
            "email" => "xdsadadasd@gmail.com",
            "password" => "StrongPassword",
            "name" => "jan",
            "surname" =>  "dumas",
            "date_of_birth" =>  "2002-11-20",
            "gender" => "male"
        ];
        $client = self::createClient();
        $client->request("POST", "http://localhost:8000/v1/api/auth/register", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = $client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertArrayHasKey("message", $data);
        $this->assertArrayHasKey("isMailSent", $data);
        $this->assertEquals("Your account has been created successfully!", $data["message"]);
        $this->assertEquals(true, $data["isMailSent"]);
    }
    public function testCannotCreateUserWithoutData(): void
    {
        $fullData = [
            "email" => "xdsadadasd@gmail.com",
            "password" => "StrongPassword",
            "name" => "jan",
            "surname" =>  "dumas",
            "date_of_birth" =>  "2002-11-20",
        ];
        $errorResponses = [
            "email is missing",
            "password is missing",
            "name is missing",
            "surname is missing",
            "date of birth is missing",
            "gender is missing"
        ];
        $client = self::createClient();
        for ($i = 0; $i < 6; $i++) {
            $testData = [];
            if ($i != 0) {
                for ($j = $i - 1; $j >= 0; $j--) {
                    $testData[array_keys($fullData)[$j]] = array_values($fullData)[$j];
                }
            }
            $client->request("POST", "http://localhost:8000/v1/api/auth/register", [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $testData,
            ]);
            $data = $client->getResponse()->getContent(false);
            $data = json_decode($data, true);
            $this->assertArrayHasKey("error", $data);
            $this->assertResponseStatusCodeSame(400);
            $this->assertEquals($errorResponses[$i], $data["error"]);
        }
    }
    public function testCannotCreateUserWithWrongEmailFromat(): void
    {
        $data = [
            "email" => "xdsadadasdgmail.com",
            "password" => "StrongPassword",
            "name" => "jan",
            "surname" =>  "dumas",
            "date_of_birth" =>  "2002-11-20",
            "gender" => "male"
        ];
        $client = self::createClient();
        $client->request("POST", "http://localhost:8000/v1/api/auth/register", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = $client->getResponse()->getContent(false);
        $data = json_decode($data, true);
        $this->assertArrayHasKey("error", $data);
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals("invalid email format", $data["error"]);
    }
    public function testCannotCreateUserWithWrongDateFromat(): void
    {
        $data = [
            "email" => "xdsadadasd@gmail.com",
            "password" => "StrongPassword",
            "name" => "jan",
            "surname" =>  "dumas",
            "date_of_birth" =>  "200211-20",
            "gender" => "male"
        ];
        $client = self::createClient();
        $client->request("POST", "http://localhost:8000/v1/api/auth/register", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = $client->getResponse()->getContent(false);
        $data = json_decode($data, true);
        $this->assertArrayHasKey("error", $data);
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals("invalid date format YYYY-MM-DD required", $data["error"]);
    }
    public function testCannotCreateUserWithWrongGender(): void
    {
        $data = [
            "email" => "xdsadadasd@gmail.com",
            "password" => "StrongPassword",
            "name" => "jan",
            "surname" =>  "dumas",
            "date_of_birth" =>  "2002-11-20",
            "gender" => "nonbinary"
        ];
        $client = self::createClient();
        $client->request("POST", "http://localhost:8000/v1/api/auth/register", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
        $data = $client->getResponse()->getContent(false);
        $data = json_decode($data, true);
        $this->assertArrayHasKey("error", $data);
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals("expected male or female", $data["error"]);
    }
    public function testCanRemoveUser(): void
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
        $headers = $client->getResponse()->getHeaders();
        $client->request("DELETE", "http://localhost:8000/v1/api/auth/account", [
            'headers' => ['Content-Type' => 'application/json', "set-cookie" => $headers["set-cookie"]],
        ]);
        $data = $client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertArrayHasKey("message", $data);
        $this->assertEquals("User has been deleted", $data["message"]);
        $this->assertResponseStatusCodeSame(202);
    }
}
