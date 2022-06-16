<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\security\Account;
use CsrDelft\model\entity\LidStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Heeft het lid deze status?
 */
class LidStatusVoter extends PrefixVoter
{
	public function supportsPrefix($attribute): bool
	{
		return $attribute === 'STATUS';
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

		$gevraagd = 'S_' . strtoupper($gevraagd);

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
