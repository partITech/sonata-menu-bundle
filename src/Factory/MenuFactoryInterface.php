<?php

namespace Partitech\SonataMenu\Factory;

use Partitech\SonataMenu\Model\MenuInterface;

/**
 * Interface implemented by the factory to create menu.
 */
interface MenuFactoryInterface
{
    /**
     * Create a menu.
     *
     * @param string $name
     *
     * @return MenuInterface
     */
    public function create($name);
}
