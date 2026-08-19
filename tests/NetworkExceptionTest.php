<?php

declare(strict_types=1);

namespace Ngandu\Tinify\Tests;

use Ngandu\Tinify\Exception\AccountException;
use Ngandu\Tinify\Exception\ClientException;
use Ngandu\Tinify\Exception\NetworkException;
use Ngandu\Tinify\Exception\ServerException;
use PHPUnit\Framework\TestCase;

final class NetworkExceptionTest extends TestCase
{
    public function testCreateReturnsAccountExceptionForUnauthorizedAndTooManyRequests(): void
    {
        $unauthorized = NetworkException::create('Unauthorized', 'Unauthorized', 401);
        $tooManyRequests = NetworkException::create('Too many requests', 'TooManyRequests', 429);

        $this->assertInstanceOf(AccountException::class, $unauthorized);
        $this->assertInstanceOf(AccountException::class, $tooManyRequests);
    }

    public function testCreateReturnsClientExceptionFor4xxErrors(): void
    {
        $exception = NetworkException::create('Bad request', 'BadRequest', 400);

        $this->assertInstanceOf(ClientException::class, $exception);
        $this->assertSame('Bad request (HTTP 400/BadRequest)', $exception->getMessage());
    }

    public function testCreateReturnsServerExceptionFor5xxErrors(): void
    {
        $exception = NetworkException::create('Internal server error', 'InternalServerError', 500);

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame('Internal server error (HTTP 500/InternalServerError)', $exception->getMessage());
    }

    public function testCreateReturnsBaseExceptionForUnexpectedStatusAndFallbackMessage(): void
    {
        $exception = NetworkException::create('', 'UnknownError', 200);

        $this->assertInstanceOf(NetworkException::class, $exception);
        $this->assertSame('No message was provided (HTTP 200/UnknownError)', $exception->getMessage());
    }
}
