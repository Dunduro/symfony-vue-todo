<?php
declare(strict_types=1);


namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Faker\Factory;

class AuthControllerTest extends WebTestCase
{
    public function testFaultyEmail(): void
    {
        $data = [
            'email' => 'test@sasda',
            'password' => 'Abcde123'
        ];
        $this->register($data, Response::HTTP_BAD_REQUEST);
    }

    public function testFaultyPassword(): void
    {
        $data = [
            'email' => 'test@sasda.com',
            'password' => 'Abcde'
        ];
        $this->register($data, Response::HTTP_BAD_REQUEST);
    }

    public function testSuccesfullRegistration(): void
    {
        $data = [
            'email' => 'test@sasda.com',
            'password' => 'Abcde123'
        ];
        $this->register($data, Response::HTTP_OK);
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @return KernelBrowser
     */
    protected function register(array $data, int $statusCode): KernelBrowser
    {
        $client = static::createClient();
        $client->xmlHttpRequest('POST', '/api/auth/register', [], [], [], json_encode($data));
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
        return $client;
    }
}
