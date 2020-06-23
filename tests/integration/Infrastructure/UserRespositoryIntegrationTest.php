<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Domain\Entities\User;
use CycleSaver\IntegrationTestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserRepositoryIntegrationTest extends IntegrationTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(
            $this->database,
            $this->container->get(LoggerInterface::class)
        );
    }

    public function test_repository_can_save_and_retrieve_user()
    {
        $user = (new User($id = Uuid::uuid4()))
            ->setEmail('test@test.com')
            ->setPassword('password');

        $this->userRepository->save($user);

        $retrievedUser = $this->userRepository->getUserById($id);

        $this->assertEquals($user, $retrievedUser);
    }
}
