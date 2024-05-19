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
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'noviet1_noviet2', columns: ['uid1', 'uid2'])]
#[ORM\Entity(repositoryClass: EetplanBekendenRepository::class)]
class EetplanBekenden implements DataTableEntry
{
	/**
  * @var int
  */
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 #[Serializer\Groups('datatable')]
 public $id;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid1', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $noviet1;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid2', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $noviet2;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string', nullable: true)]
 #[Serializer\Groups('datatable')]
 public $opmerking;

	/**
  * @return DataTableColumn
  */
 #[Serializer\SerializedName('noviet1')]
 #[Serializer\Groups('datatable')]
 public function getDataTableNoviet1(): DataTableColumn
	{
		return $this->noviet1->getDataTableColumn();
	}

	/**
  * @return DataTableColumn
  */
 #[Serializer\SerializedName('noviet2')]
 #[Serializer\Groups('datatable')]
 public function getDataTableNoviet2(): DataTableColumn
	{
		return $this->noviet2->getDataTableColumn();
	}
}
