<?php

namespace CycleSaver\Application\Bootstrap\ServiceProviders;

use DI\Definition\Helper\DefinitionHelper;

interface ServiceDefinition
{
    /**
     * Register an array of new container definitions
     *
     * @return DefinitionHelper[]
     */
    public function getDefinitions(): array;
}
