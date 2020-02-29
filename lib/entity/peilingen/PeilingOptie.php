<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\view\bbcode\CsrBB;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use CsrDelft\common\datatable\annotation as DT;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\peilingen\PeilingOptiesRepository")
 * @ORM\Table("peiling_optie")
 * @DT\DataTable()
 * @DT\DataTableRowKnop(action="/peilingen/opties/verwijderen", title="Optie verwijderen", icon="verwijderen")
 */
class PeilingOptie implements DataTableEntry {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("vue")
	 */
	public $id;
	/**
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(hidden=true)
	 */
	public $peiling_id;
	/**
	 * Titel
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn()
	 */
	public $titel;
	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(searchable=true)
	 */
	public $beschrijving;
	/**
	 * Aantal stemmen
	 * @var int
	 * @ORM\Column(type="integer")
	 * @DT\DataTableColumn()
	 */
	public $stemmen = 0;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=4, nullable=true)
	 * @DT\DataTableColumn()
	 */
	public $ingebracht_door;
	/**
	 * @var Peiling
	 * @ORM\ManyToOne(targetEntity="Peiling", inversedBy="opties")
	 */
	public $peiling;

	/**
	 * @return int
	 * @Serializer\Groups("vue")
	 * @Serializer\SerializedName("stemmen")
	 */
	public function getVueStemmen() {
		$magStemmenZien = ($this->peiling->getHeeftGestemd() || !$this->peiling->getMagStemmen()) && $this->peiling->resultaat_zichtbaar;

		if ($magStemmenZien) {
			return $this->stemmen;
		}

		return 0;
	}

	/**
	 * @return string
	 * @Serializer\Groups("vue")
	 * @DT\DataTableColumn(name="beschrijving")
	 */
	public function getBeschrijvingFormatted() {
		return CsrBB::parse($this->beschrijving);
	}
}
