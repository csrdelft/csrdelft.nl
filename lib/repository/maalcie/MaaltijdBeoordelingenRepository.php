<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use stdClass;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method MaaltijdBeoordeling|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdBeoordeling|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdBeoordeling[]    findAll()
 * @method MaaltijdBeoordeling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdBeoordelingenRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, MaaltijdBeoordeling::class);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return MaaltijdBeoordeling
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function nieuw(Maaltijd $maaltijd) {
		$b = new MaaltijdBeoordeling();
		$b->maaltijd_id = $maaltijd->maaltijd_id;
		$b->uid = LoginService::getUid();
		$b->kwantiteit = null;
		$b->kwaliteit = null;
		$this->_em->persist($b);
		$this->_em->flush();
		return $b;
	}

	public function getBeoordelingSamenvatting(Maaltijd $maaltijd) {
		// Haal beoordelingen voor deze maaltijd op
		$beoordelingen = $this->findBy(['maaltijd_id' => $maaltijd->maaltijd_id]);

		// Bepaal gemiddelde en gemiddelde afwijking
		$kwantiteit = 0;
		$kwantiteitAfwijking = 0;
		$kwantiteitAantal = 0;
		$kwaliteitAfwijking = 0;
		$kwaliteit = 0;
		$kwaliteitAantal = 0;
		foreach ($beoordelingen as $b) {
			// Haal gemiddelde beoordeling van lid op
			$avg = $this->createQueryBuilder('mb')
				->select('avg(mb.kwantiteit) as kwantiteit, avg(mb.kwaliteit) as kwaliteit')
				->where('mb.uid = :uid')
				->setParameter('uid', $b->uid)
				->getQuery()->getArrayResult();

			// Alleen als waarde is ingevuld
			if (!is_null($b->kwantiteit)) {
				$kwantiteit += $b->kwantiteit;
				// Bepaal afwijking en tel op
				$kwantiteitAfwijking += $b->kwantiteit - $avg[0]['kwantiteit'];
				$kwantiteitAantal++;
			}
			if (!is_null($b->kwaliteit)) {
				$kwaliteit += $b->kwaliteit;
				// Bepaal afwijking en tel op
				$kwaliteitAfwijking += $b->kwaliteit - $avg[0]['kwaliteit'];
				$kwaliteitAantal++;
			}
		}

		// Geef resultaat terug in object, null als er geen beoordelingen zijn
		$object = new stdClass();
		$object->kwantiteit = $kwantiteitAantal === 0 ? null : $kwantiteit / $kwantiteitAantal;
		$object->kwantiteitAfwijking = $kwantiteitAantal === 0 ? null : $kwantiteitAfwijking / $kwantiteitAantal;
		$object->kwantiteitAantal = $kwantiteitAantal;

		$object->kwaliteit = $kwaliteitAantal === 0 ? null : $kwaliteit / $kwaliteitAantal;
		$object->kwaliteitAfwijking = $kwaliteitAantal === 0 ? null : $kwaliteitAfwijking / $kwaliteitAantal;
		$object->kwaliteitAantal = $kwaliteitAantal;

		return $object;
	}

	/**
	 * @param MaaltijdBeoordeling $maaltijdBeoordeling
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(MaaltijdBeoordeling $maaltijdBeoordeling) {
		$this->_em->persist($maaltijdBeoordeling);
		$this->_em->flush();
	}
}
