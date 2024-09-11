<?php

namespace CsrDelft\common\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * ROLE_PUBLIC is een synoniem van PUBLIC_ACCESS
 */
class RolePublicVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function __construct(private Security $security)
	{
	}

	public function supportsAttribute(string $attribute): bool
	{
		return strtoupper($attribute) == 'ROLE_PUBLIC' ||
			strtoupper($attribute) == 'ROLE_FORUM_READ' ||
			strtoupper($attribute) == 'ROLE_ALBUM_PUBLIC_READ';
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		return true;
	}
}
