<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Test\IntegrationTestCase;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class CommuteRepositoryIntegrationTest extends IntegrationTestCase
{
    private CommuteRepository $commuteRepository;
    private Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commuteRepository = new CommuteRepository(
            $this->database,
            $this->container->get(LoggerInterface::class)
        );

        $this->collection = $this->database->selectCollection('commutes');
    }

    public function test_saveCommute_should_add_commute_to_db_and_return_id()
    {
        $commute = (new Commute())
            ->setId(Uuid::fromString('1562999c-ee25-4a4a-bb51-7f4e238df180'))
            ->setUserId(Uuid::fromString('02a45c1c-8c4e-4af1-bb3a-51af14f85a42'))
            ->setStartDate(new \DateTimeImmutable('2020-05-17'))
            ->setStartLatLong([51.525640, -0.087604])
            ->setEndLatLong([51.478873, -0.026715])
            ->setActivityDuration(new \DateInterval('PT1000S'))
            ->setPTDuration(new \DateInterval('PT2000S'))
            ->setPTCost(4.70);

        $fetchedId = $this->commuteRepository->saveCommute($commute);

        $this->assertEquals('1562999c-ee25-4a4a-bb51-7f4e238df180', (string) $fetchedId);

        $cursor = $this->collection->find()->toArray();

        $this->assertEquals(1, count($cursor));
        $this->assertEquals('1562999c-ee25-4a4a-bb51-7f4e238df180', $cursor[0]['_id']);
        $this->assertEquals('02a45c1c-8c4e-4af1-bb3a-51af14f85a42', $cursor[0]['user_id']);
        $this->assertEquals('51.52564,-0.087604', $cursor[0]['start_latlng']);
        $this->assertEquals('51.478873,-0.026715', $cursor[0]['end_latlng']);
        $this->assertEquals(1589673600, $cursor[0]['start_date']);
        $this->assertEquals(1000, $cursor[0]['activity_duration']);
        $this->assertEquals(2000, $cursor[0]['public_transport_duration']);
        $this->assertEquals(4.7, $cursor[0]['public_transport_cost']);
    }

    public function test_getCommutesByUserId_should_retrieve_and_transform_commutes()
    {
        $userId = Uuid::fromString('87491abb-7b3f-48d1-a34f-8082a273b9a7');

        $this->newCommute([
            '_id' => $id1 = '8c5aa612-5985-435e-b2b2-fc6bc1006284',
            'user_id' => '87491abb-7b3f-48d1-a34f-8082a273b9a7'
        ]);
        $this->newCommute([
            '_id' => $id2 = 'd5c6bfa7-8231-4724-99b8-75832772c50c',
            'user_id' => '87491abb-7b3f-48d1-a34f-8082a273b9a7'
        ]);
        $this->newCommute([]);

        $commutes = $this->commuteRepository->getCommutesByUserId($userId);

        $commuteIds = array_map(function (Commute $commute) {
            return (string) $commute->getId();
        }, $commutes);

        $this->assertEquals(2, count($commutes));
        $this->assertTrue($this->assertArrayContains($commuteIds, $id1));
        $this->assertTrue($this->assertArrayContains($commuteIds, $id2));
    }

    public function test_deleteCommutesByUserId_should_find_and_remove_commutes()
    {
        $userId = Uuid::fromString('87491abb-7b3f-48d1-a34f-8082a273b9a7');

        $this->newCommute(['user_id' => '87491abb-7b3f-48d1-a34f-8082a273b9a7']);
        $this->newCommute(['user_id' => '87491abb-7b3f-48d1-a34f-8082a273b9a7']);
        $this->newCommute([]);

        $userCommutes = count($this->commuteRepository->getCommutesByUserId($userId));

        $this->assertEquals(2, $userCommutes);

        $this->commuteRepository->deleteCommutesByUserId($userId);
        $userCommutes = count($this->commuteRepository->getCommutesByUserId($userId));

        $this->assertEquals(0, $userCommutes);
    }

    private function newCommute(array $attributes): InsertOneResult
    {
        return $this->collection->insertOne([
            '_id' => $attributes['_id'] ?? (string) Uuid::uuid4(),
            'user_id' => $attributes['user_id'] ?? (string) Uuid::uuid4(),
            'start_latlng' => $attributes['start_latlng'] ?? '51.478873,-0.026715',
            'end_latlng' => $attributes['end_latlng'] ?? '52.478873,-0.046715',
            'start_date' => $attributes['start_date'] ?? 1589673600,
            'activity_duration' => $attributes['activity_duration'] ?? 1000,
            'public_transport_duration' => $attributes['public_transport_duration'] ?? 2000,
            'public_transport_cost' => $attributes['public_transport_cost'] ?? 4.70,
        ]);
    }

    private function assertArrayContains(array $array, $value)
    {
        foreach ($array as $object) {
            if ($object == $value) {
                return true;
            }
        }
        return false;
    }
}
