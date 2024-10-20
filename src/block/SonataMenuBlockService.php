<?php

namespace Partitech\SonataMenu\block;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;
use Partitech\SonataExtra\Service\AssetsHandler;

#[AutoconfigureTag(name: 'sonata.block')]
final class SonataMenuBlockService extends AbstractBlockService implements EditableBlockService
{
    private $entityManager;
    private ParameterBagInterface $parameterBag;
    private ContainerInterface $container;
    private CmsManagerSelectorInterface $cmsSelector;
    private AssetsHandler $assetsHandler;

    #[Required]
    public function autowireDependencies(
        Environment $twig,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        ContainerInterface $container,
        CmsManagerSelectorInterface $cmsSelector,
        AssetsHandler $assetsHandler,
        RequestStack $requestStack
    ): void {
        parent::__construct($twig);
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->container = $container;
        $this->cmsSelector = $cmsSelector;
        $this->assetsHandler = $assetsHandler;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $cms = $this->cmsSelector->retrieve();
        $siteId = $cms->getCurrentPage()->getSite()->getId();
        $page = $cms->getCurrentPage();
        if(empty($this->request->get('page'))){
            $this->request->attributes->set('page', $page);
        }

        $settings = $blockContext->getSettings();
        $template = $settings['template'];
        $style_url = $settings['style_url'];
        $menuId = $settings['menu'];

        if(!empty($style_url)){
            $this->assetsHandler->addCss($style_url);
        }

        if (!empty($menuId)) {
            $menuManager = $this->container->get('sonata_menu.manager');
            $menu = $menuManager->load($menuId);

            // load menu from translation if availlable.
            if (
                isset($menu->translations)
                && !empty($menu->translations[$siteId])
                && !empty($menu->translations[$siteId]['entity_id'])
                && $menu->translations[$siteId]['entity_id'] != $menuId
            ) {
                $menu = $menuManager->load($menu->translations[$siteId]['entity_id']);
            }

            $menuItems = $menuManager->getMenuItems($menu, true);
           // dd($menuItems);
        }

        // $menu = $this->entityManager->getRepository($menu_entity)->find($menuId);

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'menuItems' => $menuItems,
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {

        $menu_entity = $this->parameterBag->get('sonata_menu.entity.menu');

        $menus = $this->entityManager->getRepository($menu_entity)->findAll();

        $menuChoices = [];
        foreach ($menus as $m) {
            $menuChoices['#'.$m->getId().' : '.$m->getName()] = $m->getId();
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['menu', ChoiceType::class, [
                    'label' => 'Menu',
                    'translation_domain' => 'SonataExtraBundle',
                    'choices' => $menuChoices,
                ]],
                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['style_url', TextType::class, [
                    'label' => 'Style URL',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                ]],
            ],
            'translation_domain' => 'SonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'menu' => null,
            'class' => null,
            'style_url' => '',
            'template' => '@PartitechSonataMenu/Blocks/menu_default.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Menu Sonata', null, null, 'SonataBlockBundle', [
            'class' => 'icon-block-tree_view',
        ]);
    }
}
