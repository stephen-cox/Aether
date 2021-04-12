<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the core API.
 */
class ApiTest extends WebTestCase
{
    /**
     * Test API root path.
     */
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
