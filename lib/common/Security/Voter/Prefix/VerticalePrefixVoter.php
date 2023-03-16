<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\entity\security\Account;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 *  Behoort een lid tot een bepaalde verticale?
 */
class VerticalePrefixVoter extends PrefixVoter
{
	protected function supportsPrefix($prefix)
	{
		return strtoupper($prefix) == 'VERTICALE';
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
		$profiel = $user->profiel;
		if (!$profiel->verticale) {
			return false;
		} elseif (
			$profiel->verticale === $gevraagd ||
			$gevraagd == strtoupper($profiel->getVerticale()->naam)
		) {
			if (!$role) {
				return true;
			} elseif ($role === 'LEIDER' && $profiel->verticaleleider) {
				return true;
			}
		}
		return false;
	}
}
