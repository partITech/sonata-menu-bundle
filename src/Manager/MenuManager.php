<?php

namespace Partitech\SonataMenu\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataMenu\Entity\Menu;
use Partitech\SonataMenu\Model\MenuInterface;
use Partitech\SonataMenu\Model\MenuItem;
use Partitech\SonataMenu\Model\MenuItemInterface;
use Partitech\SonataMenu\Repository\MenuitemRepository;
use Partitech\SonataMenu\Repository\MenuRepository;

/**
 * Menu manager.
 */
class MenuManager
{
    public const STATUS_ENABLED = true;
    public const STATUS_DISABLED = false;
    public const STATUS_ALL = null;

    public const ITEM_ROOT = true;
    public const ITEM_CHILD = false;
    public const ITEM_ALL = null;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * @var MenuItemRepository
     */
    protected $menuItemRepository;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->menuRepository = $em->getRepository(MenuInterface::class);
        $this->menuItemRepository = $em->getRepository(MenuItemInterface::class);
    }

    /**
     * Load menu by id.
     *
     * @param int $id
     *
     * @return MenuInterface
     */
    public function load($id)
    {
        $menu = $this->menuRepository->find($id);

        return $menu;
    }

    /**
     * Load menu by alias.
     *
     * @param string $alias
     *
     * @return MenuInterface
     */
    public function loadByAlias($alias)
    {
        $menu = $this->menuRepository->findOneByAlias($alias);

        return $menu;
    }

    /**
     * Remove a menu.
     */
    public function remove($menu)
    {
        $menu = $this->menuRepository->remove($menu);
    }

    /**
     * Save a menu.
     *
     * @param Menu $menu
     */
    public function save(MenuInterface $menu)
    {
        $this->menuRepository->save($menu);
    }

    /**
     * @return MenuInterface[]
     */
    public function findAll()
    {
        return $this->menuRepository->findAll();
    }

    /**
     * Get first level menu items.
     *
     * @param Menu $menu
     *
     * @return MenuItemInterface[]
     */
    public function getRootItems(MenuInterface $menu, $status)
    {
        return $this->getMenuItems($menu, static::ITEM_ROOT, $status);
    }

    /**
     * Get enabled menu items.
     *
     * @param Menu $menu
     *
     * @return MenuItemInterface[]
     */
    public function getEnabledItems(MenuInterface $menu)
    {
        return $this->getMenuItems($menu, static::ITEM_ALL, static::STATUS_ENABLED);
    }

    /**
     * Get disabled menu items.
     *
     * @param Menu $menu
     *
     * @return MenuItemInterface[]
     */
    public function getDisabledItems(MenuInterface $menu)
    {
        return $this->getMenuItems($menu, static::ITEM_ALL, static::STATUS_DISABLED);
    }

    /**
     * Get menu items.
     *
     * @return MenuItem[]
     */
    public function getMenuItems(MenuInterface $menu, $root = self::ITEM_ALL, $status = self::STATUS_ALL)
    {
        $menuItems = $menu->getMenuItems()->toArray();

        return array_filter($menuItems, function (MenuItemInterface $menuItem) use ($root, $status) {
            // Check root parameter
            if ($root === static::ITEM_ROOT && null !== $menuItem->getParent()
             || $root === static::ITEM_CHILD && null === $menuItem->getParent()
            ) {
                return;
            }

            // Check status parameter
            if ($status === static::STATUS_ENABLED && !$menuItem->getEnabled()
             || $status === static::STATUS_DISABLED && $menuItem->getEnabled()
            ) {
                return;
            }

            return $menuItem;
        });
    }

    /**
     * Update menu tree.
     *
     * @param array $items
     *
     * @return bool
     */
    public function updateMenuTree($menu, $items, $parent = null)
    {
        $update = false;

        if (!($menu instanceof MenuInterface)) {
            $menu = $this->load($menu);
        }

        if (!empty($items) && $menu) {
            foreach ($items as $pos => $item) {
                /** @var MenuItem $menuItem */
                $menuItem = $this->menuItemRepository->findOneBy(['id' => $item->id, 'menu' => $menu]);

                if ($menuItem) {
                    $menuItem
                        ->setPosition($pos)
                        ->setParent($parent)
                    ;

                    $this->em->persist($menuItem);
                }

                if (isset($item->children) && !empty($item->children)) {
                    $this->updateMenuTree($menu, $item->children, $menuItem);
                }
            }

            $this->em->flush();

            $update = true;
        }

        return $update;
    }
}
