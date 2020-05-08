<?php

namespace CycleSaver\Application\Bootstrap\Definitions;

use DI\Definition\Helper\DefinitionHelper;

interface ServiceDefinition
{
    /**
     * Register an array of new container definitions
     *
     * @return DefinitionHelper[]
     */
    public static function getDefinitions(): array;
}
