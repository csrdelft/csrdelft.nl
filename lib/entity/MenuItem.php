<?php

namespace CsrDelft\entity;

use CsrDelft\repository\MenuItemRepository;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * MenuItem.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een menu-item instantie beschrijft een menu onderdeel van een menu-boom
 * en heeft daarom een parent.
 */
#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
#[ORM\Table('menus')]
#[ORM\Index(name: 'prioriteit', columns: ['volgorde'])]
class MenuItem implements DisplayEntity
{
	/**
	 * Primary key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $item_id;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $volgorde;
	/**
	 * Link tekst
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $tekst;
	/**
	 * Link url
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $link;
	/**
	 * LoginModel::mag
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $rechten_bekijken;
	/**
	 * Zichtbaar of verborgen
	 * @var boolean
	 */
	#[ORM\Column(type: 'boolean')]
	public $zichtbaar;
	/**
	 * State of menu GUI
	 * @var boolean
	 */
	public $active;

	/**
	 * @var MenuItem|null
	 */
	#[ORM\ManyToOne(targetEntity: MenuItem::class, inversedBy: 'children')]
	#[ORM\JoinColumn(fieldName: 'parent_id', referencedColumnName: 'item_id')]
	public $parent;
	/**
	 * De sub-items van dit menu-item
	 * @var MenuItem[]|PersistentCollection
	 */
	#[ORM\OneToMany(targetEntity: MenuItem::class, mappedBy: 'parent')]
	#[ORM\OrderBy(['volgorde' => 'ASC', 'tekst' => 'ASC'])]
	public $children;

	public function hasChildren()
	{
		if (!$this->children) {
			return false;
		}

		if (is_array($this->children)) {
			return count($this->children);
		}

		return $this->children->count();
	}

	public function getId()
	{
		return $this->item_id;
	}

	public function getWeergave(): string
	{
		return $this->tekst . ' [' . $this->link . ']';
	}
}
