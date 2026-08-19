<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Storage;

/**
 * @package Ngandu\Tinify\Storage
 * @author bernard-ng <bernard@ngandu.dev>
 */
class Aws implements StorageInterface
{
    public function __construct(
        private readonly string $region,
        private readonly string $secret_access_key,
        private readonly string $access_key_id,
        private readonly array $option = []
    ) {
    }

    public function getConfiguration(): array
    {
        return array_merge([
            'service' => 'aws',
            'region' => $this->region,
            'aws_secret_access_key' => $this->secret_access_key,
            'aws_access_key_id' => $this->access_key_id,
        ], $this->option);
    }
}
