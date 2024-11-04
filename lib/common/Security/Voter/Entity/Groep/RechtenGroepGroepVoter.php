<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\RechtenGroep;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RechtenGroepGroepVoter extends AbstractGroepVoter
{
	/**
	 * @return string
	 *
	 * @psalm-return RechtenGroep::class
	 */
	protected function getGroepType(): string
	{
		return RechtenGroep::class;
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
