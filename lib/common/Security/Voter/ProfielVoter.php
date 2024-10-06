<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use CsrDelft\common\Security\Voter\CacheableVoterSupportsTrait;
use Symfony\Component\Security\Core\Security;

class ProfielVoter extends Voter
{
	use CacheableVoterSupportsTrait;

	const BEWERK_NAMESPACE = 'bewerk:';
	const BEKIJK_NAMESPACE = 'bekijk:';

	public function __construct(
		private LidToestemmingRepository $lidToestemming,
		private Security $security
	) {
	}

	public function supportsAttribute(string $attribute): bool
	{
		$split = explode(':', $attribute, 3);
		if (count($split) < 3) {
			return false;
		}
		if (
			!in_array($split[0], [self::BEWERK_NAMESPACE, self::BEKIJK_NAMESPACE])
		) {
			return false;
		}
		if (!in_array($split[1], $this->lidToestemming->getModules())) {
			return false;
		}
		return in_array($split[2], $this->lidToestemming->getModuleKeys($split[1]));
	}
	protected function supports(string $attribute, mixed $subject): bool
	{
		if (!$subject instanceof Profiel) {
			return false;
		}
		$relevanteAttributes = $this->lidToestemming->getRelevantToestemmingCategories(
			$subject->isLid()
		);
		return in_array($attribute, $relevanteAttributes);
	}

	protected function voteOnAttribute(
		string $attribute,
		$subject,
		TokenInterface $token
	): bool {
		return false;
	}
}
