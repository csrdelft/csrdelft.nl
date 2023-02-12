<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\CmsPagina;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CmsPaginaVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEKIJKEN = 'bekijken';
	const BEWERKEN = 'bewerken';
	const RECHTEN_WIJZIGEN = 'rechten_wijzigen';
	const VERWIJDEREN = 'verwijderen';
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
		return in_array($attribute, [
			self::BEKIJKEN,
			self::BEWERKEN,
			self::RECHTEN_WIJZIGEN,
			self::VERWIJDEREN,
		]);
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == CmsPagina::class;
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		if (!$subject instanceof CmsPagina) {
			return false;
		}

		switch ($attribute) {
			case self::BEKIJKEN:
				return $this->accessDecisionManager->decide($token, [
					$subject->rechtenBekijken,
				]);
			case self::BEWERKEN:
				return $this->accessDecisionManager->decide($token, [
					$subject->rechtenBewerken,
				]);
			case self::RECHTEN_WIJZIGEN:
			case self::VERWIJDEREN:
				return $this->accessDecisionManager->decide($token, ['ROLE_ADMIN']);
			default:
				throw new CsrException("Onbekende rechten nodig: '$attribute'.");
		}
	}
}
