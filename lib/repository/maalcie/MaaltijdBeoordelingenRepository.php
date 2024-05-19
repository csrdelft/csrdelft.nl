<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\entity\maalcie\MaaltijdBeoordelingDTO;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method MaaltijdBeoordeling|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdBeoordeling|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdBeoordeling[]    findAll()
 * @method MaaltijdBeoordeling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdBeoordelingenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, MaaltijdBeoordeling::class);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return MaaltijdBeoordeling
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function nieuw(Maaltijd $maaltijd): MaaltijdBeoordeling
	{
		$b = new MaaltijdBeoordeling();
		$b->maaltijd_id = $maaltijd->maaltijd_id;
		$b->uid = LoginService::getUid();
		$b->kwantiteit = null;
		$b->kwaliteit = null;
		$this->getEntityManager()->persist($b);
		$this->getEntityManager()->flush();
		return $b;
	}

	public function getBeoordelingSamenvatting(Maaltijd $maaltijd): MaaltijdBeoordelingDTO
	{
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
				->select(
					'avg(mb.kwantiteit) as kwantiteit, avg(mb.kwaliteit) as kwaliteit'
				)
				->where('mb.uid = :uid')
				->setParameter('uid', $b->uid)
				->getQuery()
				->getArrayResult();

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
		$beoordeling = new MaaltijdBeoordelingDTO();
		$beoordeling->kwantiteit = $this->getalWeergave(
			$kwantiteitAantal === 0 ? null : $kwantiteit / $kwantiteitAantal,
			'-',
			3
		);
		$beoordeling->kwantiteitAfwijking = $this->getalWeergave(
			$kwantiteitAantal === 0 ? null : $kwantiteitAfwijking / $kwantiteitAantal,
			'-',
			3,
			true
		);
		$beoordeling->kwantiteitAantal = $kwantiteitAantal;

		$beoordeling->kwaliteit = $this->getalWeergave(
			$kwaliteitAantal === 0 ? null : $kwaliteit / $kwaliteitAantal,
			'-',
			3
		);
		$beoordeling->kwaliteitAfwijking = $this->getalWeergave(
			$kwaliteitAantal === 0 ? null : $kwaliteitAfwijking / $kwaliteitAantal,
			'-',
			3,
			true
		);
		$beoordeling->kwaliteitAantal = $kwaliteitAantal;

		$beoordeling->setMaaltijd($maaltijd);

		return $beoordeling;
	}

	private function getalWeergave(
		$number,
		$placeholder,
		$precision,
		$showPlus = false
	) {
		if ($number === null) {
			return $placeholder;
		} else {
			$plus = $showPlus && $number > 0 ? '+' : '';
			return $plus . round($number, $precision);
		}
	}

	/**
	 * @param MaaltijdBeoordeling $maaltijdBeoordeling
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(MaaltijdBeoordeling $maaltijdBeoordeling)
	{
		$this->getEntityManager()->persist($maaltijdBeoordeling);
		$this->getEntityManager()->flush();
	}
}
