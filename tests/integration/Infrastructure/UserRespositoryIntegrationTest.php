<?php

namespace CycleSaver\Infrastructure;

use CycleSaver\Application\Bootstrap\ContainerFactory;
use CycleSaver\Domain\Entities\User;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserRepositoryIntegrationTest extends TestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $container = (new ContainerFactory())->create();

        $this->userRepository = new UserRepository(
            $container->get(Manager::class),
            $container->get(LoggerInterface::class)
        );
    }

    public function test_saveUser_should_insert_user_to_database_and_return_id_string()
    {
        $user = (new User())
            ->setId($id = Uuid::uuid4())
            ->setEmail('test@test.com')
            ->setPassword('password');

        $retrievedId = $this->userRepository->save($user);

        $this->assertEquals($id, $retrievedId);
    }
}
