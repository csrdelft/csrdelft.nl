<?php

namespace CsrDelft\common\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Hulpklasse om rechten te checken
 *
 * Controleert een rechtendefinitie met de volgende structuur:
 *
 * <prefix>:<gevraagd>:<role>
 *
 * Bijvoorbeeld:
 *
 * bestuur:ht:abactis
 */
abstract class PrefixVoter extends Voter
{
	abstract protected function supportsPrefix($prefix);

	abstract protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	);

	protected function supports(string $attribute, $subject)
	{
		// splits permissie in type, waarde en rol
		$p = explode(':', $attribute, 3);
		if (isset($p[0])) {
			$prefix = $p[0];
		} else {
			return false;
		}
		return $this->supportsPrefix($prefix);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		// splits permissie in type, waarde en rol
		$p = explode(':', $attribute, 3);
		if (isset($p[0])) {
			$prefix = $p[0];
		} else {
			return false;
		}
		$gevraagd = $p[1] ?? false;
		$role = $p[2] ?? false;

		return $this->voteOnPrefix($prefix, $gevraagd, $role, $subject, $token);
	}
}
