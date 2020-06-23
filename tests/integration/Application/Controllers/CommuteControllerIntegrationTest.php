<?php

namespace CycleSaver\Application\Controllers;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\IntegrationTestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Ramsey\Uuid\Uuid;

class CommuteControllerIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_getByUserId_should_retrieve_commutes_from_commute_repository_and_delete_commutes()
    {
        $userId = Uuid::fromString('0ace673d-c854-45fc-a421-0e685618cb67');

        $this->commute(['user_id' => $userId, 'id' => Uuid::fromString('f98ff19e-d348-4162-a4a0-fac72b32459c')]);
        $this->commute(['user_id' => $userId, 'id' => Uuid::fromString('74637d8b-90a5-430e-9984-9f430ac7741a')]);

        $request = new ServerRequest(
            'GET',
            'localhost:/user/0ace673d-c854-45fc-a421-0e685618cb67/commute',
        );

        $response = $this->makeRequest($request);

        $expectedBody = (object) [
            'status' => 'SUCCESS',
            'data' => (object) [
                'commutes' => [
                    (object) [
                        'id' => 'f98ff19e-d348-4162-a4a0-fac72b32459c',
                        'user_id' => '0ace673d-c854-45fc-a421-0e685618cb67',
                        'start_date' => 1594512000,
                        'start_latlng' => null,
                        'end_latlng' => null,
                        'activity_duration' => 1000,
                        'public_transport_duration' => 3600,
                        'public_transport_cost' => 25000
                    ],
                    (object) [
                        'id' => '74637d8b-90a5-430e-9984-9f430ac7741a',
                        'user_id' => '0ace673d-c854-45fc-a421-0e685618cb67',
                        'start_date' => 1594512000,
                        'start_latlng' => null,
                        'end_latlng' => null,
                        'activity_duration' => 1000,
                        'public_transport_duration' => 3600,
                        'public_transport_cost' => 25000
                    ]
                ]
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedBody, json_decode((string) $response->getBody()));

        $response = $this->makeRequest($request);

        $this->assertEmpty(json_decode((string) $response->getBody())->data->commutes);
    }

    public function test_getByUserId_should_return_404_if_invalid_user_id_given()
    {
        $request = new ServerRequest(
            'GET',
            'localhost:/user/invalid/commute',
        );

        $response = $this->makeRequest($request);

        $expectedBody = (object) [
            'status' => 'ERROR',
            'data' => (object) [
                'message' => "Invalid user UUID provided: 'invalid'."
            ]
        ];

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals($expectedBody, $this->getResponseBody($response));
    }

    private function commute(array $attributes = []): Commute
    {
        $repo = $this->container->get(CommuteRepositoryInterface::class);

        $commute = (new Commute($attributes['id'] ?? Uuid::uuid4()))
            ->setUserId($attributes['user_id'] ?? Uuid::uuid4())
            ->setStartLatLong($attributes['start_lat_lng'] ?? [])
            ->setEndLatLong($attributes['end_lat_lng'] ?? [])
            ->setPTDuration($attributes['pt_duration'] ?? new \DateInterval('PT3600S'))
            ->setActivityDuration($attributes['activity_duration'] ?? new \DateInterval('PT1000S'))
            ->setPTCost($attributes['pt_cost'] ?? 250)
            ->setStartDate($attributes['start_date'] ?? new \DateTimeImmutable('2020-07-12'));

        $repo->saveCommute($commute);

        return $commute;
    }
}
