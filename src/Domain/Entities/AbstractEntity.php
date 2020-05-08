<?php

namespace CycleSaver\Domain\Entities;

use Ramsey\Uuid\Uuid;

abstract class AbstractEntity
{
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
}
