<?php

namespace Ibnab\Bundle\PmanagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Ibnab\Bundle\PmanagerBundle\DependencyInjection\Compiler\PmanagerVariablesPass;
use Ibnab\Bundle\PmanagerBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass;

class IbnabPmanagerBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new PmanagerVariablesPass());
        $container->addCompilerPass(new TwigSandboxConfigurationPass());

    }


}
