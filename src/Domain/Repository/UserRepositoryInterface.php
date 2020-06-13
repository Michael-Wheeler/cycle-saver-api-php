<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    /**
     * @param User $user
     * @return UuidInterface
     * @throws RepositoryException
     */
    public function save(User $user): UuidInterface;
}
