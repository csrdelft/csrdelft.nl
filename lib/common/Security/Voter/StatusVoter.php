<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\security\Account;
use CsrDelft\model\entity\LidStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StatusVoter extends Voter
{
	use CacheableVoterTrait;

	public function supportsAttribute(string $attribute): bool
	{
		return LidStatus::isValidValue('S_' . strtoupper($attribute));
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		/** @var Account $user */
		$user = $token->getUser();

		if (!$user) {
			return false;
		}

		$profiel = $user->profiel;

		$gevraagd = 'S_' . $attribute;

		if ($gevraagd == $profiel->status) {
			return true;
		} elseif (
			$gevraagd == LidStatus::Lid &&
			LidStatus::isLidLike($profiel->status)
		) {
			return true;
		} elseif (
			$gevraagd == LidStatus::Oudlid &&
			LidStatus::isOudlidLike($profiel->status)
		) {
			return true;
		}

		return false;
	}
}
