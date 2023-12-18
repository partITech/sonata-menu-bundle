<?php

namespace Partitech\SonataMenu\Repository;

use Doctrine\ORM\EntityRepository;
use Partitech\SonataMenu\Model\MenuInterface;

/**
 * MenuRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MenuRepository extends EntityRepository
{
    /**
     * Remove a menu.
     */
    public function remove(MenuInterface $menu)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            foreach ($menu->getMenuItems() as $menuItem) {
                $em->remove($menuItem);
            }

            $em->remove($menu);
            $em->flush();

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Save a menu.
     */
    public function save(MenuInterface $menu)
    {
        $em = $this->getEntityManager();

        $em->persist($menu);

        $em->flush();
    }

    /**
     * Remove a menu from its id.
     *
     * @param int $id
     */
    public function removeById($id)
    {
        $menu = $this->find($id);

        $this->remove($menu);
    }
}
