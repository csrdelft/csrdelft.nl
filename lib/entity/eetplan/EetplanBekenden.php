<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\datatable\DataTableColumn;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class EetplanBekenden
 * @package CsrDelft\model\entity\eetplan
 * @ORM\Entity(repositoryClass="CsrDelft\repository\eetplan\EetplanBekendenRepository")
 */
class EetplanBekenden implements DataTableEntry {
	/**
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 * @var string
	 * @Serializer\Groups("datatable")
	 */
	public $uid1;
	/**
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 * @var string
	 * @Serializer\Groups("datatable")
	 */
	public $uid2;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid1", referencedColumnName="uid")
	 */
	public $noviet1;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid2", referencedColumnName="uid")
	 */
	public $noviet2;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 * @Serializer\Groups("datatable")
	 */
	public $opmerking;

	public function setNoviet1($noviet) {
		$this->noviet1 = $noviet;

		if ($noviet) {
			$this->uid1 = $noviet->uid;
		}
	}

	public function setNoviet2($noviet) {
		$this->noviet2 = $noviet;

		if ($noviet) {
			$this->uid2 = $noviet->uid;
		}
	}

	/**
	 * @return DataTableColumn
	 * @Serializer\SerializedName("noviet1")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableNoviet1() {
		return $this->noviet1->getDataTableColumn();
	}

	/**
	 * @return DataTableColumn
	 * @Serializer\SerializedName("noviet2")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableNoviet2() {
		return $this->noviet2->getDataTableColumn();
	}
}
