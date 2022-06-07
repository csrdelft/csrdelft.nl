<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\profiel\Profiel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class GeslachtPrefixVoter extends PrefixVoter
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
	}

	protected function supportsPrefix($prefix)
	{
		return $prefix == 'geslacht';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	) {
		/** @var Profiel $profiel */
		$profiel = $token->getUser()->profiel;

		if (
			$profiel->geslacht &&
			$gevraagd == strtoupper($profiel->geslacht->getValue())
		) {
			// Niet ingelogd heeft geslacht m dus check of ingelogd
			if ($this->security->isGranted('ROLE_LOGGED_IN')) {
				return true;
			}
		}

		return false;
	}
}
