<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\eetplan\EetplanRepository::class
	)
]
#[ORM\Table]
#[
	ORM\UniqueConstraint(name: 'noviet_woonoord', columns: ['uid', 'woonoord_id'])
]
class Eetplan implements DataTableEntry
{
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var Woonoord
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\groepen\Woonoord::class)]
	public $woonoord;

	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'date_immutable', nullable: true)]
	public $avond;
	/**
	 * Specifiek bedoelt voor bekende huizen.
	 *
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string', nullable: true)]
	public $opmerking;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $noviet;

	/**
	 * @return string
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('woonoord')]
	public function getDataTableWoonoord()
	{
		return $this->woonoord->naam;
	}

	/**
	 * @return string
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('naam')]
	public function getDataTableNaam()
	{
		return $this->noviet->getNaam();
	}

	/**
	 * @return string
	 */
	#[Serializer\Groups('datatable')]
	#[Serializer\SerializedName('avond')]
	public function getDataTableAvond()
	{
		if ($this->avond) {
			return DateUtil::dateFormatIntl($this->avond, DateUtil::DATE_FORMAT);
		} else {
			return null;
		}
	}
}
