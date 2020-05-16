<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Test\IntegrationTestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserRepositoryIntegrationTest extends IntegrationTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(
            $this->DBManager,
            $this->container->get(LoggerInterface::class)
        );
    }

    public function test_saveUser_should_insert_user_to_database_and_return_id_string()
    {
        $user = (new User($id = Uuid::uuid4()))
            ->setEmail('test@test.com')
            ->setPassword('password');

        $retrievedId = $this->userRepository->save($user);

        $this->assertEquals($id, $retrievedId);
    }

    public function test_saveUser_should_insert_multiple_users_to_database_and_return_id_string()
    {
        $user = (new User($id = Uuid::uuid4()))
            ->setEmail('test@test.com')
            ->setPassword('password');

        $user2 = (new User($id2 = Uuid::uuid4()))
            ->setEmail('test@test.com')
            ->setPassword('password');

        $retrievedId = $this->userRepository->save($user);
        $retrievedId2 = $this->userRepository->save($user2);

        $this->assertEquals($id, $retrievedId);
        $this->assertEquals($id2, $retrievedId2);
    }
}
