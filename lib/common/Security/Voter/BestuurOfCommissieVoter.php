<?php

namespace CsrDelft\common\Security\Voter;

use CsrDelft\entity\groepen\enum\GroepStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Behoort een lid tot een f.t. / h.t. / o.t. bestuur of commissie?
 *
 * Controleert rechten als
 * - bestuur
 * - bestuur:ft
 * - bestuur:ot:abactis
 * - commissie
 * - commissie:ot
 * - commissie:ht:qq
 */
class BestuurOfCommissieVoter extends PrefixVoter
{
	const PREFIX_BESTUUR = 'BESTUUR';
	const PREFIX_COMMISSIE = 'COMMISSIE';
	/**
	 * @var GroepVoter
	 */
	private $groepPrefixVoter;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(
		EntityManagerInterface $em,
		GroepVoter $groepPrefixVoter
	) {
		$this->groepPrefixVoter = $groepPrefixVoter;
		$this->em = $em;
	}

	protected function supportsPrefix($prefix)
	{
		return strtoupper($prefix) == self::PREFIX_BESTUUR ||
			strtoupper($prefix) == self::PREFIX_COMMISSIE;
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

		if (strtoupper($prefix) == self::PREFIX_BESTUUR) {
			$gevraagd = strtolower($gevraagd);
			if (in_array($gevraagd, GroepStatus::getEnumValues())) {
				return 1 ===
					(int) $this->em
						->createQuery(
							'SELECT COUNT(b) FROM CsrDelft\entity\groepen\Bestuur b JOIN b.leden l WHERE l.uid = :uid AND b.status = :gevraagd'
						)
						->setParameter('gevraagd', $gevraagd)
						->setParameter('uid', $user->getUserIdentifier())
						->getSingleScalarResult();
			}
		}

		$role = strtolower($role);
		// Alleen als GroepStatus is opgegeven, anders: fall through
		if (in_array($role, GroepStatus::getEnumValues())) {
			return 1 ===
				(int) $this->em
					->createQuery(
						'SELECT COUNT(c) FROM CsrDelft\entity\groepen\Commissie c JOIN c.leden l WHERE l.uid = :uid AND c.familie = :gevraagd AND c.status = :role'
					)
					->setParameter('gevraagd', $gevraagd)
					->setParameter('role', $role)
					->setParameter('uid', $user->getUserIdentifier())
					->getSingleScalarResult();
		}

		$attribute = $this->buildAttribute($prefix, $gevraagd, $role);

		return $this->groepPrefixVoter->vote($token, $subject, [$attribute]);
	}

	private function buildAttribute($prefix, $gevraagd, $role): string
	{
		return sprintf(
			'%s%s%s',
			$prefix,
			$gevraagd ? ':' . $gevraagd : '',
			$role ? ':' . $role : ''
		);
	}
}
