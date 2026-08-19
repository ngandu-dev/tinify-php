<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Tests;

use Ngandu\Tinify\Source;
use PHPUnit\Framework\TestCase;

final class SourceTest extends TestCase
{
    public function testAccessorsReturnExpectedValues(): void
    {
        $source = new Source(
            meta: [
                'content-type' => ['image/png'],
                'content-length' => ['120'],
                'image-width' => ['300'],
                'image-height' => ['200'],
                'location' => ['https://api.tinify.com/output/1'],
                'compression-count' => ['5'],
            ],
            data: 'image-data'
        );

        $this->assertSame('image-data', $source->toBuffer());
        $this->assertSame(120, $source->getSize());
        $this->assertSame('image/png', $source->getMediaType());
        $this->assertSame(300, $source->getWidth());
        $this->assertSame(200, $source->getHeight());
        $this->assertSame('https://api.tinify.com/output/1', $source->getLocation());
        $this->assertSame(5, $source->getCompressionCount());
    }

    public function testGetCompressionCountReturnsZeroWhenHeaderIsMissing(): void
    {
        $source = new Source(
            meta: [
                'content-type' => ['image/png'],
                'content-length' => ['1'],
                'image-width' => ['1'],
                'image-height' => ['1'],
            ],
            data: 'x'
        );

        $this->assertSame(0, $source->getCompressionCount());
    }

    public function testToFileWritesBufferToDisk(): void
    {
        $source = new Source(
            meta: [
                'content-type' => ['image/png'],
                'content-length' => ['4'],
                'image-width' => ['1'],
                'image-height' => ['1'],
            ],
            data: 'data'
        );

        $path = (string) tempnam(sys_get_temp_dir(), 'tinify_test_');

        try {
            $writtenBytes = $source->toFile($path);
            $this->assertSame(4, $writtenBytes);
            $this->assertSame('data', file_get_contents($path));
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
