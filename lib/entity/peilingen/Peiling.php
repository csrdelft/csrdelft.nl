<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\datatable\DataTableColumn;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Table('peiling')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\peilingen\PeilingenRepository::class)]
class Peiling implements DataTableEntry
{
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;
	/**
  * @var string
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'string')]
 public $titel;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $beschrijving;
	/**
  * @var string
  * @Serializer\Groups("vue")
  */
 #[ORM\Column(type: 'uid', nullable: true)]
 public $eigenaar;
	/**
  * @var boolean
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'boolean', options: ['default' => false])]
 public $mag_bewerken;
	/**
  * @var boolean
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'boolean', options: ['default' => true])]
 public $resultaat_zichtbaar;
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'integer', options: ['default' => 0])]
 public $aantal_voorstellen;
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'integer', options: ['default' => 1])]
 public $aantal_stemmen;
	/**
  * @var string|null
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $rechten_stemmen;
	/**
  * @var string|null
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $rechten_mod;
	/**
  * @var DateTimeImmutable|null
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'datetime', nullable: true)]
 public $sluitingsdatum;

	/**
  * @var PeilingOptie[]|ArrayCollection
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\OneToMany(targetEntity: \PeilingOptie::class, mappedBy: 'peiling')]
 public $opties;

	/**
  * @var PeilingStem[]|ArrayCollection
  */
 #[ORM\JoinColumn(name: 'id', referencedColumnName: 'peiling_id')]
 #[ORM\OneToMany(targetEntity: \PeilingStem::class, mappedBy: 'peiling')]
 public $stemmen;

	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'eigenaar', referencedColumnName: 'uid', nullable: true)]
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
 public $eigenaarProfiel;

	/**
	 * @return int
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public function getAantalGestemd(): int
	{
		if (!$this->opties) {
			return 0;
		}

		$aantalGestemd = 0;
		foreach ($this->opties as $optie) {
			$aantalGestemd += $optie->stemmen;
		}
		return $aantalGestemd;
	}

	/**
	 * @Serializer\Groups("vue")
	 */
	public function getHeeftGestemd(): bool
	{
		return (bool) $this->stemmen
			->matching(Eisen::voorIngelogdeGebruiker())
			->first();
	}

	public function getStem(Profiel $profiel): mixed
	{
		return $this->stemmen
			->matching(Eisen::voorGebruiker($profiel->uid))
			->first();
	}

	/**
	 * @return bool
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public function getMagBewerken()
	{
		return ContainerFacade::getContainer()
			->get('security')
			->isGranted('bewerken', $this);
	}

	/**
	 * @return bool
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public function getIsMod(): bool
	{
		return $this->getMagBewerken();
	}

	/**
	 * @return bool
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public function getMagStemmen()
	{
		return ContainerFacade::getContainer()
			->get('security')
			->isGranted('stemmen', $this);
	}

	/**
	 * @return DataTableColumn|string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("eigenaar")
	 */
	public function getDataTableEigenaar()
	{
		return $this->eigenaarProfiel
			? $this->eigenaarProfiel->getDataTableColumn()
			: '';
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("detailSource")
	 */
	public function getDetailSource(): string
	{
		return '/peilingen/opties/' . $this->id;
	}

	/**
	 * @return bool
	 */
	public function isPeilingOpen(): bool
	{
		return $this->sluitingsdatum == null ||
			time() < $this->sluitingsdatum->getTimestamp();
	}

	/**
	 * @return bool
	 */
	public function magBekijken()
	{
		return ContainerFacade::getContainer()
			->get('security')
			->isGranted('bekijken', $this);
	}

	/**
	 * @return string|null
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public function getBeschrijving(): string
	{
		return CsrBB::parse($this->beschrijving);
	}
}
