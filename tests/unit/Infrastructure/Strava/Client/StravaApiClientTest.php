<?php

namespace CycleSaver\Infrastructure\Strava\Client;

use CycleSaver\Infrastructure\Strava\Exception\StravaClientException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class StravaApiClientTest extends TestCase
{
    use ProphecyTrait;

    private StravaApiClient $client;
    /**
     * @var StravaContext|ObjectProphecy
     */
    private $context;
    /**
     * @var ClientInterface|ObjectProphecy
     */
    private $httpClient;
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;
    /**
     * @var StravaApiAuthClient|ObjectProphecy
     */
    private $authClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->prophesize(StravaContext::class);
        $this->httpClient = $this->prophesize(ClientInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->authClient = $this->prophesize(StravaApiAuthClient::class);

        $this->client = new StravaApiClient(
            $this->context->reveal(),
            $this->httpClient->reveal(),
            $this->authClient->reveal(),
            $this->logger->reveal()
        );
    }

    public function test_getActivities_should_call_strava_api_and_return_parsed_activities()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(
            'GET',
            'https://www.strava.com/api/v3/activities',
            [
                'query' => ['page' => 1],
                'headers' => ['Authorization' => 'Bearer 22222']
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                $this->activityResponse()
            )
        );

        $this->httpClient->request(
            'GET',
            'https://www.strava.com/api/v3/activities',
            [
                'query' => ['page' => 2],
                'headers' => ['Authorization' => 'Bearer 22222']
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                json_encode([])
            )
        );

        $activities = $this->client->getActivities('22222');

        $this->assertCount(1, $activities);
        $this->assertEquals([54.97, -1.59], $activities[0]->getStartLatLong());
        $this->assertEquals([54.97, -2.59], $activities[0]->getEndLatLong());
        $this->assertEquals(new \DateTimeImmutable('2020-03-27T13:18:03'), $activities[0]->getStartDate());
        $this->assertEquals(7152, $activities[0]->getDuration()->s);
        $this->assertEquals(36264, $activities[0]->getDistance());
    }

    public function test_getActivities_should_call_strava_api_and_return_parsed_activities_from_multiple_pages()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $commuteBody = json_encode(
            [
                (object) [
                    "distance" => 36264.4,
                    "elapsed_time" => 7152,
                    "start_date" => "2020-03-27T13:18:03Z",
                    "start_date_local" => "2020-03-27T13:18:03Z",
                    "start_latlng" => [
                        54.97,
                        -1.59
                    ],
                    "end_latlng" => [
                        54.97,
                        -2.59
                    ],
                    "commute" => true,
                ],
            ]
        );

        $this->httpClient->request(
            'GET',
            'https://www.strava.com/api/v3/activities',
            [
                'query' => ['page' => 1],
                'headers' => ['Authorization' => 'Bearer 22222']
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                $commuteBody
            )
        );

        $this->httpClient->request(
            'GET',
            'https://www.strava.com/api/v3/activities',
            [
                'query' => ['page' => 2],
                'headers' => ['Authorization' => 'Bearer 22222']
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                $commuteBody
            )
        );

        $this->httpClient->request(
            'GET',
            'https://www.strava.com/api/v3/activities',
            [
                'query' => ['page' => 3],
                'headers' => ['Authorization' => 'Bearer 22222']
            ]
        )->shouldBeCalled()->willReturn(
            new Response(
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                json_encode([])
            )
        );

        $activities = $this->client->getActivities('22222');

        $this->assertCount(2, $activities);
    }

    public function test_getActivities_should_throw_StravaClientException_if_strava_returns_invalid_body()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    null
                )
            );

        $this->logger->error('Strava client was unable to parse activities response: ' .
            'Response body is not in an array format')
            ->shouldBeCalled();

        $this->expectException(StravaClientException::class);
        $this->expectExceptionMessage('Strava client was unable to parse activities response: ' .
            'Response body is not in an array format');

        $this->client->getActivities('22222');
    }

    public function test_getActivities_should_skip_invalid_activity_and_log_error()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode([(object) ['invalid' => 'invalid']])
                )
            );

        $this->logger->error('Unable to parse Strava activity: Strava activity is missing required field')
            ->shouldBeCalled();

        $activities = $this->client->getActivities('22222');

        $this->assertEmpty($activities);
    }

    public function test_getActivities_should_skip_activity_with_invalid_start_date_and_log_error()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode(
                        [
                            (object) [
                                "distance" => 36264.4,
                                "elapsed_time" => 7152,
                                "start_date_local" => "invalid",
                                "start_latlng" => [
                                    54.97,
                                    -1.59
                                ],
                                "end_latlng" => [
                                    54.97,
                                    -2.59
                                ],
                                "commute" => true,
                            ],
                        ]
                    )
                )
            );

        $this->logger->error('Unable to parse Strava activity: Invalid start date format')
            ->shouldBeCalled();

        $activities = $this->client->getActivities('22222');

        $this->assertEmpty($activities);
    }


    public function test_getActivities_should_skip_activity_with_same_start_and_end_location()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode(
                        [
                            (object) [
                                "distance" => 36264.4,
                                "elapsed_time" => 7152,
                                "start_date_local" => "invalid",
                                "start_latlng" => [
                                    54.97,
                                    -1.59
                                ],
                                "end_latlng" => [
                                    54.97,
                                    -1.59
                                ],
                                "commute" => true,
                            ],
                        ]
                    )
                )
            );

        $this->logger->debug('Cannot use Strava activities that start and end at the same location')
            ->shouldBeCalled();

        $activities = $this->client->getActivities('22222');

        $this->assertEmpty($activities);
    }

    public function test_getActivities_should_skip_activity_with_invalid_duration_and_log_error()
    {
        $this->context->getBaseUri()->shouldBeCalled()->willReturn('https://www.strava.com/api/v3/');

        $this->httpClient->request(Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json; charset=utf-8'],
                    json_encode(
                        [
                            (object) [
                                "distance" => 36264.4,
                                "elapsed_time" => 'invalid',
                                "start_date_local" => "2020-03-27T13:18:03Z",
                                "start_latlng" => [
                                    54.97,
                                    -1.59
                                ],
                                "end_latlng" => [
                                    54.97,
                                    -2.59
                                ],
                                "commute" => true,
                            ],
                        ]
                    )
                )
            );

        $this->logger->error('Unable to parse Strava activity: Invalid duration format')
            ->shouldBeCalled();

        $activities = $this->client->getActivities('22222');

        $this->assertEmpty($activities);
    }

    private function activityResponse()
    {
        return json_encode(
            [
                (object) [
                    "resource_state" => 2,
                    "athlete" => (object) [
                        "id" => 1118050,
                        "resource_state" => 1
                    ],
                    "name" => "Afternoon Ride",
                    "distance" => 36264.4,
                    "moving_time" => 6270,
                    "elapsed_time" => 7152,
                    "total_elevation_gain" => 231.7,
                    "type" => "Ride",
                    "workout_type" => null,
                    "id" => 3226989256,
                    "external_id" => "2020-03-27-13-18-03.fit",
                    "upload_id" => 3449847850,
                    "start_date" => "2020-03-27T13:18:03Z",
                    "start_date_local" => "2020-03-27T13:18:03Z",
                    "timezone" => "(GMT+00 =>00) Europe/London",
                    "utc_offset" => 0.0,
                    "start_latlng" => [
                        54.97,
                        -1.59
                    ],
                    "end_latlng" => [
                        54.97,
                        -2.59
                    ],
                    "location_city" => null,
                    "location_state" => null,
                    "location_country" => "United Kingdom",
                    "start_latitude" => 54.97,
                    "start_longitude" => -1.59,
                    "achievement_count" => 3,
                    "kudos_count" => 1,
                    "comment_count" => 0,
                    "athlete_count" => 1,
                    "photo_count" => 0,
                    "map" => (object) [
                        "id" => "a3226989256",
                        "resource_state" => 2
                    ],
                    "trainer" => false,
                    "commute" => true,
                    "manual" => false,
                    "private" => false,
                    "visibility" => "everyone",
                    "flagged" => false,
                    "gear_id" => "b1057641",
                    "from_accepted_tag" => false,
                    "upload_id_str" => "3449847850",
                    "average_speed" => 5.784,
                    "max_speed" => 10.4,
                    "average_watts" => 78.7,
                    "kilojoules" => 493.3,
                    "device_watts" => false,
                    "has_heartrate" => false,
                    "heartrate_opt_out" => false,
                    "display_hide_heartrate_option" => false,
                    "elev_high" => 41.6,
                    "elev_low" => 1.5,
                    "pr_count" => 2,
                    "total_photo_count" => 0,
                    "has_kudoed" => false
                ],
                (object) [
                    "resource_state" => 2,
                    "athlete" => (object) [
                        "id" => 1118050,
                        "resource_state" => 1
                    ],
                    "name" => "Afternoon Ride",
                    "distance" => 15900.1,
                    "moving_time" => 2727,
                    "elapsed_time" => 2868,
                    "total_elevation_gain" => 61.6,
                    "type" => "Ride",
                    "workout_type" => null,
                    "id" => 3216568782,
                    "external_id" => "2020-03-25-13-33-08.fit",
                    "upload_id" => 3438783992,
                    "start_date" => "2020-03-25T13:33:08Z",
                    "start_date_local" => "2020-03-25T13:33:08Z",
                    "timezone" => "(GMT+00 =>00) Europe/London",
                    "utc_offset" => 0.0,
                    "start_latlng" => [
                        54.97,
                        -1.59
                    ],
                    "end_latlng" => [
                        54.97,
                        -1.59
                    ],
                    "location_city" => null,
                    "location_state" => null,
                    "location_country" => "United Kingdom",
                    "start_latitude" => 54.97,
                    "start_longitude" => -1.59,
                    "achievement_count" => 0,
                    "kudos_count" => 2,
                    "comment_count" => 0,
                    "athlete_count" => 1,
                    "photo_count" => 0,
                    "map" => (object) [
                        "id" => "a3216568782",
                        "resource_state" => 2
                    ],
                    "trainer" => false,
                    "commute" => false,
                    "manual" => false,
                    "private" => false,
                    "visibility" => "everyone",
                    "flagged" => false,
                    "gear_id" => "b1057641",
                    "from_accepted_tag" => false,
                    "upload_id_str" => "3438783992",
                    "average_speed" => 5.831,
                    "max_speed" => 11.6,
                    "average_watts" => 76.7,
                    "kilojoules" => 209.3,
                    "device_watts" => false,
                    "has_heartrate" => false,
                    "heartrate_opt_out" => false,
                    "display_hide_heartrate_option" => false,
                    "elev_high" => 22.6,
                    "elev_low" => 0.5,
                    "pr_count" => 0,
                    "total_photo_count" => 0,
                    "has_kudoed" => false
                ],
            ]
        );
    }
}
