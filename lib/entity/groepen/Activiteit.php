<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\ActiviteitSoort;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\model\entity\interfaces\HeeftAanmeldLimiet;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\leden\ActiviteitDeelnemersModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;


/**
 * Activiteit.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\ActiviteitenModel")
 * @ORM\Table("activiteiten")
 */
class Activiteit extends AbstractGroep implements Agendeerbaar, HeeftAanmeldLimiet {
	public function getUUID() {
		return $this->id . '@activiteit.csrdelft.nl';
	}

	const LEDEN = ActiviteitDeelnemersModel::class;
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $aanmelden_tot;
	/**
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var \DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $bewerken_tot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var \DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $afmelden_tot;

	/**
	 * Intern / Extern / SjaarsActie / etc.
	 * @var ActiviteitSoort
	 * @ORM\Column(type="string")
	 */
	public $soort;
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Locatie
	 * @var string
	 */
	public $locatie;
	/**
	 * Tonen in agenda
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $in_agenda;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, ActiviteitSoort::class),
		'rechten_aanmelden' => array(T::String, true),
		'locatie' => array(T::String, true),
		'in_agenda' => array(T::Boolean)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'activiteiten';

	public function getUrl() {
		return '/groepen/activiteiten/' . $this->id;
	}

	/**
	 * Has permission for action?
	 *
	 * @param string $action
	 * @param array|null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmelden:
				if (!empty($this->rechten_aanmelden) AND !LoginModel::mag($this->rechten_aanmelden, $allowedAuthenticationMethods)) {
					return false;
				}
				break;
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param array|null $allowedAuthenticationMethods
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods=null, $soort = null) {
		switch ($soort) {

			case ActiviteitSoort::OWee:
				if (LoginModel::mag('commissie:OWeeCie', $allowedAuthenticationMethods)) {
					return true;
				}
				break;

			case ActiviteitSoort::Dies:
				if (LoginModel::mag('commissie:DiesCie', $allowedAuthenticationMethods)) {
					return true;
				}
				break;

			case ActiviteitSoort::Lustrum:
				if (LoginModel::mag('commissie:LustrumCie', $allowedAuthenticationMethods)) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

	// Agendeerbaar:

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
		return $this->naam;
	}

	public function getBeschrijving() {
		return $this->samenvatting;
	}

	public function getLocatie() {
		return $this->locatie;
	}

	public function isHeledag() {
		$begin = date('H:i', $this->getBeginMoment());
		$eind = date('H:i', $this->getEindMoment());
		return $begin == '00:00' AND ($eind == '23:59' OR $eind == '00:00');
	}

	public function isTransparant() {
		// Toon als transparant (vrij) als lid dat wil, activiteit hele dag(en) duurt of lid niet ingeketzt is
		return lid_instelling('agenda', 'transparantICal') === 'ja' ||
			$this->isHeledag() ||
			$this->getLid(LoginModel::getUid()) === false;
	}

	public function getAanmeldLimiet() {
		return $this->aanmeld_limiet;
	}
}
