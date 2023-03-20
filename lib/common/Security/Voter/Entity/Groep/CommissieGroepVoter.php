<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommissieGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Commissie::class;
	}

	/**
	 * @param string $attribute
	 * @param Commissie $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function magAlgemeen(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		if (
			$subject->getSoort() === CommissieSoort::SjaarCie() &&
			$this->accessDecisionManager->decide($token, ['commissie:NovCie'])
		) {
			return true;
		}

		return parent::magAlgemeen($attribute, $subject, $token);
	}
}
