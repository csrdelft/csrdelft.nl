<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\entity\fiscaat\enum\CiviSaldoLogEnum;
use CsrDelft\repository\AbstractRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use stdClass;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviSaldo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviSaldo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviSaldo[]    findAll()
 * @method CiviSaldo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviSaldoRepository extends AbstractRepository
{
	/**
	 * @var CiviSaldoLogRepository
	 */
	private $civiSaldoLogRepository;

	/**
	 * @param ManagerRegistry $registry
	 * @param CiviSaldoLogRepository $civiSaldoLogRepository
	 */
	public function __construct(
		ManagerRegistry $registry,
		CiviSaldoLogRepository $civiSaldoLogRepository
	) {
		parent::__construct($registry, CiviSaldo::class);

		$this->civiSaldoLogRepository = $civiSaldoLogRepository;
	}

	/**
	 * @param string $uid
	 *
	 * @param bool $alleenActief
	 * @return CiviSaldo|null
	 */
	public function getSaldo($uid, $alleenActief = false)
	{
		$critera = ['uid' => $uid];
		if ($alleenActief) {
			$critera['deleted'] = 0;
		}
		return $this->findOneBy($critera);
	}

	/**
	 * @param string $uid
	 *
	 * @return CiviSaldo
	 */
	public function maakSaldo($uid)
	{
		$saldo = new CiviSaldo();
		$saldo->uid = $uid;
		$saldo->naam = '';
		$saldo->saldo = 0;
		$saldo->laatst_veranderd = date_create_immutable();
		$this->create($saldo);
		return $saldo;
	}

	/**
	 * @param bool $profielOnly
	 *
	 * @return mixed
	 */
	public function getSomSaldi($profielOnly = false)
	{
		$qb = $this->createQueryBuilder('s')
			->select('SUM(s.saldo)')
			->where('s.deleted = false');

		if ($profielOnly) {
			$qb = $qb->andWhere('s.uid NOT LIKE \'c%\'');
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param DateTime $date
	 * @param bool $profielOnly
	 *
	 * @return mixed
	 */
	public function getSomSaldiOp(DateTime $date, $profielOnly = false)
	{
		$currentSum = $this->getSomSaldi($profielOnly);
		return $currentSum +
			ContainerFacade::getContainer()
				->get(CiviBestellingRepository::class)
				->getSomBestellingenVanaf($date, $profielOnly);
	}

	/**
	 * @param string $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function ophogen($uid, $bedrag)
	{
		if ($bedrag < 0) {
			throw new CsrGebruikerException(
				'Kan niet ophogen met een negatief bedrag'
			);
		}

		$saldo = $this->getSaldo($uid);

		if (!$saldo) {
			throw new CsrGebruikerException('Lid heeft geen CiviSaldo');
		}

		$saldo->saldo += $bedrag;
		$saldo->laatst_veranderd = date_create_immutable();
		$this->update($saldo);

		return $saldo->saldo;
	}

	/**
	 * @param string $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws CsrGebruikerException
	 */
	public function verlagen($uid, $bedrag)
	{
		if ($bedrag < 0) {
			throw new CsrGebruikerException(
				'Kan niet verlagen met een negatief bedrag'
			);
		}

		$saldo = $this->getSaldo($uid);

		if (!$saldo) {
			throw new CsrGebruikerException('Lid heeft geen Civisaldo');
		}

		$saldo->saldo -= $bedrag;
		$saldo->laatst_veranderd = date_create_immutable();
		$this->update($saldo);

		return $saldo->saldo;
	}

	/**
	 * @param CiviSaldo $entity
	 * @return int
	 * @throws CsrGebruikerException
	 */
	public function delete(CiviSaldo $entity)
	{
		if ($entity->saldo !== 0) {
			throw new CsrGebruikerException(
				'Kan CiviSaldo niet verwijderen: Saldo ongelijk aan nul.'
			);
		}
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::DELETE_SALDO, $entity);

		$this->_em->remove($entity);
		$this->_em->flush();
	}

	/**
	 * @param CiviSaldo $entity
	 *
	 * @return string
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(CiviSaldo $entity)
	{
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::CREATE_SALDO, $entity);

		$this->_em->persist($entity);
		$this->_em->flush();

		return $entity->uid;
	}

	public function findLaatsteCommissie()
	{
		return $this->createQueryBuilder('s')
			->where('s.uid LIKE \'c%\'')
			->orderBy('s.uid', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getResult()[0];
	}

	/**
	 * @param CiviSaldo $entity
	 *
	 * @return void
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(CiviSaldo $entity)
	{
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::UPDATE_SALDO, $entity);

		$this->_em->persist($entity);
		$this->_em->flush();
	}

	public function existsByUid(string $uid)
	{
		return count($this->findBy(['uid' => $uid])) == 1;
	}

	public function zoeken($uids, $query)
	{
		return $this->createQueryBuilder('cs')
			->where('cs.deleted = false')
			->andWhere(
				'cs.uid LIKE :query OR cs.naam LIKE :query OR cs.uid in (:uids)'
			)
			->setParameter('query', sql_contains($query))
			->setParameter('uids', $uids)
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $saldogrens
	 * @return CiviSaldo[]
	 */
	public function getRoodstaandeLeden($saldogrens)
	{
		return $this->createQueryBuilder('cs')
			->where('cs.saldo < :saldogrens')
			->setParameter('saldogrens', $saldogrens)
			->getQuery()
			->getResult();
	}

	/**
	 * @param DateTimeImmutable $from
	 * @param DateTimeImmutable $until
	 * @return stdClass
	 */
	public function getWeekinvoer(
		DateTimeImmutable $from,
		DateTimeImmutable $until
	) {
		// Invoer gebeurt op maandag, zoek eerste en laatse maandag (eerste inbegrepen, laatste niet)
		$from = $from->modify('monday');
		$until = $until->modify('next monday'); // Ook als het maandag is de volgende maandag pakken

		$query = <<<SQL
SELECT G.type,
	SUM(I.aantal * PR.prijs) AS total,
	YEARWEEK(B.moment, 3) AS yearweek
FROM civi_bestelling AS B
JOIN civi_bestelling_inhoud AS I ON
	B.id = I.bestelling_id
JOIN civi_product AS P ON
	I.product_id = P.id
JOIN civi_prijs AS PR ON
	P.id = PR.product_id
	AND (B.moment > PR.van AND (B.moment < PR.tot OR PR.tot IS NULL))
JOIN civi_categorie AS G ON
	P.categorie_id = G.id
WHERE
	B.deleted = 0 AND
	G.status = 1 AND
	B.cie != 'maalcie' AND
	B.moment >= :van AND
	B.moment < :tot
GROUP BY
	yearweek,
	G.id
ORDER BY yearweek DESC
SQL;

		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('type', 'categorie');
		$rsm->addScalarResult('total', 'total', 'integer');
		$rsm->addScalarResult('yearweek', 'yearweek');

		$nativeQuery = $this->_em->createNativeQuery($query, $rsm);
		$nativeQuery->setParameter('van', $from);
		$nativeQuery->setParameter('tot', $until);

		return self::formatWeekinvoer($nativeQuery->getResult());
	}

	private static function formatWeekinvoer($result)
	{
		$weekinvoeren = new stdClass();
		// Standaard volgorde categorieën
		$weekinvoeren->categorieen = [
			'Bier',
			'Wijn',
			'Fris',
			'Speciaalbier',
			'Sterk',
			'Whisky',
			'Etenswaar',
			'Glaswerk',
			'Overig',
			'PrakCie pils',
			'PrakCie cent',
			'Incidentele donateur',
			'Cent',
			'PIN',
			'Overgemaakt',
			'Contant',
		];
		$weekinvoeren->weken = [];

		foreach ($result as $regel) {
			$yearweek = $regel['yearweek'];
			if (!isset($weekinvoeren->weken[$yearweek])) {
				$weekinvoer = new stdClass();
				$weekinvoer->jaar = intval(substr($yearweek, 0, 4));
				$weekinvoer->week = intval(substr($yearweek, 4));
				$padWeek = str_pad($weekinvoer->week, 2, '0', STR_PAD_LEFT);
				$weekinvoer->datum = new DateTimeImmutable(
					"{$weekinvoer->jaar}-W{$padWeek}-1"
				);
				$weekinvoer->einde = $weekinvoer->datum->add(new DateInterval('P1W'));
				$weekinvoer->categorieen = [];
				$weekinvoer->totaal = 0;

				$weekinvoeren->weken[$yearweek] = $weekinvoer;
			}

			if (!in_array($regel['categorie'], $weekinvoeren->categorieen)) {
				$weekinvoeren->categorieen[] = $regel['categorie'];
			}

			$weekinvoeren->weken[$yearweek]->categorieen[$regel['categorie']] =
				$regel['total'];
			$weekinvoeren->weken[$yearweek]->totaal += $regel['total'];
		}

		return $weekinvoeren;
	}

	/**
	 * @param DateTimeImmutable $from
	 * @param DateTimeImmutable $until
	 * @param string $cie
	 * @param int $categorie
	 * @param int $product
	 * @param bool $groeperen
	 * @return int|mixed|string
	 */
	public function zoekBestellingen(
		DateTimeImmutable $from,
		DateTimeImmutable $until,
		string $cie,
		int $categorie,
		int $product,
		bool $groeperen
	) {
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('moment', 'moment', 'datetime_immutable');
		$rsm->addScalarResult('cie', 'cie');
		$rsm->addScalarResult('type', 'type');
		$rsm->addScalarResult('beschrijving', 'beschrijving');
		$rsm->addScalarResult('comment', 'comment');
		$rsm->addScalarResult('prijs', 'prijs', 'integer');
		$rsm->addScalarResult('aantal', 'aantal', 'integer');

		if ($groeperen) {
			$select =
				'MIN(moment) AS moment, G.cie, type, beschrijving, comment, prijs, SUM(aantal) AS aantal';
			$orderBy = 'MIN(moment)';
			$groupBy = 'GROUP BY G.cie, type, beschrijving, comment';
		} else {
			$select =
				'moment, uid, G.cie, type, beschrijving, prijs, aantal, comment';
			$orderBy = 'moment';
			$groupBy = '';

			$rsm->addScalarResult('uid', 'uid');
		}

		$filter = '';
		if ($cie != -1) {
			$filter .= ' AND G.cie = :cie ';
		}
		if ($categorie != -1) {
			$filter .= ' AND G.id = :categorie ';
		}
		if ($product != -1) {
			$filter .= ' AND P.id = :product ';
		}

		$query = <<<SQL
SELECT $select
FROM civi_bestelling AS B
JOIN civi_bestelling_inhoud AS I ON
	B.id = I.bestelling_id
JOIN civi_product AS P ON
	I.product_id = P.id
JOIN civi_prijs AS PR ON
	P.id = PR.product_id
	AND (B.moment > PR.van AND (B.moment < PR.tot OR PR.tot IS NULL))
JOIN civi_categorie AS G ON
	P.categorie_id = G.id
WHERE
	deleted = 0 AND
	B.moment >= :van AND
	B.moment < :tot
	{$filter}
{$groupBy}
ORDER BY {$orderBy}
SQL;

		$nativeQuery = $this->_em->createNativeQuery($query, $rsm);
		$nativeQuery->setParameter('van', $from);
		$nativeQuery->setParameter('tot', $until);

		if ($cie != -1) {
			$nativeQuery->setParameter('cie', $cie);
		}
		if ($categorie != -1) {
			$nativeQuery->setParameter('categorie', $categorie);
		}
		if ($product != -1) {
			$nativeQuery->setParameter('product', $product);
		}

		$result = $nativeQuery->getResult();

		if (count($result) > 1000) {
			MeldingUtil::setMelding(
				'Te veel (>1000) resultaten. Stel specifiekere filters in.',
				-1
			);
			return [];
		}

		if (!$groeperen) {
			foreach ($result as $key => $value) {
				$civiSaldo = $this->getSaldo($value['uid']);
				if ($civiSaldo) {
					$result[$key]['civisaldo'] = $civiSaldo->getLink();
				} else {
					$result[$key]['civisaldo'] = $value['uid'];
				}
			}
		}
		return $result;
	}
}
