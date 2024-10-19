<?php

namespace CsrDelft\entity\bibliotheek;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\bibliotheek\BoekRecensieRepository::class
	)
]
#[ORM\Table('biebbeschrijving')]
class BoekRecensie
{
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer', options: ['default' => 0])]
	public $boek_id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	public $schrijver_uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'schrijver_uid', referencedColumnName: 'uid')]
	public $schrijver;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $beschrijving;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $toegevoegd;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $bewerkdatum;

	/**
	 * @var Boek
	 */
	#[ORM\ManyToOne(targetEntity: \Boek::class, inversedBy: 'recensies')]
	#[ORM\JoinColumn(name: 'boek_id', referencedColumnName: 'id')]
	public $boek;

	public function getBoek()
	{
		return $this->boek;
	}

	/*
	 * @param 	$uid lidnummer of null
	 * @return	bool
	 * 		een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */

	/**
	 * controleert rechten voor bewerkactie
	 *
	 * @return bool
	 *        een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */
	public function magVerwijderen()
	{
		return $this->isSchrijver();
	}

	public function isSchrijver($uid = null)
	{
		if (!LoginService::mag(P_LOGGED_IN)) {
			return false;
		}
		if ($uid === null) {
			$uid = LoginService::getUid();
		}
		return $this->schrijver->uid == $uid;
	}

	/**
	 * @return bool
	 */
	public function magBewerken()
	{
		return $this->isSchrijver();
	}
}
