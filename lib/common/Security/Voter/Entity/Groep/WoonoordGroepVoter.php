<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Woonoord;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WoonoordGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Woonoord::class;
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	) {
		switch ($attribute) {
			case self::BEHEREN:
			case self::WIJZIGEN:
				// Huidige bewoners mogen beheren
				if (
					$this->accessDecisionManager->decide($token, [
						'woonoord:' . $subject->familie,
					])
				) {
					// HuisStatus wijzigen wordt geblokkeerd in GroepForm->validate()
					return true;
				}
				break;
			default:
				break;
		}
		return parent::voteOnAttribute($attribute, $subject, $token);
	}
}
