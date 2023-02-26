<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeilingVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const STEMMEN = 'stemmen';
	const BEKIJKEN = 'bekijken';
	const BEWERKEN = 'bewerken';
	const TOEVOEGEN = 'toevoegen';

	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $accessDecisionManager;
	/**
	 * @var PeilingOptiesRepository
	 */
	private $peilingOptiesRepository;

	public function __construct(
		PeilingOptiesRepository $peilingOptiesRepository,
		AccessDecisionManagerInterface $accessDecisionManager
	) {
		$this->accessDecisionManager = $accessDecisionManager;
		$this->peilingOptiesRepository = $peilingOptiesRepository;
	}

	public function supportsAttribute(string $attribute): bool
	{
		return in_array($attribute, [
			self::STEMMEN,
			self::BEKIJKEN,
			self::BEWERKEN,
			self::TOEVOEGEN,
		]);
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == Peiling::class;
	}

	/**
	 * @param string $attribute
	 * @param Peiling $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		switch ($attribute) {
			case self::STEMMEN:
				return $this->magStemmen($token, $subject);
			case self::TOEVOEGEN:
				return $this->magToevoegen($token, $subject);
			case self::BEKIJKEN:
				return $this->accessDecisionManager->decide($token, ['ROLE_LOGGED_IN']);
			case self::BEWERKEN:
				return $this->magBewerken($token, $subject);
			default:
				throw new CsrException("Onbekende attribute: '$attribute'.");
		}
	}

	/**
	 * @param TokenInterface $token
	 * @param Peiling $subject
	 * @return bool
	 */
	private function magBewerken(TokenInterface $token, Peiling $subject): bool
	{
		return $this->accessDecisionManager->decide($token, [P_PEILING_MOD]) ||
			$subject->eigenaar == $token->getUserIdentifier() ||
			$this->accessDecisionManager->decide($token, [$subject->rechten_mod]);
	}

	/**
	 * @param Peiling $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	private function magStemmen(TokenInterface $token, Peiling $subject): bool
	{
		if (!$subject->isPeilingOpen()) {
			return false;
		}
		if (!$this->accessDecisionManager->decide($token, [P_PEILING_VOTE])) {
			return false;
		}
		if ($subject->eigenaar == $token->getUserIdentifier()) {
			return true;
		}
		if (empty(trim($subject->rechten_stemmen))) {
			return true;
		}
		if (
			$this->accessDecisionManager->decide($token, [$subject->rechten_stemmen])
		) {
			return true;
		}
		return false;
	}

	/**
	 * @param TokenInterface $token
	 * @param Peiling $subject
	 * @return bool
	 */
	private function magToevoegen(TokenInterface $token, Peiling $subject): bool
	{
		if ($this->magBewerken($token, $subject)) {
			return true;
		}

		if ($subject->getStem($token->getUser()->profiel)) {
			return false;
		}

		if (!$this->magStemmen($token, $subject)) {
			return false;
		}

		$aantalVoorgesteld = $this->peilingOptiesRepository->count([
			'peiling_id' => $subject->id,
			'ingebracht_door' => $token->getUserIdentifier(),
		]);
		return $aantalVoorgesteld < $subject->aantal_voorstellen;
	}
}
