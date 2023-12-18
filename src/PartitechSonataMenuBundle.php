<?php

namespace Partitech\SonataMenu;

use Partitech\SonataMenu\DependencyInjection\Compiler\DoctrineResolveTargetEntityPass;
use Partitech\SonataMenu\DependencyInjection\PartitechSonataMenuExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PartitechSonataMenuBundle extends AbstractBundle
{
    /**
     * @return void
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new PartitechSonataMenuExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineResolveTargetEntityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}
