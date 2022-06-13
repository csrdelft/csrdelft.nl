<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\profiel\Profiel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Is de gebruiker man of vrouw?
 */
class GeslachtPrefixVoter extends PrefixVoter
{
	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $accessDecisionManager;

	public function __construct(
		AccessDecisionManagerInterface $accessDecisionManager
	) {
		$this->accessDecisionManager = $accessDecisionManager;
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper($prefix) == 'GESLACHT';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	) {
		// Niet ingelogd heeft geslacht m dus check of ingelogd
		if (!$this->accessDecisionManager->decide($token, ['ROLE_LOGGED_IN'])) {
			return false;
		}

		/** @var Profiel $profiel */
		$profiel = $token->getUser()->profiel;

		return $profiel->geslacht &&
			$gevraagd == strtoupper($profiel->geslacht->getValue());
	}
}
