<?php

namespace Partitech\SonataMenu\DependencyInjection\Compiler;

use Partitech\SonataMenu\Entity\Menu;
use Partitech\SonataMenu\Entity\MenuItem;
use Partitech\SonataMenu\Model\MenuInterface;
use Partitech\SonataMenu\Model\MenuItemInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineResolveTargetEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        $definitionDriver = $container->findDefinition('doctrine.orm.default_metadata_driver');

        $menuTarget = $container->getParameter('sonata_menu.entity.menu');
        $menuItemTarget = $container->getParameter('sonata_menu.entity.menu_item');

        $definition
            ->addMethodCall('addResolveTargetEntity', [
                    MenuInterface::class,
                    $menuTarget,
                    [],
                ]
            )
            ->addMethodCall('addResolveTargetEntity', [
                    MenuItemInterface::class,
                    $menuItemTarget,
                    [],
                ]
            );

        $this->removeEntityMapping($definition, Menu::class, $menuTarget);
        $this->removeEntityMapping($definition, MenuItem::class, $menuItemTarget);
        $definition->addTag('doctrine.event_subscriber', ['connection' => 'default']);
    }

    // Ignore orm objects in Entity folder
    protected function removeEntityMapping($definition, $origin, $target)
    {
        $definition->addMethodCall('addResolveTargetEntity', [
                $origin,
                $target,
                [],
            ]
        );
    }
}
