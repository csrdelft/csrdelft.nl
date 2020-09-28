<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\entity\fiscaat\enum\CiviSaldoLogEnum;
use CsrDelft\repository\AbstractRepository;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviSaldo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviSaldo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviSaldo[]    findAll()
 * @method CiviSaldo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviSaldoRepository extends AbstractRepository {
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
	public function getSaldo($uid, $alleenActief = false) {
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
	public function maakSaldo($uid) {
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
	public function getSomSaldi($profielOnly = false) {
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
	public function getSomSaldiOp(DateTime $date, $profielOnly = false) {
		$currentSum = $this->getSomSaldi($profielOnly);
		return $currentSum + ContainerFacade::getContainer()->get(CiviBestellingRepository::class)->getSomBestellingenVanaf($date, $profielOnly);
	}

	/**
	 * @param string $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function ophogen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new CsrGebruikerException('Kan niet ophogen met een negatief bedrag');
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
	public function verlagen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new CsrGebruikerException('Kan niet verlagen met een negatief bedrag');
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
	public function delete(CiviSaldo $entity) {
		if ($entity->saldo !== 0) {
			throw new CsrGebruikerException("Kan CiviSaldo niet verwijderen: Saldo ongelijk aan nul.");
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
	public function create(CiviSaldo $entity) {
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::CREATE_SALDO, $entity);

		$this->_em->persist($entity);
		$this->_em->flush();

		return $entity->uid;
	}

	public function findLaatsteCommissie() {
		return $this->createQueryBuilder('s')
			->where('s.uid LIKE \'c%\'')
			->orderBy('s.uid', 'DESC')
			->setMaxResults(1)
			->getQuery()->getResult()[0];
	}

	/**
	 * @param CiviSaldo $entity
	 *
	 * @return void
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(CiviSaldo $entity) {
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::UPDATE_SALDO, $entity);

		$this->_em->persist($entity);
		$this->_em->flush();
	}

	public function existsByUid(string $uid) {
		return count($this->findBy(['uid' => $uid])) == 1;
	}

	public function zoeken($uids, $query) {
		return $this->createQueryBuilder('cs')
			->where('cs.deleted = false')
			->andWhere('cs.uid LIKE :query OR cs.naam LIKE :query OR cs.uid in (:uids)')
			->setParameter('query', sql_contains($query))
			->setParameter('uids', $uids)
			->getQuery()->getResult();
	}

	/**
	 * @param int $saldogrens
	 * @return CiviSaldo[]
	 */
	public function getRoodstaandeLeden($saldogrens) {
		return $this->createQueryBuilder('cs')
			->where('cs.saldo < :saldogrens')
			->setParameter('saldogrens', $saldogrens)
			->getQuery()->getResult();
	}
}
