<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\security\enum\AccessAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VerticaleGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Verticale::class;
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		return match ($attribute) {
			self::BEKIJKEN, self::AANMAKEN, self::WIJZIGEN => parent::voteOnAttribute(
				$attribute,
				$subject,
				$token
			),
			default => false,
		};
	}
}
