<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\ProfielModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CsrDelft\repository\eetplan\EetplanRepository")
 */
class Eetplan {
	/**
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 * @var string
	 */
	public $uid;

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @var int
	 */
	public $woonoord_id;

	/**
	 * @ORM\Column(type="date")
	 * @var \DateTime
	 */
	public $avond;

	/**
	 * Specifiek bedoelt voor bekende huizen.
	 *
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $opmerking;

	/**
	 * @return Woonoord|false|mixed
	 */
	public function getWoonoord() {
		return WoonoordenModel::get($this->woonoord_id);
	}

	/**
	 * @return Profiel|false
	 */
	public function getNoviet() {
		return ProfielModel::get($this->uid);
	}
}
