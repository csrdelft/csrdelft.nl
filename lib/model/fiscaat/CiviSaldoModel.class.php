<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviSaldo.class.php';
require_once 'model/entity/fiscaat/CiviSaldoLogEnum.class.php';
require_once 'model/fiscaat/CiviSaldoLogModel.class.php';

class CiviSaldoModel extends PersistenceModel {
	const ORM = CiviSaldo::class;

	protected static $instance;

	public function getSaldo($uid) {
		return $this->find('uid = ?', array($uid))->fetch();
	}

	public function maakSaldo($uid) {
		$saldo = new Civisaldo();
		$saldo->uid = $uid;
		$saldo->saldo = 0;
		$saldo->laatst_veranderd = getDateTime();
		$this->create($saldo);
		return $saldo;
	}

	/**
	 * @param $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws Exception
	 */
	public function ophogen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new Exception( 'Kan niet ophogen met een negatief bedrag');
		}

		/** @var CiviSaldo $saldo */
		$saldo = $this->retrieveByPrimaryKey(array($uid));

		if (!$saldo) {
			throw new Exception('Lid heeft geen CiviSaldo');
		}

		$saldo->saldo += $bedrag;
		$saldo->laatst_veranderd = date_create()->getTimestamp();
		$this->update($saldo);

		return $saldo->saldo;
	}

	/**
	 * @param $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws Exception
	 */
	public function verlagen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new Exception('Kan niet verlagen met een negatief bedrag');
		}

		/** @var CiviSaldo $saldo */
		$saldo = $this->retrieveByPrimaryKey(array($uid));

		if (!$saldo) {
			throw new Exception('Lid heeft geen Civisaldo');
		}

		$saldo->saldo -= $bedrag;
		$saldo->laatst_veranderd = date_create()->getTimestamp();
		$this->update($saldo);

		return $saldo->saldo;
	}

	public function delete(PersistentEntity $entity) {
		if ($entity->saldo !== 0) {
			throw new Exception("Kan CiviSaldo niet verwijderen: Saldo ongelijk aan nul.");
		}
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::DELETE_SALDO, $entity);
		return parent::delete($entity);
	}

	public function create(PersistentEntity $entity) {
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::CREATE_SALDO, $entity);
		return parent::create($entity);
	}

	public function update(PersistentEntity $entity) {
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::UPDATE_SALDO, $entity);
		return parent::update($entity);
	}
}