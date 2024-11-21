<?php

namespace Athenea\LMS;

use Athenea\LMS\DependencyInjection\AtheneaLaMevaSalutExtension;
use Athenea\LMS\DependencyInjection\Compiler\AtheneaLaMevaSalutCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AtheneaLaMevaSalutBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AtheneaLaMevaSalutExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AtheneaLaMevaSalutCompilerPass());
    }
}