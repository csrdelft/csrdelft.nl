<?php

namespace CsrDelft\common\Security\Voter\Prefix;

use CsrDelft\common\CsrException;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\Groep;
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
class GroepPrefixVoter extends PrefixVoter
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
	 * @var string[]|Groep[]
	 */
	const CLASS_MAP = [
		self::PREFIX_ACTIVITEIT => Activiteit::class,
		self::PREFIX_BESTUUR => Bestuur::class,
		self::PREFIX_COMMISSIE => Commissie::class,
		self::PREFIX_GROEP => RechtenGroep::class,
		self::PREFIX_KETZER => Ketzer::class,
		self::PREFIX_ONDERVERENIGING => Ondervereniging::class,
		self::PREFIX_WERKGROEP => Werkgroep::class,
		self::PREFIX_WOONOORD => Woonoord::class,
		self::PREFIX_KRING => Kring::class,
	];
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	protected function supportsPrefix($prefix): bool
	{
		return isset(self::CLASS_MAP[$prefix]);
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

		if (isset(self::CLASS_MAP[$prefix])) {
			return $this->em
				->getRepository(self::CLASS_MAP[$prefix])
				->isLid($user, $gevraagd, $role);
		}

		throw new CsrException("Geen klasse gevonden voor prefix: '$prefix'");
	}
}
