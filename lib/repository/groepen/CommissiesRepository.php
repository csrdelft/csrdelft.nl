<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\GroepRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CommissiesRepository extends GroepRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Commissie::class);
	}

	public function nieuw($soort = null)
	{
		if (is_string($soort)) {
			$soort = $this->parseSoort($soort);
		}
		if ($soort == null) {
			$soort = CommissieSoort::Commissie();
		}
		/** @var Commissie $commissie */
		$commissie = parent::nieuw();
		$commissie->commissieSoort = $soort;
		return $commissie;
	}

	public function overzicht(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return $this->findBy([
				'status' => GroepStatus::HT(),
				'commissieSoort' => CommissieSoort::from($soort),
			]);
		}
		return parent::overzicht($soort);
	}

	public function beheer(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return $this->findBy(['commissieSoort' => CommissieSoort::from($soort)]);
		}
		return parent::beheer($soort);
	}

	public function parseSoort(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return CommissieSoort::from($soort);
		}
		return parent::parseSoort($soort);
	}

	public function isLid(
		UserInterface $user,
		$familie,
		$status = 'HT',
		$role = null
	) {
		$role = strtolower($role);
		if (in_array($role, GroepStatus::getEnumValues())) {
			return 1 ===
				(int) $this->_em
					->createQuery(
						'SELECT COUNT(c) FROM CsrDelft\entity\groepen\Commissie c JOIN c.leden l WHERE l.uid = :uid AND c.familie = :gevraagd AND c.status = :role'
					)
					->setParameter('gevraagd', $familie)
					->setParameter('role', $status)
					->setParameter('uid', $user->getUserIdentifier())
					->getSingleScalarResult();
		}

		return parent::isLid($user, $familie, $status, $role);
	}
}
