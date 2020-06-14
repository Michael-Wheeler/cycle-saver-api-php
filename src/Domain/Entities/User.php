<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class User
{
    private ?UuidInterface $id;
    private ?string $email;
    private ?string $password;

    public function __construct(
        ?UuidInterface $id = null,
        ?string $email = null,
        ?string $password = null
    ) {
        $this->id = $id ?? Uuid::uuid4();
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
