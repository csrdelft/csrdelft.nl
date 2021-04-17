<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Werkgroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\WerkgroepenRepository")
 */
class Werkgroep extends Groep {
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 * @Groups({"datatable", "log", "vue"})
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

	public function getUrl() {
		return '/groepen/werkgroepen/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null) {
		if ($action === AccessAction::Aanmaken AND !LoginService::mag(P_LEDEN_MOD)) {
			return false;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

}
