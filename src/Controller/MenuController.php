<?php

namespace Partitech\SonataMenu\Controller;

use Partitech\SonataExtra\Controller\Admin\TranslationController;
use Partitech\SonataMenu\Manager\MenuManager;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuController extends Controller
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;

    private $menuManager;
    private $translator;
    private $routeGenerator;
    private $adminPool;

    #[Required]
    public function autowireDependencies(
        MenuManager $menuManager,
        TranslatorInterface $translator,
        DefaultRouteGenerator $routeGenerator,
        Pool $adminPool,
        TranslationController $TranslationController
    ): void {
        $this->menuManager = $menuManager;
        $this->translator = $translator;
        $this->routeGenerator = $routeGenerator;
        $this->adminPool = $adminPool;
        $this->TranslationController = $TranslationController;
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn): Response
    {
        return $this->TranslationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
    }

    /**
     * Manager menu items.
     */
    public function itemsAction(Request $request, $id)
    {
        $object = $this->admin->getSubject();

        if (empty($object)) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        /** @var MenuManager $menuManager */
        // $menuManager = $this->container->get("sonata_menu.manager");
        $menuManager = $this->menuManager;

        if (null !== $request->get('btn_update') && 'POST' == $request->getMethod()) {
            $menuId = $request->get('menu', null);
            $items = $request->get('items', null);

            if (!empty($items) && !empty(intval($menuId))) {
                $items = json_decode($items);

                $update = $menuManager->updateMenuTree($menuId, $items);
                /* @var TranslatorInterface $translator */
                // $translator = $this->get('translator');

                $request->getSession()->getFlashBag()->add('notice',
                    $this->translator->trans(
                        $update ? 'config.label_saved' : 'config.label_error',
                        [],
                        'PartitechSonataMenuBundle'
                    )
                );

                return new RedirectResponse($this->routeGenerator->generateUrl(
                    $this->adminPool->getAdminByAdminCode('sonata_menu.admin.menu'),
                    'items',
                    ['id' => $menuId]
                )
                );
            }
        }

        $menuItemsEnabled = $menuManager->getRootItems($object, MenuManager::STATUS_ENABLED);
        $menuItemsDisabled = $menuManager->getDisabledItems($object);

        $menus = $menuManager->findAll();

        return $this->renderWithExtraParams('@PartitechSonataMenu/Menu/menu_edit_items.html.twig', [
            'menus' => $menus,
            'menu' => $object,
            'menuItemsEnabled' => $menuItemsEnabled,
            'menuItemsDisabled' => $menuItemsDisabled,
        ]);
    }
}
