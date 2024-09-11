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
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\ActiviteitenRepository")
 */
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
	 * @ORM\Column(type="enumActiviteitSoort")
	 */
	#[Serializer\Groups('datatable')]
	public $activiteitSoort;
	/**
	 * Locatie
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	#[Serializer\Groups('datatable')]
	public $locatie;
	/**
	 * Tonen in agenda
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	#[Serializer\Groups('datatable')]
	public $inAgenda;

	public function getUUID()
	{
		return $this->id . '@activiteit.csrdelft.nl';
	}

	public function getUrl()
	{
		return '/groepen/activiteiten/' . $this->id;
	}

	// Agendeerbaar:

	public function getTitel()
	{
		return $this->naam;
	}

	public function getBeschrijving()
	{
		return $this->samenvatting;
	}

	public function getLocatie()
	{
		return $this->locatie;
	}

	public function isTransparant()
	{
		// Toon als transparant (vrij) als lid dat wil, activiteit hele dag(en) duurt of lid niet ingeketzt is
		return InstellingUtil::lid_instelling('agenda', 'transparantICal') ===
			'ja' ||
			$this->isHeledag() ||
			!$this->getLid(LoginService::getUid());
	}

	public function isHeledag()
	{
		$begin = $this->getBeginMoment()->format('H:i');
		$eind = $this->getEindMoment()->format('H:i');
		return $begin == '00:00' && ($eind == '23:59' || $eind == '00:00');
	}

	public function getAanmeldLimiet()
	{
		return $this->aanmeldLimiet;
	}

	public function getSoort()
	{
		return $this->activiteitSoort;
	}

	public function setSoort($soort)
	{
		$this->activiteitSoort = $soort;
	}

	public function setSoortString($soort)
	{
		$this->activiteitSoort = ActiviteitSoort::from($soort);
	}
}
