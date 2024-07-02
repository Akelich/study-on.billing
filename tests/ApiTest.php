<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [new \App\DataFixtures\AppFixtures($this->getContainer()->get(UserPasswordHasherInterface::class))];
    }

    public function testSuccesssRegister()
    { 
        $client = $this->getClient(); 
        $client->request(
            'POST', 
            '/api/v1/register',
            [],
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([
                'username'=> 'test@test.test', 
                'password' => 'password'
            ])
        ); 
        $arrayedContent = (array)json_decode($client->getResponse()->getContent()); 
        $this->assertResponseCode(201); 
        $this->assertArrayHasKey('token', $arrayedContent); 
    }



    public function testSuccessAuth() 
    { 
        $client = $this->createTestClient(); 
        $client->request( 
            'POST', 
            '/api/v1/auth', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user@mail.ru', 
                'password' => 'password' 
            ]) 
        ); 
        $content = json_decode($client->getResponse()->getContent(), true); 
        $this->assertResponseCode(200); 
        $this->assertTrue(array_key_exists('token', $content)); 
    }

    
    public function testFailAuth() 
    { 
        $client = $this->createTestClient(); 

        // несуществующий логин 
        $client->request( 
            'POST', 
            '/api/v1/auth', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user@email.rus', 
                'password' => 'user@email.ru' 
            ]) 
        ); 
        $content = json_decode($client->getResponse()->getContent(), true); 
        $this->assertResponseCode(401); 
        $this->assertEquals('Invalid credentials.', $content['message']); 
        
        // неверный пароль 
        $client->request( 
            'POST', 
            '/api/v1/auth', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user@email.ru', 
                'password' => 'user@email.rururu' 
            ]) 
        ); 
        $content = json_decode($client->getResponse()->getContent(), true); 
        $this->assertResponseCode(401); 
        $this->assertEquals('Invalid credentials.', $content['message']); 
    }

    public function testFailRegisterEmail() 
    { 
        $client = $this->createTestClient(); 
  
        $client->request( 
            'POST', 
            '/api/v1/register', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user@mail.ru', 
                'password' => 'password' 
            ]) 
        ); 
        $this->assertResponseCode(409); 
        
        $client->request( 
            'POST', 
            '/api/v1/register', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => '', 
                'password' => 'user123@mail.ru' 
            ]) 
        );  
        $this->assertResponseCode(400);  
    }

    public function testFailRegisterPassword() 
    {
        $client = $this->createTestClient(); 
        // пустое поле password 
        $client->request( 
            'POST', 
            '/api/v1/register', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user123@mail.ru', 
            ]) 
        ); 
        $this->assertResponseCode(400); 
         
        // пароль короче 6 символов 
        $client->request( 
            'POST', 
            '/api/v1/register', 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([ 
                'username' => 'user123@mail.ru', 
                'password' => '123' 
            ]) 
        ); 
        $this->assertResponseCode(400); 
    }

}    