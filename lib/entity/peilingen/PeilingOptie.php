<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use Peiling;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\view\bbcode\CsrBB;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Entity(repositoryClass: PeilingOptiesRepository::class)]
#[ORM\Table('peiling_optie')]
#[ORM\Index(name: 'optie', columns: ['titel'])]
class PeilingOptie implements DataTableEntry
{
	/**
	 * Primary key
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * Foreign key
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'integer')]
	public $peiling_id;
	/**
	 * Titel
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'string')]
	public $titel;
	/**
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'text', nullable: true)]
	public $beschrijving;
	/**
	 * Aantal stemmen
	 * @var int
	 */
	#[Serializer\Groups(['datatable'])]
	#[ORM\Column(type: 'integer')]
	public $stemmen = 0;
	/**
	 * @var string
	 */
	#[Serializer\Groups(['datatable'])]
	#[ORM\Column(type: 'uid', nullable: true)]
	public $ingebracht_door;
	/**
	 * @var Peiling
	 */
	#[ORM\ManyToOne(targetEntity: Peiling::class, inversedBy: 'opties')]
	public $peiling;

	/**
	 * @return int
	 */
	#[Serializer\Groups('vue')]
	#[Serializer\SerializedName('stemmen')]
	public function getVueStemmen()
	{
		$magStemmenZien =
			($this->peiling->getHeeftGestemd() || !$this->peiling->getMagStemmen()) &&
			$this->peiling->resultaat_zichtbaar;

		if ($magStemmenZien) {
			return $this->stemmen;
		}

		return 0;
	}

	/**
	 * @return string
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	public function getBeschrijvingFormatted()
	{
		return CsrBB::parse($this->beschrijving);
	}
}
