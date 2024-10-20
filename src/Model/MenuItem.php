<?php

namespace Partitech\SonataMenu\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sonata_menu_item')]
#[ORM\MappedSuperclass]
#[ORM\InheritanceType('SINGLE_TABLE')]
abstract class MenuItem implements MenuItemInterface
{
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Translatable]
    protected $name;

    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    protected $url;

    #[ORM\Column(name: 'class_attribute', type: 'string', length: 255, nullable: true)]
    protected $classAttribute;

    #[ORM\Column(name: 'position', type: 'smallint', options: ['unsigned' => true], nullable: true)]
    protected $position;

    #[ORM\Column(name: 'target', type: 'boolean', nullable: true, options: ['default' => false])]
    protected $target;

    #[ORM\Column(name: 'enabled', type: 'boolean', nullable: true, options: ['default' => true])]
    protected $enabled;

    protected $page;

    #[ORM\ManyToOne(targetEntity: "\Partitech\SonataMenu\Model\MenuItemInterface", inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    protected $parent;

    #[ORM\OneToMany(targetEntity: "\Partitech\SonataMenu\Model\MenuItemInterface", mappedBy: 'parent', cascade: ['all'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $children;

    #[ORM\ManyToOne(targetEntity: "\Partitech\SonataMenu\Model\MenuInterface", inversedBy: 'menuItems')]
    #[ORM\JoinColumn(name: 'menu', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    protected $menu;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->position = 999;
        $this->enabled = true;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setClassAttribute(?string $classAttribute): self
    {
        $this->classAttribute = $classAttribute;

        return $this;
    }

    public function getClassAttribute(): ?string
    {
        return $this->classAttribute;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setTarget(?bool $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget(): ?bool
    {
        return $this->target;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        if (!$enabled && $this->hasChild()) {
            foreach ($this->children as $child) {
                if ($child->enabled) {
                    $child->setEnabled(false);
                    $child->setParent(null);
                }
            }
            $this->children = new ArrayCollection();
        }

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    public function setParent(?MenuItemInterface $parent): self
    {
        $this->parent = $parent;

        if (!is_null($parent)) {
            $parent->addChild($this);
        }

        return $this;
    }

    public function addChild(MenuItemInterface $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    public function removeChild(MenuItemInterface $child): void
    {
        $this->children->removeElement($child);
    }

    public function setChildren(ArrayCollection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setMenu(MenuInterface $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getMenu(): ?MenuInterface
    {
        return $this->menu;
    }

    public function hasChild(): bool
    {
        return count($this->children) > 0;
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    public function getActiveChildren(): array
    {
        $children = [];

        foreach ($this->children as $child) {
            if ($child->enabled) {
                array_push($children, $child);
            }
        }

        return $children;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function __clone()
    {
        if ($this->getId()) {
            $this->setId(null);
            $this->children = null;
            $this->parent = null;
            $this->menu = null;
        }
    }
}
