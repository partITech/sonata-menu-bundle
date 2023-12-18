<?php

namespace Partitech\SonataMenu\Admin;

use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Partitech\SonataMenu\Model\MenuInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MenuAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    protected $baseRoutePattern = 'sonata/menu';

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('config.label_menu', ['translation_domain' => 'PartitechSonataMenuBundle'])
                ->add('name', TextType::class,
                    [
                        'label' => 'config.label_name',
                    ],
                    [
                        'translation_domain' => 'PartitechSonataMenuBundle',
                    ]
                )
                ->add('alias', TextType::class,
                    [
                        'label' => 'config.label_alias',
                    ],
                    [
                        'translation_domain' => 'PartitechSonataMenuBundle',
                    ]
                )
            ->end()
        ->end();
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id', null, ['label' => 'config.label_id', 'translation_domain' => 'PartitechSonataMenuBundle'])
            ->addIdentifier('alias', null, ['label' => 'config.label_alias', 'translation_domain' => 'PartitechSonataMenuBundle'])
            ->addIdentifier('name', null, ['label' => 'config.label_name', 'translation_domain' => 'PartitechSonataMenuBundle'])
        ;

        $listMapper->add('_action', 'actions', [
            'label' => 'config.label_modify',
            'translation_domain' => 'PartitechSonataMenuBundle',
            'actions' => [
                'edit' => [],
                'delete' => [],
                'items' => ['template' => '@PartitechSonataMenu/CRUD/list__action_edit_items.html.twig', 'route' => 'items'],
            ],
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name')
            ->add('alias')
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->configureTraitRoutes($collection);
        $collection->add('items', $this->getRouterIdParameter().'/items');
    }

    protected function configure(): void
    {
        $this->configureTrait();
        $this->setTemplate('edit', '@PartitechSonataMenu/CRUD/edit.html.twig');
    }

    public function toString(object $object): string
    {
        return $object instanceof MenuInterface ? $object->getName() : $this->getTranslator()->trans('config.label_menu', [], 'PartitechSonataMenuBundle');
    }

    protected function prePersist(object $object): void
    {
        parent::prePersist($object);
        foreach ($object->getMenuItems() as $menuItem) {
            $menuItem->setMenu($object);
        }
    }

    protected function preUpdate(object $object): void
    {
        parent::prePersist($object);
        foreach ($object->getMenuItems() as $menuItem) {
            $menuItem->setMenu($object);
        }
    }

    protected function preCreate(object $object): void
    {
        dd('dsf');
    }
}
