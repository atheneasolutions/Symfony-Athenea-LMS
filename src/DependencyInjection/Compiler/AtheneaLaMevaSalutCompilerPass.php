<?php

namespace Athenea\LMS\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AtheneaLaMevaSalutCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('doctrine_mongodb')) {
            // Doctrine MongoDB ODM is not available, skip configuration
            return;
        }
    }
}
