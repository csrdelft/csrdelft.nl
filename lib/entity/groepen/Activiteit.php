<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Activiteit.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\groepen\ActiviteitenRepository::class
	)
]
class Activiteit extends Groep implements
	Agendeerbaar,
	HeeftAanmeldLimiet,
	HeeftAanmeldRechten,
	HeeftAanmeldMoment,
	HeeftMoment,
	HeeftSoort
{
	use GroepMoment;
	use GroepAanmeldMoment;
	use GroepAanmeldRechten;
	use GroepAanmeldLimiet;

	/**
	 * Intern / Extern / SjaarsActie / etc.
	 * @var ActiviteitSoort
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'enumActiviteitSoort')]
	public $activiteitSoort;
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string', nullable: true)]
	public ?string $locatie;
	/**
	 * Tonen in agenda
	 * @var boolean
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'boolean')]
	public $inAgenda;

	/**
	 * @return string
	 */
	public function getUUID(): string
	{
		return $this->id . '@activiteit.csrdelft.nl';
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return '/groepen/activiteiten/' . $this->id;
	}

	// Agendeerbaar:

	public function getTitel(): string
	{
		return $this->naam;
	}

	/**
	 * @return string
	 */
	public function getAanmeldLimiet()
	{
		return $this->aanmeldLimiet;
	}

	/**
	 * @return ActiviteitSoort
	 */
	public function getSoort()
	{
		return $this->activiteitSoort;
	}

	/**
	 * @return void
	 */
	public function setSoort($soort)
	{
		$this->activiteitSoort = $soort;
	}
}
