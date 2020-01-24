<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\repository\ProfielRepository;
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
	 * @var Profiel
	 * @ORM\OneToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $noviet;

	/**
	 * @return Woonoord|false|mixed
	 */
	public function getWoonoord() {
		return WoonoordenModel::instance()->get($this->woonoord_id);
	}
}
