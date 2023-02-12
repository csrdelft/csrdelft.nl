<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;

/**
 * Werkgroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\WerkgroepenRepository")
 */
class Werkgroep extends Groep implements HeeftMoment
{
	use GroepMoment;

	public function getUrl()
	{
		return '/groepen/werkgroepen/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param null $soort
	 * @return boolean
	 */
	public static function magAlgemeen(AccessAction $action, $soort = null)
	{
		if (AccessAction::isAanmaken($action) && !LoginService::mag(P_LEDEN_MOD)) {
			return false;
		}
		return parent::magAlgemeen($action, $soort);
	}
}
