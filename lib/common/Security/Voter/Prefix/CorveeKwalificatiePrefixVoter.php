<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Heeft een lid een kwalficatie voor een functie in het covee-systeem?
 */
class CorveeKwalificatiePrefixVoter extends PrefixVoter
{
	public function __construct(private readonly EntityManagerInterface $em)
	{
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper((string) $prefix) === 'KWALIFICATIE';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	): bool {
		/** @var Account $user */
		$user = $token->getUser();
		if (!$user) {
			return false;
		}

		if (is_numeric($gevraagd)) {
			$functieId = (int) $gevraagd;
		} else {
			$corveeFunctiesRepository = $this->em->getRepository(
				CorveeFunctie::class
			);

			$functie = $corveeFunctiesRepository->findOneBy([
				'afkorting' => $gevraagd,
			]);

			if (!$functie) {
				$functie = $corveeFunctiesRepository->findOneBy([
					'naam' => $gevraagd,
				]);
			}

			if ($functie) {
				$functieId = $functie->functie_id;
			} else {
				return false;
			}
		}

		return $this->em
			->getRepository(CorveeKwalificatie::class)
			->isLidGekwalificeerdVoorFunctie($user->getUserIdentifier(), $functieId);
	}
}
