
# Sonata Menu Bundle

This bundle facilitates menu management through the Sonata Admin Bundle and is compatible with the Sonata Page Bundle. It's a derivative of the Prodigous Sonata Menu Bundle, found at this GitHub repository. As the original bundle hasn't seen updates, and our pull requests remain pending, we've taken the initiative to upgrade and continue delivering this valuable resource to the community.


# Compatibility
Symfony 6.3

# Prerequisites
- SonataAdminBundle
- SonataPageBundle (Optional)


# Installation

```

composer require partitech/sonata-menu-bundle

```

# Configuration


```
// Symfony 6
// config/bundles.php
Partitech\SonataMenu\PartitechSonataMenuBundle::class => ['all' => true],

php bin/console cache:clear
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
php bin/console assets:install
```

### sonata_admin.yml

Add menu to your sonata admin menu list.
For symfony 6, you can also let it empty, menu will be added automatically

```
sonata_admin:
    dashboard:
        groups:     
            // Optional for symfony 4
            sonata.admin.group.menu_builder:
                label:           config.label_menu
                label_catalogue: PartitechSonataMenuBundle
                icon:            '<i class="fa fa-magic"></i>'
                items:
                    - sonata_menu.admin.menu

            // Sonata page menu
            # sonata.admin.group.cms:
            #     label:           site
            #     label_catalogue: SonataPageBundle
            #     icon:            '<i class="fa fa-puzzle-piece"></i>'
            #     items:
            #         - sonata.page.admin.site
            #         - sonata.page.admin.page
```

# Advanced configurations ( Symfony 6 )
#### Create custom entities

Edit the configuration

* sonata_menu.yaml

```
sonata_menu:
    entities:
        menu: AppBundle\Entity\Menu
        menu_item: AppBundle\Entity\MenuItem
```

Then create the related entity menu and menu item.
You can add extra fields

* Menu

```
<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataMenu\Model\Menu as BaseMenu;


#[ORM\Table(name: "sonata_extra__menu")]
#[ORM\Entity(repositoryClass: "Partitech\SonataMenu\Repository\MenuRepository")]

class Menu extends BaseMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

* MenuItem

```
<?php
namespace App\Entity;

use App\Repository\MenuItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataMenu\Model\MenuItem as BaseMenuItem;

#[ORM\Table(name: "sonata_extra__menu_item")]
#[ORM\Entity(repositoryClass: "Partitech\SonataMenu\Repository\MenuItemRepository")]
class MenuItem extends BaseMenuItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```
Clear cache and update database
```
php bin/console cache:clear
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate

```

You update admin classes by extending the original ones :

Edit the configuration

* sonata_menu.yaml

```
sonata_menu:
    entities:
        menu: App\Entity\Menu
        menu_item: App\Entity\MenuItem
    admins:
        menu: App\Admin\MyMenuAdmin
        menu_item: App\Admin\MyMenuItemAdmin
```
And create your admin class
```
namespace App\Admin;

use Partitech\SonataMenu\Admin\MenuAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class MyMenuAdmin extends BaseAdmin
{

    protected function configureFormFields(FormMapper $formMapper):void
    {
        parent::configureFormFields($formMapper);
    }
}
```

```
namespace App\Admin;

use Partitech\SonataMenu\Admin\MenuItemAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MyMenuItemAdmin extends BaseAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        
        $formMapper
            ->with('config.label_menu_item')
                ->add('icon', TextType::class, [
                        'label' => 'config.label_icon'
                    ]
                )
            ->end()
        ->end();
    }
}
```

# Getting Started with Menu Manager

### Controller

```
$mm = $this->container->get('sonata_menu.manager');

$menuId = 1; // Example

$menu = $mm->load($menuId);

// $status = true (Get enabled menu items)
// $status = false (Get disabled menu items)
// getMenuItems($menu, $root = MenuManager::ITEM_CHILD, $status = MenuManager::STATUS_ALL)

$menuItems = $mm->getMenuItems($menu, true);

return  $this->render('menu/menu.html.twig', [
            '$menuItems' => $menuItems,
        ]);

```

#### And then, it's your showtime. For the frontend, you can output the results as you want. 

#### For example :

* menu.html.twig

```
{% if menuItems is not null and menuItems|length > 0 %}

{% import 'AppBundle:Menu:menu_tree_macro.html.twig' as tree %}

{% set currentPath = app.request.requestUri %}

{{ tree.menu(menuItems, currentPath) }}

{% endif %}
```

* menu_tree_macro.html.twig

```
{% macro menu(items, currentPath) %}
    
    {% import _self as self %}

        <ul>
            {% for menuItem in items %}
    
            {% set url = menuItem.url %}
            {% set attributes = "menu-item" %}
            {% if menuItem.classAttribute %}
            {% set attributes = attributes ~ ' ' ~ menuItem.classAttribute %}
            {% endif %}
            {% if menuItem.hasChild() %}
            
            {% set attributes = attributes ~ ' has-child' %}
            
            {% for childItem in menuItem.children %}

            {% set childUrl = childItem.url %}

            {% if childUrl == currentPath %}
            {% set attributes = attributes ~ ' current-parent' %}
            {% endif %}
            
            {% endfor %}
            
            {% endif %}

            <li class="{{ attributes }}" role="menu-item">
                {% if menuItem.hasChild() %}
                <a href="{{ url }}" class="parent" {% if currentPath == url %} class="current"{% endif %}" {% if menuItem.target %} target="_blank"{% endif %}>{{ menuItem.name }}</a>
                {{ self.menu(menuItem.children, currentPath) }}
                {% else %}
                <a href="{{ url }}" {% if currentPath == url %} class="current"{% endif %}" {% if menuItem.target %} target="_blank"{% endif %}>{{ menuItem.name }}</a>
                {% endif %}
            </li>
            {% endfor %}
        </ul>

{% endmacro %}
```

# Changelog
### 1.0.0
- Upgrade of the prodigious Menu bundle



# Additional info
### Initial version (sf2,sf3,sf4)
Author: [Nan GUO](https://github.com/nan-guo/)
Company : [Prodigious](http://www.prodigious.com/)

### Upgraded version (sf6)
Author: [Thomas Bourdin](mailto:tbourdin@partitech.com)
Company : [Partitech](http://www.partitech.com/)

Author: [Géraud Bourdin](mailto:gbourdin@partitech.com)
Company : [Partitech](http://www.partitech.com/)