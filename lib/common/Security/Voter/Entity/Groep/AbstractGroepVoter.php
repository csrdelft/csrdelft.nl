<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractGroepVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEKIJKEN = 'groep.bekijken';
	const AANMAKEN = 'groep.aanmaken';
	const AANMELDEN = 'groep.aanmelden';
	const BEWERKEN = 'groep.bewerken';
	const AFMELDEN = 'groep.afmelden';
	const BEHEREN = 'groep.beheren';
	const WIJZIGEN = 'groep.wijzigen';
	const VERWIJDEREN = 'groep.verwijderen';
	const OPVOLGING = 'groep.opvolging';
	/**
	 * @var AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	public function __construct(
		AccessDecisionManagerInterface $accessDecisionManager
	) {
		$this->accessDecisionManager = $accessDecisionManager;
	}

	public function supportsType(string $subjectType): bool
	{
		return $subjectType == $this->getGroepType();
	}

	abstract protected function getGroepType(): string;

	/**
	 * @param string $attribute
	 * @param Groep $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		if (!$this->accessDecisionManager->decide($token, ['ROLE_LOGGED_IN'])) {
			return false;
		}

		if (
			$subject instanceof HeeftAanmeldLimiet &&
			!$this->magAanmeldLimiet($attribute, $subject)
		) {
			return false;
		}

		if (
			$subject instanceof HeeftAanmeldMoment &&
			!$this->magAanmeldMoment($attribute, $subject)
		) {
			return false;
		}

		if (
			$this instanceof HeeftAanmeldRechten &&
			!$this->magAanmeldRechten($attribute, $subject, $token)
		) {
			return false;
		}

		$aangemeld = $subject->getLid($token->getUserIdentifier()) != null;
		switch ($attribute) {
			case self::AANMELDEN:
				if ($aangemeld) {
					return false;
				}
				break;

			case self::BEWERKEN:
			case self::AFMELDEN:
				if (!$aangemeld) {
					return false;
				}
				break;

			default:
				// Maker van groep mag alles
				if (
					$subject->maker &&
					$subject->maker->uid === $token->getUserIdentifier()
				) {
					return true;
				}
				break;
		}
		return $this->magAlgemeen($attribute, $subject, $token);
	}

	protected function magAanmeldLimiet(
		string $attribute,
		HeeftAanmeldLimiet $groep
	): bool {
		if ($attribute != self::AANMELDEN) {
			return true;
		}
		if ($groep->getAanmeldLimiet() == null) {
			return true;
		}
		// Controleer maximum leden
		return $groep->aantalLeden() < $groep->getAanmeldLimiet();
	}

	protected function magAanmeldMoment(
		string $attribute,
		HeeftAanmeldMoment $groep
	): bool {
		$nu = date_create_immutable();
		switch ($attribute) {
			case self::AANMELDEN:
				return $nu <= $groep->getAanmeldenTot() &&
					$nu >= $groep->getAanmeldenVanaf();
			case self::BEWERKEN:
				return $nu <= $groep->getBewerkenTot();
			case self::AFMELDEN:
				return $nu <= $groep->getAfmeldenTot();
			default:
				return true;
		}
	}

	protected function magAanmeldRechten(
		string $action,
		HeeftAanmeldRechten $groep,
		TokenInterface $token
	): bool {
		$beschermdeActies = [
			AbstractGroepVoter::BEKIJKEN,
			AbstractGroepVoter::AANMELDEN,
			AbstractGroepVoter::BEWERKEN,
			AbstractGroepVoter::AFMELDEN,
		];

		if (in_array($action, $beschermdeActies)) {
			return $this->accessDecisionManager->decide($token, [
				$groep->getAanmeldRechten(),
			]);
		}

		return true;
	}

	protected function magAlgemeen(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		switch ($attribute) {
			case self::BEKIJKEN:
				return $this->accessDecisionManager->decide($token, [
					'ROLE_LEDEN_READ',
				]);

			// Voorkom dat moderators overal een normale aanmeldknop krijgen
			case self::AANMELDEN:
			case self::BEWERKEN:
			case self::AFMELDEN:
				return false;
			default:
				// Moderators mogen alles
				return $this->accessDecisionManager->decide($token, ['ROLE_LEDEN_MOD']);
		}
	}
}
