<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\model\security\LoginModel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 * @ORM\Entity(repositoryClass="CsrDelft\repository\bibliotheek\BoekRecensieRepository")
 * @ORM\Table("biebbeschrijving")
 */
class BoekRecensie {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $boek_id;
	/**
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $schrijver_uid;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $beschrijving;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $toegevoegd;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $bewerkdatum;

	/**
	 * @var Boek
	 * @ORM\ManyToOne(targetEntity="Boek", inversedBy="recensies")
	 * @ORM\JoinColumn(name="boek_id", referencedColumnName="id")
	 */
	protected $boek;

	public function getBoek() {
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
	public function magVerwijderen() {
		return $this->isSchrijver();
	}

	public function isSchrijver($uid = null) {
		if (!LoginModel::mag(P_LOGGED_IN)) {
			return false;
		}
		if ($uid === null) {
			$uid = LoginModel::getUid();
		}
		return $this->schrijver_uid == $uid;
	}

	/**
	 * @return bool
	 */
	public function magBewerken() {
		return $this->isSchrijver();
	}
}
