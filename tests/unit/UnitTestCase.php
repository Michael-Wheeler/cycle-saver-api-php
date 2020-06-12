<?php

namespace CycleSaver\Test;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class UnitTestCase extends TestCase
{
    protected function getResponseBody(ResponseInterface $response): object
    {
        return json_decode((string) $response->getBody());
    }

    protected function assertResponseCodeAndMessage(int $code, string $message, ResponseInterface $response): void
    {
        $responseMessage = $this->getResponseBody($response)->data->message;

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals($message, $responseMessage);
    }
}
