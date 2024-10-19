<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\repository\eetplan\EetplanBekendenRepository;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\datatable\DataTableColumn;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class EetplanBekenden
 * @package CsrDelft\model\entity\eetplan
 */
#[ORM\Entity(repositoryClass: EetplanBekendenRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'noviet1_noviet2', columns: ['uid1', 'uid2'])]
class EetplanBekenden implements DataTableEntry
{
	/**
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public $id;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: Profiel::class)]
	#[ORM\JoinColumn(name: 'uid1', referencedColumnName: 'uid')]
	public $noviet1;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: Profiel::class)]
	#[ORM\JoinColumn(name: 'uid2', referencedColumnName: 'uid')]
	public $noviet2;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string', nullable: true)]
	public $opmerking;

	/**
	 * @return DataTableColumn
	 */
	#[Serializer\SerializedName('noviet1')]
	#[Serializer\Groups('datatable')]
	public function getDataTableNoviet1()
	{
		return $this->noviet1->getDataTableColumn();
	}

	/**
	 * @return DataTableColumn
	 */
	#[Serializer\SerializedName('noviet2')]
	#[Serializer\Groups('datatable')]
	public function getDataTableNoviet2()
	{
		return $this->noviet2->getDataTableColumn();
	}
}
