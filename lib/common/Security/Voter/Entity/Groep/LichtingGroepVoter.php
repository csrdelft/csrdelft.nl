<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Lichting;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LichtingGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Lichting::class;
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		return $attribute == AbstractGroepVoter::BEKIJKEN;
	}
}
