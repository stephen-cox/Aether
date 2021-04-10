<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTests extends WebTestCase
{
    public function testRoot()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals('Hello', $content->data->message);
        $this->assertEquals('v1', $content->data->api_version);
    }
}
