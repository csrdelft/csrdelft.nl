<?php

namespace CsrDelft\common\Security\Voter\Entity;

use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\entity\groepen\GroepLid;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroepLidVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	public function __construct(
		private AccessDecisionManagerInterface $accessDecisionManager
	) {
	}

	public function supportsType(string $subjectType): bool
	{
		// Ook subclasses van Groep
		return $subjectType == GroepLid::class;
	}

	/**
	 * @param string $attribute
	 * @param GroepLid $subject
	 * @param TokenInterface $token
	 * @return bool|void
	 */
	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		if (
			!$this->accessDecisionManager->decide(
				$token,
				[$attribute],
				$subject->groep
			)
		) {
			return false;
		}
		return match ($attribute) {
			AbstractGroepVoter::AFMELDEN,
			AbstractGroepVoter::BEHEREN,
			AbstractGroepVoter::BEWERKEN
				=> $this->magLid($token, $subject),
			default => false,
		};
	}

	/**
	 * @param string $groepAttribute
	 * @param TokenInterface $token
	 * @param GroepLid $subject
	 * @return bool
	 */
	private function magLid(TokenInterface $token, GroepLid $subject): bool
	{
		if ($token->getUserIdentifier() == $subject->uid) {
			return true;
		}

		// LEDEN_MOD mag sowieso
		return $this->accessDecisionManager->decide($token, ['ROLE_LEDEN_MOD']);
	}
}
