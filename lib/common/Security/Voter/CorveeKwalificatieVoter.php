<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Heeft een lid een kwalficatie voor een functie in het covee-systeem?
 */
class CorveeKwalificatieVoter extends PrefixVoter
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper($prefix) == 'KWALIFICATIE';
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	) {
		/** @var Account $user */
		$user = $token->getUser();
		if (!$user) {
			return false;
		}

		$profiel = $user->profiel;

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
			->isLidGekwalificeerdVoorFunctie($profiel->uid, $functieId);
	}
}
