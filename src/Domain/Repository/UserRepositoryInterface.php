<?php

namespace CycleSaver\Domain\Repository;

use CycleSaver\Domain\Entities\User;

interface UserRepositoryInterface
{
    /**
     * @param User $user
     *
     * User ID
     * @return string
     */
    public function save(User $user): string;
}
