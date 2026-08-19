<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Storage;

/**
 * @package Ngandu\Tinify\Storage
 * @author bernard-ng <bernard@ngandu.dev>
 */
interface StorageInterface
{
    public function getConfiguration(): array;
}
