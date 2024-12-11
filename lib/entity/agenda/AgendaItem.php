<?php

namespace CsrDelft\entity\agenda;

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
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\agenda\AgendaRepository::class
	)
]
#[ORM\Table('agenda')]
#[ORM\Index(name: 'begin_moment', columns: ['begin_moment'])]
#[ORM\Index(name: 'eind_moment', columns: ['eind_moment'])]
class AgendaItem implements Agendeerbaar
{
	/**
	 * Primary key
	 * @var int
	 */
	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	#[ORM\GeneratedValue]
	public int $item_id;
	#[ORM\Column(type: 'string')]
	public string $titel;
	#[ORM\Column(type: 'text', nullable: true)]
	public ?string $beschrijving;
	#[ORM\Column(type: 'datetime')]
	public DateTimeImmutable $begin_moment;
	#[ORM\Column(type: 'datetime')]
	public DateTimeImmutable $eind_moment;
	#[ORM\Column(type: 'string')]
	public string $rechten_bekijken;
	#[ORM\Column(type: 'string', nullable: true)]
	public ?string $locatie;
	#[ORM\Column(type: 'string', nullable: true)]
	public ?string $link;

	public function getBeginMoment(): DateTimeImmutable
	{
		return $this->begin_moment;
	}

	public function getEindMoment(): DateTimeImmutable
	{
		if ($this->eind_moment && $this->eind_moment !== $this->begin_moment) {
			return $this->eind_moment;
		}
		return $this->getBeginMoment()->add(new \DateInterval('PT30M'));
	}

	public function getTitel(): string
	{
		return $this->titel;
	}

	public function getBeschrijving(): ?string
	{
		return $this->beschrijving;
	}

	public function getLocatie(): ?string
	{
		return $this->locatie;
	}

	public function getUrl(): ?string
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
		return LoginService::mag($this->rechten_bekijken);
	}

	public function magBeheren($ical = false)
	{
		$auth = $ical ? AuthenticationMethod::getEnumValues() : null;
		if (LoginService::mag(P_AGENDA_MOD)) {
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
