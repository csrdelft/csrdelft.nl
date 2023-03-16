<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\RechtenGroep;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RechtenGroepGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return RechtenGroep::class;
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
