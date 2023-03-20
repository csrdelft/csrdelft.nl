<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Werkgroep;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WerkgroepGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Werkgroep::class;
	}

	protected function magAlgemeen(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		if (
			$attribute == self::AANMAKEN &&
			!$this->accessDecisionManager->decide($token, ['ROLE_LEDEN_MOD'])
		) {
			return false;
		}

		return parent::magAlgemeen($attribute, $subject, $token);
	}
}
