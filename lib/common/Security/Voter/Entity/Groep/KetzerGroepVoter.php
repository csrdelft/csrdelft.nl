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

	protected function magAlgemeen(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		return match ($attribute) {
			self::AANMAKEN, self::AANMELDEN, self::BEWERKEN, self::AFMELDEN => true,
			default => parent::magAlgemeen($attribute, $subject, $token),
		};
	}
}
