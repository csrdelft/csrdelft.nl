<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\corvee\CorveeVrijstelling;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method CorveeVrijstelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeVrijstelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeVrijstelling[]    findAll()
 * @method CorveeVrijstelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeVrijstellingenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CorveeVrijstelling::class);
	}

	public function nieuw(
		$profiel = null,
		$begin = null,
		$eind = null,
		$percentage = 0
	): CorveeVrijstelling {
		$vrijstelling = new CorveeVrijstelling();
		$vrijstelling->profiel = $profiel;
		$vrijstelling->uid = $profiel->uid ?? null;
		if ($begin === null) {
			$begin = date_create_immutable();
		}
		$vrijstelling->begin_datum = $begin;
		if ($eind === null) {
			$eind = date_create_immutable();
		}
		$vrijstelling->eind_datum = $eind;
		if ($percentage === null) {
			$percentage = intval(
				InstellingUtil::instelling(
					'corvee',
					'standaard_vrijstelling_percentage'
				)
			);
		}
		$vrijstelling->percentage = $percentage;

		return $vrijstelling;
	}

	public function getAlleVrijstellingen($groupByUid = false)
	{
		$vrijstellingen = $this->findAll();
		if ($groupByUid) {
			$vrijstellingenByUid = [];
			foreach ($vrijstellingen as $vrijstelling) {
				$vrijstellingenByUid[$vrijstelling->uid] = $vrijstelling;
			}
			return $vrijstellingenByUid;
		}
		return $vrijstellingen;
	}

	/**
	 * @param $uid
	 * @return CorveeVrijstelling|null
	 */
	public function getVrijstelling($uid)
	{
		return $this->find($uid);
	}

	/**
	 * @param Profiel $profiel
	 * @param DateTimeInterface $begin
	 * @param DateTimeInterface $eind
	 * @param integer $percentage
	 * @return CorveeVrijstelling
	 * @throws Throwable
	 */
	public function saveVrijstelling(
		$profiel,
		DateTimeInterface $begin,
		DateTimeInterface $eind,
		$percentage
	) {
		return $this->getEntityManager()->transactional(function () use (
			$profiel,
			$begin,
			$eind,
			$percentage
		) {
			$vrijstelling = $this->getVrijstelling($profiel->uid);

			if (!$vrijstelling) {
				$vrijstelling = $this->nieuw($profiel, $begin, $eind, $percentage);
			} else {
				$vrijstelling->begin_datum = $begin;
				$vrijstelling->eind_datum = $eind;
				$vrijstelling->percentage = $percentage;
			}

			$this->getEntityManager()->persist($vrijstelling);
			$this->getEntityManager()->flush();
			return $vrijstelling;
		});
	}

	public function verwijderVrijstelling($uid)
	{
		$this->createQueryBuilder('v')
			->delete()
			->where('v.uid = :uid')
			->setParameter('uid', $uid)
			->getQuery()
			->execute();
	}
}
