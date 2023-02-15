<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Is een lid aangemeld voor een bepaalde maaltijd?
 */
class MaaltijdVoter extends PrefixVoter
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $accessDecisionManager;

	public function __construct(
		EntityManagerInterface $em,
		AccessDecisionManagerInterface $accessDecisionManager
	) {
		$this->em = $em;
		$this->accessDecisionManager = $accessDecisionManager;
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper($prefix) == 'MAALTIJD';
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

		if (!is_numeric($gevraagd)) {
			return false;
		}

		$maaltijd = $this->em->getRepository(Maaltijd::class)->find($gevraagd);

		if (!$maaltijd) {
			return false;
		}

		$aanmelding = $maaltijd->getAanmelding($profiel);

		// Aangemeld voor maaltijd?
		if (!$role && $aanmelding) {
			return true;
		} elseif ($role === 'SLUITEN') {
			// Mag maaltijd sluiten?
			if ($this->accessDecisionManager->decide($token, ['ROLE_MAAL_MOD'])) {
				return true;
			}

			if ($maaltijd->magSluiten($profiel->uid)) {
				return true;
			}
		}

		return false;
	}
}
