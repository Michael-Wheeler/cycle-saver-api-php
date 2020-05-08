<?php

namespace CycleSaver\Application\Bootstrap;

use CycleSaver\Application\Bootstrap\ServiceProviders\MongoDefinition;
use CycleSaver\Application\Bootstrap\ServiceProviders\ServiceDefinition;
use DI\Definition\Helper\DefinitionHelper;

class ContainerFactory
{
    /**
     * @var ServiceDefinition
     */
    private const DEFINITIONS = [
        MongoDefinition::class,
    ];

    /**
     * @return DefinitionHelper[]
     */
    protected function getProviderDefinitions(): array
    {
        return array_merge(...array_map(function (ServiceDefinition $definition) {
            return $definition->getDefinitions();
        }, $this->getProviders()));
    }

    /**
     * @return ServiceDefinition[]
     */
    protected function getProviders(): array
    {
        return array_map(function (string $definition) {
            return new $definition();
        }, self::DEFINITIONS);
    }
}
