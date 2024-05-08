<?php

namespace CsrDelft\entity\documenten;

use CsrDelft\entity\ISelectEntity;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\documenten\DocumentCategorieRepository")
 */
class DocumentCategorie implements ISelectEntity, DisplayEntity
{
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
	public $zichtbaar = true;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $leesrechten = P_LOGGED_IN;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $schrijfrechten = P_DOCS_MOD;

	/**
	 * @ORM\OneToMany(targetEntity="CsrDelft\entity\documenten\Document", mappedBy="categorie")
	 * @ORM\OrderBy({"toegevoegd" = "DESC"})
	 * @var Document[]|ArrayCollection
	 */
	public $documenten;

	public function magBekijken(): bool
	{
		return LoginService::mag($this->leesrechten);
	}

	public function getValue(): string
	{
		return $this->naam;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->naam ?? '';
	}
}
