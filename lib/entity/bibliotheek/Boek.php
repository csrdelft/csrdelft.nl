<?php

namespace CsrDelft\entity\bibliotheek;

use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\bibliotheek\BoekRepository::class
	)
]
#[ORM\Table('biebboek')]
class Boek
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $titel;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $auteur;
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $uitgavejaar;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $uitgeverij;
	/**
	 * @var int|null
	 */
	#[ORM\Column(type: 'integer', nullable: true)]
	public $paginas;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $taal = 'Nederlands';
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $isbn;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $code;
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer', nullable: true)]
	public $categorie_id;

	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer', options: ['default' => 0])]
	public $auteur_id = 0;

	/**
	 * @var BiebAuteur
	 */
	#[ORM\ManyToOne(targetEntity: \BiebAuteur::class)]
	#[ORM\JoinColumn(name: 'auteur_id', referencedColumnName: 'id')]
	public $auteur2;

	/**
	 * @var BoekRecensie[]
	 */
	#[ORM\OneToMany(targetEntity: \BoekRecensie::class, mappedBy: 'boek')]
	protected $recensies;

	/**
	 * @var BoekExemplaar[]
	 */
	#[ORM\OneToMany(targetEntity: \BoekExemplaar::class, mappedBy: 'boek')]
	protected $exemplaren;

	/**
	 * @var BiebRubriek|null
	 */
	#[ORM\ManyToOne(targetEntity: \BiebRubriek::class)]
	#[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id')]
	protected $categorie;

	public function getRubriek(): BiebRubriek|null
	{
		return $this->categorie;
	}

	public function getUrl(): string
	{
		return '/bibliotheek/boek/' . $this->id;
	}

	/**
	 * @return BoekRecensie[]
	 */
	public function getRecensies()
	{
		return $this->recensies ?? [];
	}
}
