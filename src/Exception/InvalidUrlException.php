<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Exception;

use InvalidArgumentException;

/**
 * @package Ngandu\Tinify\Exception
 * @author bernard-ng <bernard@ngandu.dev>
 */
class InvalidUrlException extends InvalidArgumentException
{
    public function __construct(string $url)
    {
        parent::__construct(
            message: sprintf('%s is not a valid URL', $url)
        );
    }
}
