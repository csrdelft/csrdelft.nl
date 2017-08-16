<?php
namespace CsrDelft\model\fiscaat;

use function CsrDelft\getDateTime;
use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\model\entity\fiscaat\CiviSaldoLogEnum;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use Exception;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviSaldoModel extends PersistenceModel {
	const ORM = CiviSaldo::class;

	/**
	 * @var CiviSaldoModel
	 */
	protected static $instance;

	/**
	 * @param string $uid
	 *
	 * @return CiviSaldo
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
	    return array_reduce($this->select(['saldo'], "deleted = 0 $after")->fetchAll(), function($a, $b) {return $a+$b['saldo'];}, 0);
    }

	/**
	 * @param string $uid
	 * @param int $bedrag
	 * @return int Nieuwe saldo
	 * @throws Exception
	 */
	public function ophogen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new Exception( 'Kan niet ophogen met een negatief bedrag');
		}

		/** @var CiviSaldo $saldo */
		$saldo = $this->find('uid = ?', array($uid))->fetch();

		if (!$saldo) {
			throw new Exception('Lid heeft geen CiviSaldo');
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
	 * @throws Exception
	 */
	public function verlagen($uid, $bedrag) {
		if ($bedrag < 0) {
			throw new Exception('Kan niet verlagen met een negatief bedrag');
		}

		/** @var CiviSaldo $saldo */
		$saldo = $this->find('uid = ?', array($uid))->fetch();

		if (!$saldo) {
			throw new Exception('Lid heeft geen Civisaldo');
		}

		$saldo->saldo -= $bedrag;
		$saldo->laatst_veranderd = getDateTime();
		$this->update($saldo);

		return $saldo->saldo;
	}

	/**
	 * @param PersistentEntity|CiviSaldo $entity
	 * @return int
	 * @throws Exception
	 */
	public function delete(PersistentEntity $entity) {
		if ($entity->saldo !== 0) {
			throw new Exception("Kan CiviSaldo niet verwijderen: Saldo ongelijk aan nul.");
		}
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::DELETE_SALDO, $entity);
		return parent::delete($entity);
	}

	/**
	 * @param PersistentEntity $entity
	 *
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::CREATE_SALDO, $entity);
		return parent::create($entity);
	}

	/**
	 * @param PersistentEntity $entity
	 *
	 * @return int
	 */
	public function update(PersistentEntity $entity) {
		CiviSaldoLogModel::instance()->log(CiviSaldoLogEnum::UPDATE_SALDO, $entity);
		return parent::update($entity);
	}
}
