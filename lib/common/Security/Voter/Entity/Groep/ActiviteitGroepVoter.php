<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ActiviteitGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Activiteit::class;
	}

	protected function magAlgemeen(string $attribute, TokenInterface $token): bool
	{
		switch ($attribute) {
			case self::AANMAKEN:
			case self::AANMELDEN:
			case self::BEWERKEN:
			case self::AFMELDEN:
				return true;
			default:
				return parent::magAlgemeen($attribute, $token);
		}
	}
}
