<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
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
class Ketzer extends Groep implements HeeftAanmeldLimiet, HeeftAanmeldMoment
{
	use GroepAanmeldMoment;
	use GroepAanmeldLimiet;

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

	public function getUrl()
	{
		return '/groepen/ketzers/' . $this->id;
	}

	public function getAanmeldLimiet()
	{
		return $this->aanmeldLimiet;
	}
}
