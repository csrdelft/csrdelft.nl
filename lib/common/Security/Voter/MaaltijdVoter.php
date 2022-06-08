<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\common\CsrException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

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
	 * @var Security
	 */
	private $security;

	public function __construct(EntityManagerInterface $em, Security $security)
	{
		$this->em = $em;
		$this->security = $security;
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
			if ($this->security->isGranted('ROLE_MAAL_MOD', $subject)) {
				return true;
			}
			try {
				$maaltijd = $this->em
					->getRepository(Maaltijd::class)
					->getMaaltijd((int) $gevraagd);
				if ($maaltijd && $maaltijd->magSluiten($profiel->uid)) {
					return true;
				}
			} catch (CsrException $e) {
				// Maaltijd bestaat niet
			}
		}

		return false;
	}
}
