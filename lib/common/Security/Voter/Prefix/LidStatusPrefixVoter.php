<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\entity\security\Account;
use CsrDelft\model\entity\LidStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Heeft het lid deze status?
 */
class LidStatusPrefixVoter extends PrefixVoter
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

		$gevraagd = 'S_' . strtoupper((string) $gevraagd);

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
