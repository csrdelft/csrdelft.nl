<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ActiviteitGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Activiteit::class;
	}

	/**
	 * @param string $attribute
	 * @param Activiteit $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function magAlgemeen(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		if ($subject->getSoort() instanceof ActiviteitSoort) {
			switch ($subject->getSoort()) {
				case ActiviteitSoort::OWee():
					if (
						$this->accessDecisionManager->decide($token, ['commissie:OWeeCie'])
					) {
						return true;
					}
					break;

				case ActiviteitSoort::Dies():
					if (
						$this->accessDecisionManager->decide($token, ['commissie:DiesCie'])
					) {
						return true;
					}
					break;

				case ActiviteitSoort::Lustrum():
					if (
						$this->accessDecisionManager->decide($token, [
							'commissie:LustrumCie',
						])
					) {
						return true;
					}
					break;
				default:
					break;
			}
		}
		return match ($attribute) {
			self::AANMAKEN, self::AANMELDEN, self::BEWERKEN, self::AFMELDEN => true,
			default => parent::magAlgemeen($attribute, $subject, $token),
		};
	}
}
