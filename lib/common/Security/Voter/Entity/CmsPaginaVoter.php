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

	public function __construct(
		private AccessDecisionManagerInterface $accessDecisionManager
	) {
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
	): bool {
		if (!$subject instanceof CmsPagina) {
			return false;
		}

		return match ($attribute) {
			self::BEKIJKEN => $this->accessDecisionManager->decide($token, [
				$subject->rechtenBekijken,
			]),
			self::BEWERKEN => $this->accessDecisionManager->decide($token, [
				$subject->rechtenBewerken,
			]),
			self::RECHTEN_WIJZIGEN,
			self::VERWIJDEREN
				=> $this->accessDecisionManager->decide($token, ['ROLE_ADMIN']),
			default => throw new CsrException(
				"Onbekende rechten nodig: '$attribute'."
			),
		};
	}
}
