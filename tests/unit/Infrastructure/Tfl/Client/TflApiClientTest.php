<?php

namespace CycleSaver\Infrastructure\Tfl\Client;

use CycleSaver\Infrastructure\Tfl\Exception\TflClientException;
use CycleSaver\UnitTestCase;
use DateTimeImmutable;
use DateTimeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class TflApiClientTest extends UnitTestCase
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
        $this->dateTime->getTimestamp()->willReturn(1589673600);

        $this->client = new TflApiClient(
            $this->context->reveal(),
            $this->httpClient->reveal(),
            $this->logger->reveal(),
            $this->dateTime->reveal()
        );
    }

    public function test_createPTJourneys_should_make_api_request_and_return_single_journey()
    {
        $this->tflContext();

        $coordinates = [
            [
                'start_latlng' => [22.22, 33.33],
                'end_latlng' => [44.44, 55.55],
            ]
        ];

        $expectedRequest = Argument::that(function (Request $request) {
            $this->assertEquals('GET', $request->getMethod());

            $this->assertEquals(
                'test.com/Journey/JourneyResults/22.22,33.33/to/44.44,55.55',
                (string) $request->getUri()->getPath()
            );

            $this->assertEquals(
                'nationalSearch=true&date=20200518&time=0900&0%5Bapp_id%5D=11111&0%5Bapp_key%5D=22222',
                $request->getUri()->getQuery()
            );

            return true;
        });

        $this->httpClient->sendAsync($expectedRequest, [])->willReturn(new FulfilledPromise(new Response(
            200,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($this->pTJourneyBody())
        )));

        $journeys = $this->client->createPTJourneys($coordinates);

        $this->assertCount(1, $journeys);
        $this->assertEquals(3360, $journeys[0]->getDuration()->s);
        $this->assertEquals(280, $journeys[0]->getCost());
    }

    public function test_createPTJourneys_should_make_multiple_api_requests_and_parse_responses()
    {
        $this->tflContext();

        $coordinates = [];
        for ($i = 0; $i < 11; $i++) {
            $coordinates[] = [
                'start_latlng' => [22.22, 33.33],
                'end_latlng' => [44.44, 55.55],
            ];
        }

        $this->httpClient->sendAsync(Argument::any(), Argument::any())
            ->willReturn(
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response(),
                $this->response()
            );

        $journeys = $this->client->createPTJourneys($coordinates);

        $this->assertCount(11, $journeys);

        foreach ($journeys as $journey) {
            $this->assertEquals(3360, $journey->getDuration()->s);
            $this->assertEquals(280, $journey->getCost());
        }
    }

    public function test_createPTJourneys_should_log_error_and_return_false_if_cannot_create_journey()
    {
        $this->tflContext();

        $coordinates = [
            [
                'start_latlng' => [55.55, 55.55],
                'end_latlng' => [44.44, 55.55],
            ],
            [
                'start_latlng' => [22.22, 33.33],
                'end_latlng' => [44.44, 55.55],
            ]
        ];

        $this->httpClient->sendAsync(Argument::any(), [])->willReturn(
            new RejectedPromise(new \Exception('Error message')),
            new FulfilledPromise(new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                json_encode($this->pTJourneyBody())
            ))
        );

        $this->logger->error("Error when calling TFL API: Error message")->shouldBeCalled();

        $journeys = $this->client->createPTJourneys($coordinates);

        $this->assertFalse($journeys[0]);
        $this->assertEquals(3360, $journeys[1]->getDuration()->s);
        $this->assertEquals(280, $journeys[1]->getCost());
    }

    public function test_createPTJourney_should_make_api_request_and_parse_response()
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

        $pTJourney = $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);

        $this->assertEquals(280, $pTJourney->getCost());
        $this->assertEquals(3360, $pTJourney->getDuration()->s);
    }

    public function test_createPTJourney_should_throw_TflClientException_if_API_returns_error()
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

        $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_createPTJourney_should_throw_TflClientException_if_response_not_JSON()
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

        $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_createPTJourney_should_throw_TflClientException_if_response_is_missing_journeys()
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

        $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_createPTJourney_should_throw_TflClientException_if_all_journeys_are_missing_information()
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

        $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);
    }

    public function test_createPTJourney_should_throw_TflClientException_if_invalid_duration_given()
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

        $this->client->createPTJourney([51.501, -0.123], [51.478873, -0.026715]);
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

    private function tflContext(): void
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('test.com');
        $this->context->getClientId()->shouldBeCalled()->willReturn('11111');
        $this->context->getClientKey()->shouldBeCalled()->willReturn('22222');
    }

    private function response(): FulfilledPromise
    {
        return new FulfilledPromise(new Response(
            200,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($this->pTJourneyBody())
        ));
    }
}
