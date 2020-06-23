<?php

namespace CycleSaver\Domain\Services;

use CycleSaver\Domain\Entities\Commute;
use CycleSaver\Domain\Entities\PTJourney;
use CycleSaver\Domain\Entities\StravaActivity;
use CycleSaver\Domain\Repository\CommuteRepositoryInterface;
use CycleSaver\Domain\Repository\RepositoryException;
use CycleSaver\Domain\Repository\UserRepositoryInterface;
use CycleSaver\Infrastructure\Strava\StravaRepository;
use CycleSaver\Infrastructure\Tfl\TflApiRepository;
use CycleSaver\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

class StravaServiceTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var StravaRepository|ObjectProphecy
     */
    private $stravaRepo;
    /**
     * @var UserRepositoryInterface|ObjectProphecy
     */
    private $userRepo;
    /**
     * @var CommuteRepositoryInterface|ObjectProphecy
     */
    private $commuteRepo;
    /**
     * @var TflApiRepository|ObjectProphecy
     */
    private $tflRepo;
    private StravaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stravaRepo = $this->prophesize(StravaRepository::class);
        $this->userRepo = $this->prophesize(UserRepositoryInterface::class);
        $this->commuteRepo = $this->prophesize(CommuteRepositoryInterface::class);
        $this->tflRepo = $this->prophesize(TflApiRepository::class);

        $this->service = new StravaService(
            $this->stravaRepo->reveal(),
            $this->userRepo->reveal(),
            $this->commuteRepo->reveal(),
            $this->tflRepo->reveal()
        );
    }

    public function test_createStravaUser_should_get_strava_data_and_create_new_user()
    {
        $authCode = 'auth_code';

        $this->userRepo->save(Argument::any())->shouldBeCalled();

        $this->stravaRepo->createUser(Argument::any(), $authCode)->shouldBeCalled();

        $this->stravaRepo->getActivities(Argument::any())->shouldBeCalled()
            ->willReturn($activities = [
                new StravaActivity(
                    [1, 1],
                    [2, 2],
                    new \DateTimeImmutable('2020-06-01'),
                    new \DateInterval('PT1S'),
                    10
                ),
            ]);

        $this->tflRepo->createPTJourneys([
            [
                'start_latlng' => [1, 1],
                'end_latlng' => [2, 2],
            ]
        ])->shouldBeCalled()->willReturn([
            new PTJourney(100, new \DateInterval('PT2S'))
        ]);

        $expectedCommute = Argument::that(function (Commute $commute) {
            $this->assertEquals([1, 1], $commute->getStartLatLong());
            $this->assertEquals([2, 2], $commute->getEndLatLong());
            $this->assertEquals(100, $commute->getPTCost());
            $this->assertEquals(new \DateInterval('PT2S'), $commute->getPTDuration());
            $this->assertEquals(new \DateInterval('PT1S'), $commute->getActivityDuration());
            $this->assertEquals(new \DateTimeImmutable('2020-06-01'), $commute->getStartDate());
            return true;
        });

        $this->commuteRepo->saveCommute($expectedCommute);

        $returnedUserId = $this->service->createStravaUser($authCode);

        $this->assertTrue($returnedUserId instanceof UuidInterface);
    }

    public function test_createStravaUser_should_pass_up_RepositoryException()
    {
        $this->userRepo->save(Argument::any())->shouldBeCalled()
            ->willThrow(new RepositoryException('Error message'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Error message');

        $this->service->createStravaUser('auth_code');
    }
}
