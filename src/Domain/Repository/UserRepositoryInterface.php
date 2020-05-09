<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    /**
     * @param User $user
     *
     * User ID
     * @return UuidInterface
     */
    public function save(User $user): UuidInterface;
}
