<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StravaUser
{
    private UuidInterface $id;
    private UuidInterface $userId;
    private string $stravaId;
    private string $refreshToken;

    public function __construct(?UuidInterface $id, UuidInterface $userId, string $stravaId, string $refreshToken)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->userId = $userId;
        $this->stravaId = $stravaId;
        $this->refreshToken = $refreshToken;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getStravaId(): string
    {
        return $this->stravaId;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
