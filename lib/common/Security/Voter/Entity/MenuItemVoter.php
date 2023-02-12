<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\MenuItem;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MenuItemVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEKIJKEN = 'bekijken';
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
		return in_array($attribute, [self::BEKIJKEN, self::BEHEREN]);
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == MenuItem::class;
	}

	/**
	 * @param string $attribute
	 * @param MenuItem $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		switch ($attribute) {
			case self::BEKIJKEN:
				return $subject->zichtbaar &&
					$this->accessDecisionManager->decide($token, [
						$subject->rechten_bekijken,
					]);
			case self::BEHEREN:
				return $subject->rechten_bekijken ==
					$token->getUser()->getUserIdentifier() ||
					$this->accessDecisionManager->decide($token, ['ROLE_ADMIN']);
			default:
				throw new CsrException("Onbekende attribute: '$attribute'.");
		}
	}
}
