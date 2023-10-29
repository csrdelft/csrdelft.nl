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
		switch ($attribute) {
			case self::BEKIJKEN:
			case self::AANMAKEN:
			case self::WIJZIGEN:
				return parent::voteOnAttribute($attribute, $subject, $token);
			default:
				return false;
		}
	}
}
