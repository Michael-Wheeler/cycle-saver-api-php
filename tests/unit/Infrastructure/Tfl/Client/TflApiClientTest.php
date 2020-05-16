<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class TflApiClientTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ClientInterface|ObjectProphecy
     */
    private $httpClient;
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;
    /**
     * @var TflContext
     */
    private $context;

    private TflApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->prophesize(TflContext::class);
        $this->httpClient = $this->prophesize(ClientInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->client = new TflApiClient(
            $this->context->reveal(),
            $this->httpClient->reveal(),
            $this->logger->reveal()
        );
    }

    public function test_getPTJourney_should_make_api_request_and_parse_response()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('test.com');
        $this->context->getClientId()->shouldBeCalled()->willReturn('11111');
        $this->context->getClientKey()->shouldBeCalled()->willReturn('22222');

        $this->httpClient->request(
            'GET',
            'test.com/Journey/JourneyResults/51.501,-0.123/to/51.478873,-0.026715',
            [
                'query' =>
                    [
                        'nationalSearch' => true,
                        'date' => '20200518',
                        'time' => '0900',
                        'app_id' => '11111',
                        'app_key' => '22222'
                    ]
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                json_encode($this->pTJourneyBody())
            )
        );

        $pTJourney = $this->client->getPTJourney(
            [51.501, -0.123],
            [51.478873, -0.026715],
        );

        $this->assertEquals(2.80, $pTJourney->getCost());
        $this->assertEquals(3360, $pTJourney->getDuration()->s);
    }

    private function pTJourneyBody(): object
    {
        return (object) [
            'journeys' =>
                [
                    [
                        'duration' => 56,
                        'fare' =>
                            [
                                'totalCost' => 280,
                            ],
                    ],
                    [
                        'duration' => 49,
                        'fare' =>
                            [
                                'totalCost' => 430,
                            ],
                    ],
                ],
        ];
    }
}
