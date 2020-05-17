<?php

namespace CycleSaver\Application\Bootstrap;

use CycleSaver\Application\Bootstrap\Definitions\HttpClientDefinition;
use CycleSaver\Application\Bootstrap\Definitions\MongoDefinition;
use CycleSaver\Application\Bootstrap\Definitions\RepositoryDefinition;
use CycleSaver\Application\Bootstrap\Definitions\SlimLoggerDefinition;
use CycleSaver\Application\Bootstrap\Definitions\StravaInfrastructureDefinition;
use CycleSaver\Application\Bootstrap\Definitions\StravaServiceDefinition;
use CycleSaver\Application\Bootstrap\Definitions\TflDefinition;
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
            StravaInfrastructureDefinition::getDefinitions(),
            HttpClientDefinition::getDefinitions(),
            TflDefinition::getDefinitions(),
            StravaServiceDefinition::getDefinitions(),
        );
    }
}
