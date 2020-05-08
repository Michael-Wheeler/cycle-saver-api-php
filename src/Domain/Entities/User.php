<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\Uuid;

class User //extends AbstractEntity
{
    private string $email;
    private string $password;
    protected Uuid $id;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function toObject()
    {
        return (object) [
            'id' => $this->id ?? '',
            'email' => $this->email ?? '',
            'password' => $this->password ?? ''
        ];
    }
}
