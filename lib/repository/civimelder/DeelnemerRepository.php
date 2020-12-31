<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\Entity\civimelder\Deelnemer;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Deelnemer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deelnemer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deelnemer[]    findAll()
 * @method Deelnemer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeelnemerRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Deelnemer::class);
	}

	public function getAantalAanmeldingen(Activiteit $activiteit): int {
		$q = $this->createQueryBuilder('a')
			->select('SUM(a.aantal)')
			->where('a.activiteit = :activiteit')
			->setParameter('activiteit', $activiteit)
			->getQuery();

		try {
			return $q->getSingleScalarResult();
		} catch (NoResultException $e) {
			return 0;
		} catch (NonUniqueResultException $e) {
			return 0;
		}
	}

	public function isAangemeld(Activiteit $activiteit, Profiel $profiel): bool {
		return $this->getDeelnemer($activiteit, $profiel) !== null;
	}

	public function getDeelnemer(Activiteit $activiteit, Profiel $profiel): ?Deelnemer {
		return $this->findOneBy(['activiteit' => $activiteit, 'lid' => $profiel]);
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Profiel $lid
	 * @param int $aantal
	 * @throws ORMException
	 */
	public function aanmelden(Activiteit $activiteit, Profiel $lid, int $aantal): void {
		$reden = '';
		if (!$activiteit->magAanmelden($aantal, $reden)) {
			throw new CsrGebruikerException("Aanmelden mislukt: {$reden}.");
		}

		$deelnemer = new Deelnemer($activiteit, $lid, $aantal);

		$this->getEntityManager()->persist($deelnemer);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Profiel $lid
	 * @param int $aantal
	 * @throws ORMException
	 */
	public function afmelden(Activiteit $activiteit, Profiel $lid, int $aantal): void {
		$reden = '';
		if (!$this->isAangemeld($activiteit, $lid)) {
			throw new CsrGebruikerException("Afmelden mislukt: je bent niet aangemeld.");
		} elseif (!$activiteit->magAfmelden($reden)) {
			throw new CsrGebruikerException("Afmelden mislukt: {$reden}.");
		}

		$deelnemer = $this->getDeelnemer($activiteit, $lid);
		$this->getEntityManager()->remove($deelnemer);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param Activiteit $activiteit
	 * @param Profiel $lid
	 * @param int $aantal
	 * @throws ORMException
	 */
	public function aantalAanpassen(Activiteit $activiteit, Profiel $lid, int $aantal): void {
		if (!$this->isAangemeld($activiteit, $lid)) {
			throw new CsrGebruikerException("Afmelden mislukt: je bent niet aangemeld.");
		}

		$deelnemer = $this->getDeelnemer($activiteit, $lid);
		$reden = '';
		if ($deelnemer->getAantal() > $aantal) {
			$extra = $aantal - $deelnemer->getAantal();
			if (!$activiteit->magAanmelden($extra, $reden)) {
				throw new CsrGebruikerException("Aanmelden mislukt: {$reden}.");
			}
		} elseif ($deelnemer->getAantal() < $aantal) {
			if (!$activiteit->magAfmelden($reden)) {
				throw new CsrGebruikerException("Afmelden mislukt: {$reden}.");
			}
		} else {
			return;
		}

		$deelnemer->setAantal($aantal);
		$this->getEntityManager()->flush();
	}
}
