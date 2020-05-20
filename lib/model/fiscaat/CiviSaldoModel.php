<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\model\entity\fiscaat\CiviSaldoLogEnum;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\repository\fiscaat\CiviSaldoLogRepository;
use DateTime;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviSaldoModel extends PersistenceModel {
	const ORM = CiviSaldo::class;

	/**
	 * @var CiviSaldoLogRepository
	 */
	private $civiSaldoLogRepository;

	/**
	 * CiviSaldoModel constructor.
	 * @param CiviSaldoLogRepository $civiSaldoLogRepository
	 */
	public function __construct(
		CiviSaldoLogRepository $civiSaldoLogRepository
	) {
		parent::__construct();

		$this->civiSaldoLogRepository = $civiSaldoLogRepository;
	}

	/**
	 * @param string $uid
	 *
	 * @return CiviSaldo|false
	 */
	public function getSaldo($uid) {
		return $this->find('uid = ?', array($uid))->fetch();
	}

	/**
	 * @param string $uid
	 *
	 * @return CiviSaldo
	 */
	public function maakSaldo($uid) {
		$saldo = new Civisaldo();
		$saldo->uid = $uid;
		$saldo->naam = '';
		$saldo->saldo = 0;
		$saldo->laatst_veranderd = getDateTime();
		$this->create($saldo);
		return $saldo;
	}

	/**
	 * @param bool $profielOnly
	 *
	 * @return mixed
	 */
	public function getSomSaldi($profielOnly = false) {
		$after = $profielOnly ? "AND uid NOT LIKE 'c%'" : "";
		return array_reduce($this->select(['saldo'], "deleted = 0 $after")->fetchAll(), function ($a, $b) {
			return $a + $b['saldo'];
		}, 0);
	}

	/**
	 * @param DateTime $date
	 * @param bool $profielOnly
	 *
	 * @return mixed
	 */
	public function getSomSaldiOp(DateTime $date, $profielOnly = false) {
		$currentSum = $this->getSomSaldi($profielOnly);
		return $currentSum + ContainerFacade::getContainer()->get(CiviBestellingModel::class)->getSomBestellingenVanaf($date, $profielOnly);
	}

	/**
	 * @param string $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws CsrGebruikerException
	 */
	public function ophogen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new CsrGebruikerException('Kan niet ophogen met een negatief bedrag');
		}

		/** @var CiviSaldo $saldo */
		$saldo = $this->find('uid = ?', array($uid))->fetch();

		if (!$saldo) {
			throw new CsrGebruikerException('Lid heeft geen CiviSaldo');
		}

		$saldo->saldo += $bedrag;
		$saldo->laatst_veranderd = getDateTime();
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

		/** @var CiviSaldo $saldo */
		$saldo = $this->find('uid = ?', array($uid))->fetch();

		if (!$saldo) {
			throw new CsrGebruikerException('Lid heeft geen Civisaldo');
		}

		$saldo->saldo -= $bedrag;
		$saldo->laatst_veranderd = getDateTime();
		$this->update($saldo);

		return $saldo->saldo;
	}

	/**
	 * @param PersistentEntity|CiviSaldo $entity
	 * @return int
	 * @throws CsrGebruikerException
	 */
	public function delete(PersistentEntity $entity) {
		if ($entity->saldo !== 0) {
			throw new CsrGebruikerException("Kan CiviSaldo niet verwijderen: Saldo ongelijk aan nul.");
		}
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::DELETE_SALDO, $entity);
		return parent::delete($entity);
	}

	/**
	 * @param PersistentEntity $entity
	 *
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::CREATE_SALDO, $entity);
		return parent::create($entity);
	}

	/**
	 * @param PersistentEntity $entity
	 *
	 * @return int
	 */
	public function update(PersistentEntity $entity) {
		$this->civiSaldoLogRepository->log(CiviSaldoLogEnum::UPDATE_SALDO, $entity);
		return parent::update($entity);
	}

	public function existsByUid(string $uid) {
		return $this->count('uid = ?', [$uid]) == 1;
	}
}
