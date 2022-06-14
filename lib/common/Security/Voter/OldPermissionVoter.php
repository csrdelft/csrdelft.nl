<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\common\CsrException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Gooi een exception als er onverhoopt nog een oude P_.... permissie langs komt vliegen.
 */
class OldPermissionVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function supportsAttribute(string $attribute): bool
	{
		return preg_match('/^P_[a-zA-Z_]+$/', $attribute);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		throw new CsrException("Rechten voor '$attribute' niet te verwerken!");
	}
}
