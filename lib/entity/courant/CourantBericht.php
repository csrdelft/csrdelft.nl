<?php

namespace CsrDelft\entity\courant;

use CsrDelft\repository\CourantBerichtRepository;
use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CourantBericht
 * @package CsrDelft\entity\courant
 */
#[ORM\Entity(repositoryClass: CourantBerichtRepository::class)]
#[ORM\Table('courantbericht')]
class CourantBericht
{
	/**
	 * @var integer
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
	 * @var CourantCategorie
	 */
	#[ORM\Column(type: 'enumCourantCategorie')]
	public $cat;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $bericht;
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	public $volgorde;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	public $uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $schrijver;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime', name: 'datumTijd')]
	public $datumTijd;

	public function setVolgorde()
	{
		if ($this->cat == null) {
			return;
		}

		$this->volgorde = [
			'voorwoord' => 0,
			'bestuur' => 1,
			'csr' => 2,
			'overig' => 3,
			'sponsor' => 4,
		][$this->cat->getValue()];
	}
}
