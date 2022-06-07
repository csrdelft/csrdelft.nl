<?php

namespace CsrDelft\common\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

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
		if (isset($p[1])) {
			$gevraagd = $p[1];
		} else {
			$gevraagd = false;
		}
		if (isset($p[2])) {
			$role = $p[2];
		} else {
			$role = false;
		}

		return $this->voteOnPrefix($prefix, $gevraagd, $role, $subject, $token);
	}
}
