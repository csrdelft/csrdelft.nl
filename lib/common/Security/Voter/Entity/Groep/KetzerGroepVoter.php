<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\security\enum\AccessAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class KetzerGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Ketzer::class;
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
