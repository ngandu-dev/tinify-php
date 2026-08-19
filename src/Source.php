<?php

declare(strict_types=1);

namespace Ngandu\Tinify;

/**
 * @package Ngandu\Tinify
 * @author bernard-ng <bernard@ngandu.dev>
 */
class Source
{
    public function __construct(
        private array $meta,
        private readonly mixed $data
    ) {
        $this->meta = array_combine(
            keys: array_keys($meta),
            values: array_column($meta, column_key: 0)
        );
    }

    public function toBuffer(): mixed
    {
        return $this->data;
    }

    public function toFile(string $path): int|bool
    {
        return file_put_contents($path, $this->data);
    }

    public function getSize(): int
    {
        return intval($this->meta['content-length']);
    }

    public function getMediaType(): string
    {
        return $this->meta['content-type'];
    }

    public function getWidth(): int
    {
        return intval($this->meta['image-width']);
    }

    public function getHeight(): int
    {
        return intval($this->meta['image-height']);
    }

    public function getLocation(): ?string
    {
        return $this->meta['location'] ?? null;
    }

    public function getCompressionCount(): int
    {
        return intval($this->meta['compression-count'] ?? 0);
    }
}
