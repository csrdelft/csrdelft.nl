<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\entity\groepen\Woonoord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Behoort een lid tot een bepaalde groep? Verticalen en kringen zijn ook groepen.
 * Als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer de h.t.
 * groep met die korte naam erbij, als het getal is uiteraard de groep met dat id.
 * Met de toevoeging ':Fiscus' kan ook specifieke functie geëist worden binnen een groep.
 */
class GroepVoter extends PrefixVoter
{
	const PREFIX_ACTIVITEIT = 'ACTIVITEIT';
	const PREFIX_BESTUUR = 'BESTUUR';
	const PREFIX_COMMISSIE = 'COMMISSIE';
	const PREFIX_GROEP = 'GROEP';
	const PREFIX_KETZER = 'KETZER';
	const PREFIX_ONDERVERENIGING = 'ONDERVERENIGING';
	const PREFIX_WERKGROEP = 'WERKGROEP';
	const PREFIX_WOONOORD = 'WOONOORD';
	const PREFIX_KRING = 'KRING';
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
		switch (strtoupper($prefix)) {
			case self::PREFIX_KRING:
			case self::PREFIX_ONDERVERENIGING:
			case self::PREFIX_WOONOORD:
			case self::PREFIX_ACTIVITEIT:
			case self::PREFIX_KETZER:
			case self::PREFIX_WERKGROEP:
			case self::PREFIX_GROEP:
			case self::PREFIX_BESTUUR:
			case self::PREFIX_COMMISSIE:
				return true;
			default:
				return false;
		}
	}

	protected function voteOnPrefix(
		string $prefix,
		$gevraagd,
		$role,
		$subject,
		TokenInterface $token
	) {
		$user = $token->getUser();

		if (!$user) {
			return false;
		}

		switch (strtoupper($prefix)) {
			case self::PREFIX_BESTUUR:
				if (in_array(ucfirst($gevraagd), CommissieFunctie::getEnumValues())) {
					$role = $gevraagd;
					$gevraagd = false;
				}
				if ($gevraagd) {
					$groep = $this->em->getRepository(Bestuur::class)->get($gevraagd);
				} else {
					$groep = $this->em->getRepository(Bestuur::class)->get('bestuur'); // h.t.
				}
				break;

			case self::PREFIX_COMMISSIE:
				$groep = $this->em->getRepository(Commissie::class)->get($gevraagd);
				break;

			case self::PREFIX_KRING:
				$groep = $this->em->getRepository(Kring::class)->get($gevraagd);
				break;

			case self::PREFIX_ONDERVERENIGING:
				$groep = $this->em
					->getRepository(Ondervereniging::class)
					->get($gevraagd);
				break;

			case self::PREFIX_WOONOORD:
				$groep = $this->em->getRepository(Woonoord::class)->get($gevraagd);
				break;

			case self::PREFIX_ACTIVITEIT:
				$groep = $this->em->getRepository(Activiteit::class)->get($gevraagd);
				break;

			case self::PREFIX_KETZER:
				$groep = $this->em->getRepository(Ketzer::class)->get($gevraagd);
				break;

			case self::PREFIX_WERKGROEP:
				$groep = $this->em->getRepository(Werkgroep::class)->get($gevraagd);
				break;

			case self::PREFIX_GROEP:
			default:
				$groep = $this->em->getRepository(RechtenGroep::class)->get($gevraagd);
				break;
		}

		if (!$groep) {
			return false;
		}

		$lid = $groep->getLid($user->getUserIdentifier());
		if (!$lid) {
			return false;
		}

		// wordt er een functie gevraagd?
		if ($role && strtoupper($role) !== strtoupper($lid->opmerking)) {
			return false;
		}
		return true;
	}
}
