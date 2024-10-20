<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\entity\profiel\Profiel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Is de gebruiker man of vrouw?
 */
class GeslachtPrefixVoter extends PrefixVoter
{
	public function __construct(
		private readonly AccessDecisionManagerInterface $accessDecisionManager
	) {
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper((string) $prefix) === 'GESLACHT';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	): bool {
		// Niet ingelogd heeft geslacht m dus check of ingelogd
		if (!$this->accessDecisionManager->decide($token, ['ROLE_LOGGED_IN'])) {
			return false;
		}

		/** @var Profiel $profiel */
		$profiel = $token->getUser()->profiel;

		return $profiel->geslacht &&
			$gevraagd == strtoupper((string) $profiel->geslacht->getValue());
	}
}
