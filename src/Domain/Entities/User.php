<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\UuidInterface;

class User
{
    private ?UuidInterface $id;
    private string $email;
    private string $password;

    public function __construct(string $email, string $password, ?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
