<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\common\CsrException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Is een lid aangemeld voor een bepaalde maaltijd?
 */
class MaaltijdPrefixVoter extends PrefixVoter
{
	public function __construct(
		private readonly EntityManagerInterface $em,
		private readonly AccessDecisionManagerInterface $accessDecisionManager
	) {
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper((string) $prefix) == 'MAALTIJD';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	): bool {
		/** @var Account $user */
		$user = $token->getUser();

		if (!$user) {
			return false;
		}

		$profiel = $user->profiel;

		if (!is_numeric($gevraagd)) {
			return false;
		}
		// Aangemeld voor maaltijd?
		if (
			!$role &&
			$this->em
				->getRepository(MaaltijdAanmelding::class)
				->getIsAangemeld((int) $gevraagd, $profiel->uid)
		) {
			return true;
		} elseif ($role === 'SLUITEN') {
			// Mag maaltijd sluiten?
			if ($this->accessDecisionManager->decide($token, ['ROLE_MAAL_MOD'])) {
				return true;
			}
			try {
				$maaltijd = $this->em
					->getRepository(Maaltijd::class)
					->getMaaltijd((int) $gevraagd);
				if ($maaltijd && $maaltijd->magSluiten($profiel->uid)) {
					return true;
				}
			} catch (CsrException) {
				// Maaltijd bestaat niet
			}
		}

		return false;
	}
}
