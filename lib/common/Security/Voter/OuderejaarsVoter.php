<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\security\Account;
use CsrDelft\repository\groepen\LichtingenRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Is de gebruiker ouderejaars?
 */
class OuderejaarsVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function supportsAttribute(string $attribute): bool
	{
		return strtoupper($attribute) == 'OUDEREJAARS';
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		/** @var ?Account $user */
		$user = $token->getUser();
		if (!$user) {
			return false;
		}

		if ($user->profiel->lidjaar === LichtingenRepository::getJongsteLidjaar()) {
			return false;
		}
		return true;
	}
}
