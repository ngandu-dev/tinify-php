<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Tests;

use Ngandu\Tinify\Client;
use Ngandu\Tinify\Exception\InvalidUrlException;
use Ngandu\Tinify\Source;
use Ngandu\Tinify\Storage\Aws;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ClientTest extends TestCase
{
    public function testFromUrlThrowsWhenUrlIsInvalid(): void
    {
        $client = new Client('token');

        $this->expectException(InvalidUrlException::class);
        $client->fromUrl('not-a-valid-url');
    }

    public function testFromBufferUploadsImageAndReturnsSource(): void
    {
        $calls = [];
        $client = $this->getClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return $this->getResponse('compressed-image-data', [
                'Content-Type: image/png',
                'Image-Width: 200',
                'Image-Height: 100',
                'Location: https://api.tinify.com/output/1',
                'Compression-Count: 3',
            ]);
        });

        $source = $client->fromBuffer('raw-image-data');

        $this->assertCount(1, $calls);
        $this->assertSame('POST', $calls[0]['method']);
        $this->assertStringEndsWith('/shrink', $calls[0]['url']);
        $this->assertSame('raw-image-data', $calls[0]['options']['body']);
        $this->assertInstanceOf(Source::class, $source);
        $this->assertSame('compressed-image-data', $source->toBuffer());
        $this->assertSame('https://api.tinify.com/output/1', $source->getLocation());
        $this->assertSame(3, $source->getCompressionCount());
    }

    public function testToBufferDownloadsSourceWhenLocationIsAvailable(): void
    {
        $calls = [];
        $client = $this->getClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return $this->getResponse('downloaded-image-data', [
                'Content-Type: image/png',
                'Image-Width: 200',
                'Image-Height: 100',
            ]);
        });

        $source = new Source(
            meta: [
                'content-type' => ['image/png'],
                'content-length' => ['10'],
                'image-width' => ['20'],
                'image-height' => ['10'],
                'location' => ['https://api.tinify.com/output/1'],
            ],
            data: 'old-data'
        );

        $this->assertSame('downloaded-image-data', $client->toBuffer($source));
        $this->assertCount(1, $calls);
        $this->assertSame('GET', $calls[0]['method']);
        $this->assertSame('https://api.tinify.com/output/1', $calls[0]['url']);
    }

    public function testToCloudSendsStoreConfiguration(): void
    {
        $calls = [];
        $client = $this->getClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return $this->getResponse('', [
                'Content-Type: application/json',
                'Image-Width: 0',
                'Image-Height: 0',
                'Location: https://api.tinify.com/output/2',
            ]);
        });

        $source = new Source(
            meta: [
                'content-type' => ['image/png'],
                'content-length' => ['10'],
                'image-width' => ['20'],
                'image-height' => ['10'],
                'location' => ['https://api.tinify.com/output/1'],
            ],
            data: 'source-data'
        );

        $storage = new Aws(
            region: 'eu-west-3',
            secret_access_key: 'secret',
            access_key_id: 'key',
            option: [
                'headers' => [
                    'Cache-Control' => 'public',
                ],
            ]
        );

        $output = $client->toCloud($source, 'bucket/images/file.png', $storage);

        $payload = json_decode((string) $calls[0]['options']['body'], true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('store', $payload);
        $this->assertIsArray($payload['store']);

        $this->assertCount(1, $calls);
        $this->assertSame('POST', $calls[0]['method']);
        $this->assertSame('https://api.tinify.com/output/1', $calls[0]['url']);
        $this->assertSame(
            [
                'service' => 'aws',
                'region' => 'eu-west-3',
                'aws_secret_access_key' => 'secret',
                'aws_access_key_id' => 'key',
                'headers' => [
                    'Cache-Control' => 'public',
                ],
                'path' => 'bucket/images/file.png',
            ],
            $payload['store']
        );
        $this->assertInstanceOf(Source::class, $output);
        $this->assertSame('https://api.tinify.com/output/2', $output->getLocation());
    }

    private function getClient(callable|MockResponse $mock): Client
    {
        $client = new Client('token');
        $reflectionClass = new ReflectionClass($client);
        $httpProperty = $reflectionClass->getProperty('http');

        if ($httpProperty->isReadOnly()) {
            /** @var Client $mutableClient */
            $mutableClient = $reflectionClass->newInstanceWithoutConstructor();
            $httpProperty->setValue($mutableClient, new MockHttpClient($mock));

            return $mutableClient;
        }

        $httpProperty->setValue($client, new MockHttpClient($mock));

        return $client;
    }

    /**
     * @param string[] $headers
     */
    private function getResponse(string $content, array $headers): MockResponse
    {
        return new MockResponse($content, [
            'response_headers' => $headers,
        ]);
    }
}
