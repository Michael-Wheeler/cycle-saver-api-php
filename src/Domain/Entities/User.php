<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\UuidInterface;

class User
{
    private ?UuidInterface $id;
    private string $email;
    private string $password;
    private ?string $refreshToken;

    public function __construct(
        string $email,
        string $password,
        ?UuidInterface $id = null,
        ?string $refreshToken = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->refreshToken = $refreshToken;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}
