<?php

namespace CsrDelft\entity\agenda;

use CsrDelft\repository\agenda\AgendaRepository;
use DateInterval;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * AgendaItem.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * AgendaItems worden door de agenda getoont samen met andere Agendeerbare dingen.
 */
#[ORM\Table('agenda')]
#[ORM\Index(name: 'begin_moment', columns: ['begin_moment'])]
#[ORM\Index(name: 'eind_moment', columns: ['eind_moment'])]
#[ORM\Entity(repositoryClass: AgendaRepository::class)]
class AgendaItem implements Agendeerbaar
{
	/**
  * Primary key
  * @var int
  */
 #[ORM\Id]
 #[ORM\Column(type: 'integer')]
 #[ORM\GeneratedValue]
 public $item_id;
	/**
  * Titel
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $titel;
	/**
  * Beschrijving
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 public $beschrijving;
	/**
  * DateTime begin
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 public $begin_moment;
	/**
  * DateTime eind
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 public $eind_moment;
	/**
  * Permissie voor tonen
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $rechten_bekijken;
	/**
  * Locatie
  * @var string
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $locatie;
	/**
  * Link
  * @var string
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $link;

	public function getBeginMoment(): DateTimeImmutable
	{
		return $this->begin_moment;
	}

	public function getEindMoment(): DateTimeImmutable
	{
		if ($this->eind_moment && $this->eind_moment !== $this->begin_moment) {
			return $this->eind_moment;
		}
		return $this->getBeginMoment()->add(new DateInterval('PT30M'));
	}

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBeschrijving()
	{
		return $this->beschrijving;
	}

	public function getLocatie()
	{
		return $this->locatie;
	}

	public function getUrl()
	{
		return $this->link;
	}

	public function isHeledag(): bool
	{
		$begin = $this->getBeginMoment()->format('H:i');
		$eind = $this->getEindMoment()->format('H:i');
		return $begin == '00:00' && ($eind == '23:59' || $eind == '00:00');
	}

	public function magBekijken($ical = false)
	{
		$auth = $ical ? AuthenticationMethod::getEnumValues() : null;
		return LoginService::mag($this->rechten_bekijken, $auth);
	}

	public function magBeheren($ical = false): bool
	{
		$auth = $ical ? AuthenticationMethod::getEnumValues() : null;
		if (LoginService::mag(P_AGENDA_MOD, $auth)) {
			return true;
		}
		$verticale = 'verticale:' . LoginService::getProfiel()->verticale;
		if (
			$this->rechten_bekijken === $verticale and
			LoginService::getProfiel()->verticaleleider
		) {
			return true;
		}
		return false;
	}

	public function isTransparant(): bool
	{
		// Toon als transparant (vrij) als lid dat wil of activiteit hele dag(en) duurt
		return InstellingUtil::lid_instelling('agenda', 'transparantICal') ===
			'ja' || $this->isHeledag();
	}

	public function getUUID(): string
	{
		return strtolower(
			sprintf(
				'%s@%s.csrdelft.nl',
				implode('.', [$this->item_id]),
				ReflectionUtil::short_class($this)
			)
		);
	}
}
