<?php

namespace CycleSaver;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class UnitTestCase extends TestCase
{
    protected function getResponseBody(ResponseInterface $response): object
    {
        return json_decode((string) $response->getBody());
    }
}
