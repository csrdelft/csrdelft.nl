<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\entity\corvee\CorveeRepetitie;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeVoorkeur.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_voorkeur instantie beschrijft een voorkeur van een lid om een periodieke taak uit te voeren.
 *
 *
 * Zie ook CorveeRepetitie.class.php
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeVoorkeurenRepository")
 * @ORM\Table("crv_voorkeuren")
 */
class CorveeVoorkeur {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $crv_repetitie_id;
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;

	public $corvee_repetitie;
	public $van_uid;

	public function getCorveeRepetitieId() {
		return (int)$this->crv_repetitie_id;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getVanUid() {
		return $this->van_uid;
	}

	public function getCorveeRepetitie() {
		return $this->corvee_repetitie;
	}

	public function setCorveeRepetitie(CorveeRepetitie $repetitie) {
		$this->corvee_repetitie = $repetitie;
	}

	public function setVanUid($uid) {
		$this->van_uid = $uid;
	}
}
