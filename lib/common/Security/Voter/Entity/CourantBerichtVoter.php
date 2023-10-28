<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\courant\CourantBericht;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourantBerichtVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEHEREN = 'beheren';
	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $accessDecisionManager;

	public function __construct(
		AccessDecisionManagerInterface $accessDecisionManager
	) {
		$this->accessDecisionManager = $accessDecisionManager;
	}

	public function supportsAttribute(string $attribute): bool
	{
		return $attribute == self::BEHEREN;
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == CourantBericht::class;
	}

	/**
	 * @param string $attribute
	 * @param CourantBericht $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		switch ($attribute) {
			case self::BEHEREN:
				return $this->accessDecisionManager->decide($token, [
					'ROLE_MAIL_COMPOSE',
				]) || $this->accessDecisionManager->decide($token, [$subject->uid]);
			default:
				throw new CsrGebruikerException(
					"Attribute niet gevonden: '$attribute'."
				);
		}
	}
}
