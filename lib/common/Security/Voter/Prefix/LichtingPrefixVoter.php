<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\entity\security\Account;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LichtingPrefixVoter extends PrefixVoter
{
	const PREFIX_LICHTING = 'LICHTING';
	const PREFIX_LIDJAAR = 'LIDJAAR';

	protected function supportsPrefix($prefix)
	{
		return strtoupper((string) $prefix) == self::PREFIX_LICHTING ||
			strtoupper((string) $prefix) == self::PREFIX_LIDJAAR;
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	) {
		/** @var Account $user */
		$user = $token->getUser();

		if (!$user) {
			return false;
		}

		return (string) $user->profiel->lidjaar === $gevraagd;
	}
}
