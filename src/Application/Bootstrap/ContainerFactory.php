<?php

namespace CycleSaver\Application\Bootstrap;

use CycleSaver\Application\Bootstrap\Definitions\MongoDefinition;
use CycleSaver\Application\Bootstrap\Definitions\RepositoryDefinition;
use CycleSaver\Application\Bootstrap\Definitions\SlimLoggerDefinition;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;
use Exception;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    /**
     * @return ContainerInterface
     * @throws Exception
     */
    public function create(): ContainerInterface
    {
        return (new ContainerBuilder())
            ->addDefinitions($this->getDefinitions())
            ->build();
    }

    /**
     * @return DefinitionHelper[]
     */
    protected function getDefinitions(): array
    {
        return array_merge(
            SlimLoggerDefinition::getDefinitions(),
            MongoDefinition::getDefinitions(),
            RepositoryDefinition::getDefinitions(),
        );
    }
}
