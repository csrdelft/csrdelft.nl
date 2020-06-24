<?php

namespace CsrDelft\entity\agenda;

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
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\agenda\AgendaRepository")
 * @ORM\Table("agenda", indexes={
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="eind_moment", columns={"eind_moment"})
 * })
 */
class AgendaItem implements Agendeerbaar {

	/**
	 * Primary key
	 * @ORM\Id()
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue()
	 * @var int
	 */
	public $item_id;
	/**
	 * Titel
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $titel;
	/**
	 * Beschrijving
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	public $beschrijving;
	/**
	 * DateTime begin
	 * @ORM\Column(type="datetime")
	 * @var DateTimeImmutable
	 */
	public $begin_moment;
	/**
	 * DateTime eind
	 * @ORM\Column(type="datetime")
	 * @var DateTimeImmutable
	 */
	public $eind_moment;
	/**
	 * Permissie voor tonen
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Locatie
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $locatie;
	/**
	 * Link
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $link;

	public function getBeginMoment() {
		return $this->begin_moment->getTimestamp();
	}

	public function getEindMoment() {
		if ($this->eind_moment AND $this->eind_moment !== $this->begin_moment) {
			return $this->eind_moment->getTimestamp();
		}
		return $this->getBeginMoment() + 1800;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function getLocatie() {
		return $this->locatie;
	}

	public function getUrl() {
		return $this->link;
	}

	public function isHeledag() {
		$begin = date('H:i', $this->getBeginMoment());
		$eind = date('H:i', $this->getEindMoment());
		return $begin == '00:00' AND ($eind == '23:59' OR $eind == '00:00');
	}

	public function magBekijken($ical = false) {
		$auth = ($ical ? AuthenticationMethod::getEnumValues() : null);
		return LoginService::mag($this->rechten_bekijken, $auth);
	}

	public function magBeheren($ical = false) {
		$auth = ($ical ? AuthenticationMethod::getEnumValues() : null);
		if (LoginService::mag(P_AGENDA_MOD, $auth)) {
			return true;
		}
		$verticale = 'verticale:' . LoginService::getProfiel()->verticale;
		if ($this->rechten_bekijken === $verticale AND LoginService::getProfiel()->verticaleleider) {
			return true;
		}
		return false;
	}

	public function isTransparant() {
		// Toon als transparant (vrij) als lid dat wil of activiteit hele dag(en) duurt
		return lid_instelling('agenda', 'transparantICal') === 'ja' || $this->isHeledag();
	}

	public function getUUID() {
		return strtolower(sprintf(
			'%s@%s.csrdelft.nl',
			implode('.', [$this->item_id]),
			short_class($this)
		));
	}
}
