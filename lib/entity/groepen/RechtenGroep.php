<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;

/**
 * RechtenGroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep beperkt voor rechten.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\RechtenGroepenRepository")
 */
class RechtenGroep extends Groep
{
	use GroepAanmeldRechten;

	public function getUrl()
	{
		return '/groepen/overig/' . $this->id;
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
		switch ($action) {
			case AccessAction::Aanmaken():
			case AccessAction::Aanmelden():
			case AccessAction::Bewerken():
			case AccessAction::Afmelden():
				return true;
		}
		return parent::magAlgemeen($action, $soort);
	}

	public function mag(AccessAction $action)
	{
		if (AccessAction::isBekijken($action)) {
			return true;
		}
		return parent::mag($action);
	}
}
