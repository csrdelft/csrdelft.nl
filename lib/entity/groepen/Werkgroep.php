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
	use GroepMoment;

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
		if ($action === AccessAction::Aanmaken && !LoginService::mag(P_LEDEN_MOD)) {
			return false;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

}
