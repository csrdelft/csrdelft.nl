<?php

namespace CsrDelft\entity\documenten;

use CsrDelft\entity\ISelectEntity;
use CsrDelft\model\security\LoginModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Table("DocumentCategorie")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\documenten\DocumentCategorieRepository")
 */
class DocumentCategorie implements ISelectEntity {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	public $id;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $naam;
	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $zichtbaar;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $leesrechten;

	/**
	 * @ORM\OneToMany(targetEntity="CsrDelft\entity\documenten\Document", mappedBy="categorie")
	 * @ORM\OrderBy({"toegevoegd" = "DESC"})
	 * @var Document[]|ArrayCollection
	 */
	public $documenten;

	public function magBekijken() {
		return LoginModel::mag($this->leesrechten);
	}

	public function getValue() {
		return $this->naam;
	}

	public function getId() {
		return $this->id;
	}
}
