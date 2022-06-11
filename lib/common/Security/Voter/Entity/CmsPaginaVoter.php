<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\CmsPagina;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CmsPaginaVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEKIJKEN = 'CmsPagina_bekijken';
	const BEWERKEN = 'CmsPagina_bewerken';
	const RECHTEN_WIJZIGEN = 'CmsPagina_rechten_wijzigen';
	const VERWIJDEREN = 'CmsPagina_verwijderen';
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
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
				return $this->security->isGranted($subject->rechtenBekijken);
			case self::BEWERKEN:
				return $this->security->isGranted($subject->rechtenBewerken);
			case self::RECHTEN_WIJZIGEN:
			case self::VERWIJDEREN:
				return $this->security->isGranted('ROLE_ADMIN');
			default:
				throw new CsrException("Onbekende rechten nodig: '$attribute'.");
		}
	}
}
