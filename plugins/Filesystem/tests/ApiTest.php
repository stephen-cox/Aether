<?php

namespace Aether\Filesystem\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{

    protected function setUp(): void
    {
        if ($_ENV['AETHER_FS_ROOT']) {
            $rootFs = $_ENV['AETHER_FS_ROOT'];
            if (file_exists($rootFs.'/copy.txt')) {
                unlink($rootFs.'/copy.txt');
            }
            if (file_exists($rootFs.'/test.txt')) {
                unlink($rootFs.'/test.txt');
            }
            if (file_exists($rootFs.'/test/test.txt')) {
                unlink($rootFs.'/test/test.txt');
            }
            if (file_exists($rootFs.'/test')) {
                rmdir($rootFs.'/test');
            }
        }
    }

    public function testFilesystemStatus()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/v1/fs',
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

    public function testFilesystemFunctionality()
    {

        $client = static::createClient();

        // Check directory listing.
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEmpty($content->data->listing);

        // Check file read for non-existent path.
        $notFound = '/does_not_exist';
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode($notFound).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('fail', $content->status);
        $this->assertEquals($notFound, $content->data->path);
        $this->assertEquals('', $content->data->content);
        $this->assertEquals('File not found', $content->data->message);

        // Check file creation.
        $data = 'Test file';
        $filename = '/test.txt';
        $client->request(
            'PUT',
            '/api/v1/fs/'.base64_encode($filename).'/file',
            ['content' => $data],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($filename, $content->data->path);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertCount(1, $content->data->listing);
        $this->assertEquals(basename($filename), $content->data->listing[0]->path);

        // Check unable to create file with same name.
        $client->request(
            'PUT',
            '/api/v1/fs/'.base64_encode($filename).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $data,
        );
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('fail', $content->status);

        // Check file read.
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode($filename).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($filename, $content->data->path);
        $this->assertEquals($data, $content->data->content);

        // Check file updating.
        $update = 'Updated file';
        $client->request(
            'PATCH',
            '/api/v1/fs/'.base64_encode($filename).'/file',
            ['content' => $update],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($filename, $content->data->path);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode($filename).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($filename, $content->data->path);
        $this->assertEquals($update, $content->data->content);

        // Check directory creation.
        $directory = '/test';
        $client->request(
            'PUT',
            '/api/v1/fs/'.base64_encode($directory).'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertCount(2, $content->data->listing);
        foreach ($content->data->listing as $item) {
            if ($item->type == 'dir') {
                $this->assertEquals(basename($directory), $item->path);
            }
        }

        // Check file move.
        $moveTo = '/test/test.txt';
        $client->request(
            'POST',
            '/api/v1/fs/'.base64_encode($filename).'/move',
            ['destination' => base64_encode($moveTo)],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($filename, $content->data->source);
        $this->assertEquals($moveTo, $content->data->destination);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/test').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertCount(1, $content->data->listing);
        $this->assertEquals($moveTo, '/'.$content->data->listing[0]->path);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertCount(1, $content->data->listing);
        $this->assertEquals(basename($directory), $content->data->listing[0]->path);

        // Check file copy.
        $copy = '/copy.txt';
        $client->request(
            'POST',
            '/api/v1/fs/'.base64_encode($moveTo).'/copy',
            ['destination' => base64_encode($copy)],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($moveTo, $content->data->source);
        $this->assertEquals($copy, $content->data->destination);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode($copy).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($copy, $content->data->path);
        $this->assertEquals($update, $content->data->content);

        // Check file delete.
        $client->request(
            'DELETE',
            '/api/v1/fs/'.base64_encode($copy).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($copy, $content->data->path);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode($copy).'/file',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('fail', $content->status);

        // Check directory delete.
        $client->request(
            'DELETE',
            '/api/v1/fs/'.base64_encode($directory).'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEquals($directory, $content->data->path);
        $client->request(
            'GET',
            '/api/v1/fs/'.base64_encode('/').'/dir',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals('success', $content->status);
        $this->assertEmpty($content->data->listing);
    }
}
