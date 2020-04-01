<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\WoonoordenModel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="CsrDelft\repository\eetplan\EetplanRepository")
 */
class Eetplan implements DataTableEntry {
	/**
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 * @var string
	 * @Serializer\Groups("datatable")
	 */
	public $uid;

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @var int
	 * @Serializer\Groups("datatable")
	 */
	public $woonoord_id;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 * @var \DateTimeImmutable
	 */
	public $avond;

	/**
	 * Specifiek bedoelt voor bekende huizen.
	 *
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 * @Serializer\Groups("datatable")
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

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("woonoord")
	 */
	public function getDataTableWoonoord() {
		return $this->getWoonoord()->naam;
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("naam")
	 */
	public function getDataTableNaam() {
		return $this->noviet->getNaam();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("avond")
	 */
	public function getDataTableAvond() {
		if ($this->avond) {
			return $this->avond->format(DATE_FORMAT);
		} else {
			return null;
		}
	}
}
