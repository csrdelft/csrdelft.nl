<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\security\enum\AccessAction;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ketzer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ketzer is een aanmeldbare groep.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KetzersRepository")
 */
class Ketzer extends Groep implements HeeftAanmeldLimiet
{
	use GroepAanmelden {
		GroepAanmelden::mag as magAanmelden;
	}
	use GroepAanmeldLimiet {
		GroepAanmeldLimiet::mag as magAanmeldLimiet;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null)
	{
		switch ($action) {

			case AccessAction::Aanmaken:
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return true;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

	public function getUrl()
	{
		return '/groepen/ketzers/' . $this->id;
	}


	public function getAanmeldLimiet()
	{
		return $this->aanmeldLimiet;
	}

	public function mag($action, $allowedAuthenticationMethods = null)
	{
		return $this->magAanmelden($action, $allowedAuthenticationMethods)
			&& $this->magAanmeldLimiet($action, $allowedAuthenticationMethods);
	}
}
