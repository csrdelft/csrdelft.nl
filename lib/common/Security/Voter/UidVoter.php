<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Check rechten voor een specifieke uid.
 */
class UidVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function supportsAttribute(string $attribute): bool
	{
		return AccountRepository::isValidUid($attribute);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		return $attribute == $token->getUserIdentifier();
	}
}
