<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    public function save(User $user): UuidInterface;
}
