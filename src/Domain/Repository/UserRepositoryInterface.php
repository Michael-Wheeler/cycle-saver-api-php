<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    /**
     * @param User $user
     * @return void
     * @throws RepositoryException
     */
    public function save(User $user): void;

    /**
     * @param UuidInterface $id
     * @return User
     * @throws RepositoryException
     */
    public function getUserById(UuidInterface $id): User;
}
