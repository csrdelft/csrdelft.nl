<?php

namespace CsrDelft\entity\documenten;

use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\entity\ISelectEntity;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Entity(repositoryClass: DocumentCategorieRepository::class)]
class DocumentCategorie implements ISelectEntity, DisplayEntity
{
	/**
	 * @var int
	 */
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $naam;
	/**
	 * @var boolean
	 */
	#[ORM\Column(type: 'boolean')]
	public $zichtbaar = true;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $leesrechten = P_LOGGED_IN;

	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $schrijfrechten = P_DOCS_MOD;

	/**
	 * @var Document[]|ArrayCollection
	 */
	#[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'categorie')]
	#[ORM\OrderBy(['toegevoegd' => 'DESC'])]
	public $documenten;

	public function magBekijken()
	{
		return LoginService::mag($this->leesrechten);
	}

	public function getValue()
	{
		return $this->naam;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->naam ?? '';
	}
}
