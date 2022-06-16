<?php

namespace CsrDelft\common\Security\Voter\Entity\Groepen;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Check rechten op groepen aanmaken, aanmelden, verwijderen, etc.
 */
abstract class AbstractGroepenVoter extends Voter
{
	public function mag(AccessAction $action, Groep $groep)
	{
		if (!LoginService::mag(P_LOGGED_IN)) {
			return false;
		}

		if (
			$groep instanceof HeeftAanmeldLimiet &&
			!$groep->magAanmeldLimiet($action)
		) {
			return false;
		}

		if (
			$groep instanceof HeeftAanmeldMoment &&
			!$groep->magAanmeldMoment($action)
		) {
			return false;
		}

		if (
			$groep instanceof HeeftAanmeldRechten &&
			!$groep->magAanmeldRechten($action)
		) {
			return false;
		}

		$aangemeld = $this->getLid(LoginService::getUid()) != null;
		switch ($action) {
			case AccessAction::Aanmelden():
				if ($aangemeld) {
					return false;
				}
				break;

			case AccessAction::Bewerken():
			case AccessAction::Afmelden():
				if (!$aangemeld) {
					return false;
				}
				break;

			default:
				// Maker van groep mag alles
				if ($this->maker->uid === LoginService::getUid()) {
					return true;
				}
				break;
		}
		return static::magAlgemeen($action);
	}
}
