<?php

namespace CycleSaver\Domain\Services;

use CycleSaver\Domain\Entities\User;
use CycleSaver\Domain\Repository\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function saveUser(User $user): string
    {
        return $this->userRepository->save($user);
    }
}
