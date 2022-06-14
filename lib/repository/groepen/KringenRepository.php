<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Kring;
use CsrDelft\repository\GroepRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class KringenRepository extends GroepRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Kring::class);
	}

	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		return parent::findBy(
			$criteria,
			['verticale' => 'ASC', 'kringNummer' => 'ASC'] + ($orderBy ?? []),
			$limit,
			$offset
		);
	}

	public function isLid(
		UserInterface $user,
		$familie,
		$status = 'ht',
		$role = null
	): bool {
		try {
			list($verticale, $kringNummer) = explode('.', $familie);
			if ($verticale && $kringNummer) {
				return 1 ===
					(int) $this->_em
						->createQuery(
							<<<'EOF'
SELECT COUNT(kring)
FROM CsrDelft\entity\groepen\Kring kring
JOIN kring.leden lid
WHERE kring.verticale = :verticale AND kring.kringNummer = :kringNummer AND lid.uid = :uid
EOF
						)
						->setParameters([
							'verticale' => $verticale,
							'kringNummer' => $kringNummer,
							'uid' => $user->getUserIdentifier(),
						])
						->getSingleScalarResult();
			}

			return parent::isLid($user, $familie, $status, $role);
		} catch (NoResultException | NonUniqueResultException $e) {
			return false;
		}
	}

	public function get($id)
	{
		if (is_numeric($id)) {
			return parent::get($id);
		}
		list($verticale, $kringNummer) = explode('.', $id);
		return $this->findOneBy([
			'verticale' => $verticale,
			'kringNummer' => $kringNummer,
		]);
	}

	public function nieuw($letter = null)
	{
		/** @var Kring $kring */
		$kring = parent::nieuw();
		$kring->verticale = $letter;
		return $kring;
	}
}
