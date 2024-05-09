<?php

namespace CsrDelft\entity\bibliotheek;

use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use Boek;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 */
#[ORM\Table('biebbeschrijving')]
#[ORM\Entity(repositoryClass: BoekRecensieRepository::class)]
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
 #[ORM\JoinColumn(name: 'schrijver_uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $schrijver;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $beschrijving;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 public $toegevoegd;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 public $bewerkdatum;

	/**
  * @var Boek
  */
 #[ORM\JoinColumn(name: 'boek_id', referencedColumnName: 'id')]
 #[ORM\ManyToOne(targetEntity: Boek::class, inversedBy: 'recensies')]
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
	public function magVerwijderen(): bool
	{
		return $this->isSchrijver();
	}

	public function isSchrijver($uid = null): bool
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
	public function magBewerken(): bool
	{
		return $this->isSchrijver();
	}
}
