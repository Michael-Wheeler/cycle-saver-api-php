<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;
use DateTimeImmutable;
use DateTimeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
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
     * @var DateTimeInterface|ObjectProphecy
     */
    private $dateTime;
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
        $this->dateTime = $this->prophesize(DateTimeImmutable::class);

        $this->client = new TflApiClient(
            $this->context->reveal(),
            $this->httpClient->reveal(),
            $this->logger->reveal(),
            $this->dateTime->reveal()
        );

        $this->dateTime->getTimestamp()->shouldBeCalled()->willReturn(1589673600);
    }

    public function test_getPTJourney_should_make_api_request_and_parse_response()
    {
        $this->tflContext();

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

        $pTJourney = $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);

        $this->assertEquals(2.80, $pTJourney->getCost());
        $this->assertEquals(3360, $pTJourney->getDuration()->s);
    }

    public function test_getPTJourney_should_throw_TflClientException_if_API_returns_error()
    {
        $this->tflContext();

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
        )->shouldBeCalled()->willThrow(new \Exception('api error'));

        $this->logger->error('TFL client error when calling TFL API: api error')->shouldBeCalled();

        $this->expectException(TflClientException::class);
        $this->expectExceptionMessage('TFL client error when calling TFL API: api error');

        $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_getPTJourney_should_throw_TflClientException_if_response_not_JSON()
    {
        $this->tflContext();

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
                null
            )
        );
        $this->logger->error(
            'TFL client was unable to parse activities response: Body is not in valid JSON format'
        )->shouldBeCalled();

        $this->expectException(TflClientException::class);
        $this->expectExceptionMessage(
            'TFL client was unable to parse activities response: Body is not in valid JSON format'
        );

        $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_getPTJourney_should_throw_TflClientException_if_response_is_missing_journeys()
    {
        $this->tflContext();

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
                json_encode((object) ['invalid' => 'invalid'])
            )
        );
        $this->logger->error(
            'TFL client was unable to parse activities response: Response does not contain an array of journeys'
        )->shouldBeCalled();

        $this->expectException(TflClientException::class);
        $this->expectExceptionMessage(
            'TFL client was unable to parse activities response: Response does not contain an array of journeys'
        );

        $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_getPTJourney_should_throw_TflClientException_if_all_journeys_are_missing_information()
    {
        $this->tflContext();

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
                json_encode((object) [
                    'journeys' =>
                        [
                            [
                                'duration' => 56,
                            ],
                            [
                                'duration' => 49,
                            ],
                        ],
                ])
            )
        );

        $this->logger->debug(
            'TFL public transport journey is missing required field'
        )->shouldBeCalled();

        $this->logger->error(
            'TFL client was unable to parse activities response: ' .
            'Response does not contain any journeys with a fare and duration'
        )->shouldBeCalled();

        $this->expectException(TflClientException::class);
        $this->expectExceptionMessage(
            'TFL client was unable to parse activities response: ' .
            'Response does not contain any journeys with a fare and duration'
        );

        $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_getPTJourney_should_throw_TflClientException_if_invalid_duration_given()
    {
        $this->tflContext();

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
                json_encode((object) [
                    'journeys' =>
                        [
                            [
                                'duration' => 'invalid',
                                'fare' =>
                                    [
                                        'totalCost' => 280,
                                    ],
                            ],
                        ],
                ])
            )
        );

        $this->logger->error(
            'TFL client was unable to parse activities response: Invalid duration format'
        )->shouldBeCalled();

        $this->expectException(TflClientException::class);
        $this->expectExceptionMessage(
            'TFL client was unable to parse activities response: Invalid duration format'
        );

        $this->client->getPTJourney([51.501, -0.123], [51.478873, -0.026715]);
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

    function date($format, $timestamp = 'time()')
    {
        return '20200518';
    }

    private function tflContext(): void
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('test.com');
        $this->context->getClientId()->shouldBeCalled()->willReturn('11111');
        $this->context->getClientKey()->shouldBeCalled()->willReturn('22222');
    }
}
