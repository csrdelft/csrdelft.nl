<?php

namespace CsrDelft\entity;

use CsrDelft\common\CsrException;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * MenuItem.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een menu-item instantie beschrijft een menu onderdeel van een menu-boom
 * en heeft daarom een parent.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\MenuItemRepository")
 * @ORM\Table("menus")
 */
class MenuItem {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $item_id;
	/**
	 * Dit menu-item is een sub-item van
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $parent_id;
	/**
	 * Volgorde van weergave
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $volgorde;
	/**
	 * Link tekst
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $tekst;
	/**
	 * Link url
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $link;
	/**
	 * LoginModel::mag
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $rechten_bekijken;
	/**
	 * Zichtbaar of verborgen
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $zichtbaar;
	/**
	 * State of menu GUI
	 * @var boolean
	 */
	public $active;

	/**
	 * @var MenuItem|null
	 * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
	 * @ORM\JoinColumn(fieldName="parent_id", referencedColumnName="item_id")
	 */
	public $parent;
	/**
	 * De sub-items van dit menu-item
	 * @var MenuItem[]|PersistentCollection
	 * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent")
	 * @ORM\OrderBy({"volgorde": "ASC", "tekst": "ASC"})
	 */
	public $children;

	public function hasChildren() {
		if (!$this->children) {
			return false;
		}

		if (is_array($this->children)) {
			return count($this->children);
		}

		return $this->children->count();
	}

	public function magBekijken() {
		return $this->zichtbaar && LoginModel::mag($this->rechten_bekijken);
	}

	public function magBeheren() {
		return $this->rechten_bekijken == LoginModel::getUid() || LoginModel::mag(P_ADMIN);
	}

	public function isOngelezen() {
		$prefix = '/forum/onderwerp/';
		if (startsWith($this->link, $prefix)) {
			$begin = strlen($prefix);
			$end = strpos($this->link, '/', $begin);
			if ($end) {
				$draad_id = substr($this->link, $begin, $end - $begin);
			} else {
				$draad_id = substr($this->link, $begin);
			}
			try {
				$draad = ForumDradenModel::instance()->get((int)$draad_id);
				return $draad->isOngelezen();
			} catch (CsrException $e) {
				setMelding('Uw favoriete forumdraadje bestaat helaas niet meer: ' . htmlspecialchars($this->tekst), 2);
			}
		}
		return false;
	}

}
