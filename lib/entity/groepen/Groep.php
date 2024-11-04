<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\repository\GroepRepository;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'groep_type', type: 'string')]
#[
	ORM\DiscriminatorMap([
		'groep' => 'Groep',
		'activiteit' => 'Activiteit',
		'bestuur' => 'Bestuur',
		'commissie' => 'Commissie',
		'ketzer' => 'Ketzer',
		'kring' => 'Kring',
		'lichting' => 'Lichting',
		'ondervereniging' => 'Ondervereniging',
		'rechtengroep' => 'RechtenGroep',
		'verticale' => 'Verticale',
		'werkgroep' => 'Werkgroep',
		'woonoord' => 'Woonoord',
	])
]
#[ORM\Table('groep')]
#[ORM\Index(columns: ['in_agenda'])]
#[ORM\Index(columns: ['familie'])]
#[ORM\Index(columns: ['begin_moment'])]
#[ORM\Index(columns: ['huis_status'])]
#[ORM\Index(columns: ['ondervereniging_status'])]
#[ORM\Index(columns: ['activiteit_soort'])]
#[ORM\Index(columns: ['commissie_soort'])]
#[ORM\Index(columns: ['eetplan'])]
#[ORM\Index(columns: ['kring_nummer'])]
#[ORM\Index(columns: ['verticale'])]
#[ORM\Index(columns: ['groep_type'])]
class Groep implements DataTableEntry, DisplayEntity
{
	/**
	 * Primary key, groter dan 3000 in de database
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * Oude ID, uniek voor type groep, kleiner dan 3000 in de database (sorry)
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'integer', nullable: true)]
	public $oudId;

	/**
	 * Naam
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'stringkey')]
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'stringkey')]
	public $familie;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'enumGroepStatus')]
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'text')]
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'text', nullable: true)]
	public $omschrijving;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $keuzelijst;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[
		ORM\JoinColumn(
			name: 'maker_uid',
			referencedColumnName: 'uid',
			nullable: false
		)
	]
	public $maker;
	/**
	 * @var GroepVersie
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'enumGroepVersie')]
	public $versie;
	/**
	 * @var GroepKeuze[]
	 */
	#[Serializer\Groups('vue')]
	#[ORM\Column(type: 'groepkeuze', nullable: true)]
	public $keuzelijst2 = [];
	/**
	 * Gebruik @see Groep::getLeden om leden op te vragen.
	 * @var GroepLid[]|ArrayCollection
	 */
	#[ORM\OneToMany(targetEntity: \GroepLid::class, mappedBy: 'groep')]
	#[ORM\OrderBy(['lidSinds' => 'ASC'])]
	#[ORM\JoinColumn(name: 'groep_id', referencedColumnName: 'id')]
	protected $leden;

	public function __construct()
	{
		$this->versie = GroepVersie::V1();
		$this->leden = new ArrayCollection();
	}

	/**
	 * De URL van de groep
	 * @return string
	 */
	public function getUrl()
	{
		return '/groepen/groep/' . $this->id;
	}

	/**
	 * @psalm-return int<0, max>
	 */
	public function aantalLeden(): int
	{
		return $this->getLeden()->count();
	}

	/**
	 * Maak het mogelijk om leden te 'faken', zie verticale/lichting
	 * @return GroepLid[]|ArrayCollection
	 */
	#[Serializer\Groups('vue')]
	public function getLeden()
	{
		return $this->leden;
	}

	public function getFamilieSuggesties(): array
	{
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		/** @var GroepRepository $repo */
		$repo = $em->getRepository(static::class);

		$result = $repo
			->createQueryBuilder('g')
			->select('DISTINCT g.familie')
			->getQuery()
			->getScalarResult();

		return array_map(fn($e) => $e['familie'], $result);
	}

	/**
	 * @return (CommissieFunctie|mixed|string)[]
	 *
	 * @psalm-return array<CommissieFunctie|mixed|string>
	 */
	public function getOpmerkingSuggesties(): array
	{
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie || $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getEnumValues();
		} else {
			$suggesties = array_unique(
				$this->getLeden()
					->map(fn(GroepLid $lid) => $lid->opmerking)
					->toArray()
			);
		}
		return $suggesties;
	}

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return GroepLid|null
	 */
	public function getLid($uid)
	{
		if ($this->getLeden() == null) {
			return null;
		}

		return $this->getLeden()
			->matching(Eisen::voorGebruiker($uid))
			->first();
	}

	/**
	 * @return string
	 */
	public function getUUID()
	{
		return $this->id .
			'@' .
			ReflectionUtil::short_class($this) .
			'.csrdelft.nl';
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->naam ?? '';
	}
}
