<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aether\About\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test filesystem API.
 */
class ApiTest extends WebTestCase
{

    /**
     * Test API about response.
     */
    public function testAbout()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/v1/about',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();

        // Check response.
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($response->getContent());

        // Check API data.
        $this->assertEquals('success', $content->status);
        $this->assertEquals('Hello', $content->data->message);
        $this->assertEquals('v1', $content->data->api_version);
    }
}
