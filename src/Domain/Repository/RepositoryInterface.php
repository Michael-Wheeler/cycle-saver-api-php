<?php

namespace CycleSaver\Domain\Repository;

use Ramsey\Uuid\Uuid;

interface RepositoryInterface
{
    public function getById(Uuid $id);

    public function getAll();

    public function save($entity);
}
