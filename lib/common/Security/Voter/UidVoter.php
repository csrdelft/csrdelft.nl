<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UidVoter extends Voter
{
	use CacheableVoterTrait;

	public function supportsAttribute(string $attribute): bool
	{
		return AccountRepository::isValidUid($attribute);
	}

	public function supportsType(string $subjectType): bool
	{
		return in_array(UserInterface::class, class_implements($subjectType));
	}

	/**
	 * @param string $attribute
	 * @param UserInterface $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		return $attribute == $subject->getUserIdentifier();
	}
}
