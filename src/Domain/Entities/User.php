<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\UuidInterface;

class User
{
    private ?UuidInterface $id;
    private ?string $email;
    private ?string $password;
    private ?string $refreshToken;

    public function __construct(
        ?UuidInterface $id,
        ?string $email = null,
        ?string $password = null,
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
