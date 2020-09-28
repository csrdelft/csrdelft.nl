<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\security\enum\AccessAction;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ketzer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ketzer is een aanmeldbare groep.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KetzersRepository")
 * @ORM\Table("ketzers", indexes={
 *   @ORM\Index(name="status", columns={"status"}),
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="familie", columns={"familie"}),
 * })
 */
class Ketzer extends AbstractGroep implements HeeftAanmeldLimiet {
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
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
	 * @var KetzerDeelnemer
	 * @ORM\OneToMany(targetEntity="KetzerDeelnemer", mappedBy="groep")
	 * @ORM\OrderBy({"lid_sinds"="ASC"})
	 */
	public $leden;

	public function __construct() {
		parent::__construct();
		$this->leden = new ArrayCollection();
	}

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

	public function getLidType() {
		return KetzerDeelnemer::class;
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
