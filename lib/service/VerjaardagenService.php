<?php

namespace CsrDelft\service;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use DateTimeInterface;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class VerjaardagenService {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository) {
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @return Profiel[][]
	 */
	public function getJaar() {
		return array_map([$this, 'get'], range(1, 12));
	}

	/**
	 * @param $maand
	 *
	 * @return Profiel[]
	 */
	public function get($maand) {
		return $this->profielRepository->createQueryBuilder('p')
			->where('p.status in (:lidstatus) and MONTH(p.gebdatum) = :maand')
			->setParameter('lidstatus', array_merge(LidStatus::getLidLike(), [LidStatus::Kringel]))
			->setParameter('maand', $maand)
			->orderBy('DAY(p.gebdatum)')
			->getQuery()->getResult();
	}

	/**
	 * @param int $aantal
	 *
	 * @return Profiel[]
	 */
	public function getKomende($aantal = 10) {
		return $this->profielRepository->createQueryBuilder('p')
			->where('p.status in (:lidstatus) and not p.gebdatum = \'0000-00-00\'')
			->setParameter('lidstatus', array_merge(LidStatus::getLidLike(), [LidStatus::Kringel]))
			->orderBy('MOD(DAYOFYEAR(p.gebdatum) - DAYOFYEAR(NOW()) + 365, 365)')
			->setMaxResults($aantal)
			->getQuery()->getResult();
	}

	/**
	 * @param DateTimeInterface $van
	 * @param DateTimeInterface $tot
	 * @param int $limiet
	 *
	 * @return Profiel[]
	 */
	public function getTussen(DateTimeInterface $van, DateTimeInterface $tot, $limiet = null) {
		$vanDag = (int) $van->format('z');
		$totDag = (int) $tot->format('z');

		$qb = $this->profielRepository->createQueryBuilder('p')
			->where('p.status in (:lidstatus) and not p.gebdatum = \'0000-00-00\' and p.gebdatum <= :gebdatum')
			->setParameter('lidstatus', array_merge(LidStatus::getLidLike(), [LidStatus::Kringel]))
			->setParameter('gebdatum', $tot)
			->orderBy('MOD(DAYOFYEAR(p.gebdatum) - DAYOFYEAR(NOW()) + 365, 365)')
		->setMaxResults($limiet);

		if ($vanDag > $totDag) { // van en tot spannen over nieuw jaar
			$qb->andWhere('DAYOFYEAR(p.gebdatum) >= :vanDag or DAYOFYEAR(p.gebdatum) <= :totDag')
				->setParameter('vanDag', $vanDag)
				->setParameter('totDag', $totDag);
		} else {
			$qb->andWhere('DAYOFYEAR(p.gebdatum) >= :vanDag and DAYOFYEAR(p.gebdatum) <= :totDag')
				->setParameter('vanDag', $vanDag)
				->setParameter('totDag', $totDag);
		}

		return $qb->getQuery()->getResult();
	}
}
