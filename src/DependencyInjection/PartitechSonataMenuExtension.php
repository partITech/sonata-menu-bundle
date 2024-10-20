<?php

namespace Partitech\SonataMenu\DependencyInjection;

use Partitech\SonataMenu\Admin\MenuAdmin;
use Partitech\SonataMenu\Admin\MenuItemAdmin;
use Partitech\SonataMenu\Entity\Menu;
use Partitech\SonataMenu\Entity\MenuItem;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PartitechSonataMenuExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $this->registerEntities($container, $config);
        $this->registerAdmins($container, $config);
    }

    /**
     * @return $this
     */
    protected function registerEntities(ContainerBuilder $container, array $config)
    {
        if (isset($config['entities'])) {
            if (isset($config['entities']['menu'])) {
                $container->setParameter('sonata_menu.entity.menu', $config['entities']['menu']);
            } else {
                $container->setParameter('sonata_menu.entity.menu', Menu::class);
            }

            if (isset($config['entities']['menu_item'])) {
                $container->setParameter('sonata_menu.entity.menu_item', $config['entities']['menu_item']);
            } else {
                $container->setParameter('sonata_menu.entity.menu_item', MenuItem::class);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function registerAdmins(ContainerBuilder $container, array $config)
    {
        if (isset($config['admins'])) {
            if (isset($config['admins']['menu'])) {
                $container->setParameter('sonata_menu.admins.menu', $config['admins']['menu']);
            } else {
                $container->setParameter('sonata_menu.admins.menu', MenuAdmin::class);
            }

            if (isset($config['admins']['menu_item'])) {
                $container->setParameter('sonata_menu.admins.menu_item', $config['admins']['menu_item']);
            } else {
                $container->setParameter('sonata_menu.admins.menu_item', MenuItemAdmin::class);
            }
        }

        return $this;
    }
}
