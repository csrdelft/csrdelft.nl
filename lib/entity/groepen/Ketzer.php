<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\security\enum\AccessAction;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Ketzer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ketzer is een aanmeldbare groep.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KetzersRepository")
 */
class Ketzer extends Groep implements HeeftAanmeldLimiet {
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $aanmelden_tot;
	/**
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $bewerken_tot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $afmelden_tot;

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null) {
		switch ($action) {

			case AccessAction::Aanmaken:
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return true;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

	public function getLeden() {
		return $this->leden;
	}

	public function getUrl() {
		return '/groepen/ketzers/' . $this->id;
	}

	/**
	 * Has permission for action?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		$nu = date_create_immutable();

		switch ($action) {
			case AccessAction::Aanmelden:
				// Controleer maximum leden
				if (isset($this->aanmeld_limiet) and $this->aantalLeden() >= $this->aanmeld_limiet) {
					return false;
				}
				// Controleer aanmeldperiode
				if ($nu > $this->aanmelden_tot || $nu < $this->aanmelden_vanaf) {
					return false;
				}
				break;

			case AccessAction::Bewerken:
				// Controleer bewerkperiode
				if ($nu > $this->bewerken_tot) {
					return false;
				}
				break;

			case AccessAction::Afmelden:
				// Controleer afmeldperiode
				if ($nu > $this->afmelden_tot) {
					return false;
				}
				break;
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}

	public function getAanmeldLimiet() {
		return $this->aanmeld_limiet;
	}
}
