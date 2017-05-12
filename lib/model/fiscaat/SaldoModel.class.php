<?php

use CsrDelft\Orm\Persistence\OrmMemcache;
use CsrDelft\Orm\DynamicEntityModel;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/fiscaat/CiviSaldoModel.class.php';
require_once 'model/fiscaat/CiviBestellingModel.class.php';

class SaldoModel extends PersistenceModel {
	const ORM = Saldo::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	/**
	 * Flot wil graag een timestamp in milliseconden, php kent timestamps in seconden
	 *
	 * @param $moment
	 * @return false|int
	 */
	private function flotTime($moment) {
		return strtotime($moment) * 1000;
	}

	public function getDataPointsForMaalCie($uid, $timespan) {
		if (!$this->magGrafiekZien($uid, "maalcie")) {
			return null;
		}
		$points = $this->find('uid = ? AND cie = "maalcie" AND moment > (NOW() - INTERVAL ? DAY)', array($uid, $timespan))->fetchAll();
		if (!empty($points)) {
			// herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
			$row = reset($points);
			$time = strtotime('-' . $timespan . ' days');
			$saldo = new Saldo();
			$saldo->saldo = $row->saldo;
			$saldo->moment = getDateTime($time + 3600);
			array_unshift($points, $saldo);
		}
		if (!empty($points)) {
			// Dupliceer het laatste punt met saldo 0 voor de overgang naar CiviSaldo
			$row = end($points);
			$saldo = new Saldo();
			$saldo->saldo = 0;
			$saldo->moment = $row->moment;
			array_push($points, $saldo);
		}

		return array(
			"label" => "MaalCie",
			"data" => $points,
			"threshold" => array("below" => 0, "color" => "red"),
			"lines" => array("steps" => true)
		);
	}

	public function getDataPointsForSocCie($uid, $timespan) {
		if (!$this->magGrafiekZien($uid)) {
			return null;
		}
		$model = DynamicEntityModel::makeModel('socCieKlanten');
		$klant = $model->findSparse(array('socCieId', 'saldo'), 'stekUID = ?', array($uid), null, null, 1)->fetch();
		if (!$klant) {
			return null;
		}
		$saldo = $klant->saldo;
		$data = $data = [];
		$model = DynamicEntityModel::makeModel('socCieBestelling');
		$bestellingen = $model->findSparse(array('tijd', 'totaal'), 'socCieId = ? AND deleted = FALSE AND tijd>(NOW() - INTERVAL ? DAY)', array($klant->socCieId, $timespan), null, 'tijd DESC');
		foreach ($bestellingen as $bestelling) {
			$data[] = array('moment' => $bestelling->tijd, 'saldo' => round($saldo / 100, 2));
			$saldo += $bestelling->totaal;
		}
		if (!empty($data)) {
			// herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
			// Pas op, soccie is omgedraaid van maalcie omdat saldo's op runtime berekend worden
			$row = end($data);
			$time = strtotime('-' . $timespan . ' days');
			array_push($data, array('moment' => getDateTime($time + 3600), 'saldo' => $row['saldo']));
		}
		if (!empty($data)) {
			// Dupliceer het laatste punt met saldo 0 voor de overgang naar CiviSaldo
			$row = reset($data);
			array_unshift($data, ['moment' => $row['moment'], 'saldo' => 0]);
		}
		$points = [];
		foreach ($data as $entry) {
			$saldo = new Saldo();
			$saldo->moment = $entry['moment'];
			$saldo->saldo = $entry['saldo'];
			$points[] = $saldo;
		}
		return array(
			"label" => "SocCie",
			"data" => $points,
			"threshold" => array("below" => 0, "color" => "red"),
			"lines" => array("steps" => true)
		);
	}

	public function getDataPointsForCiviSaldo($uid, $timespan) {
		if (!$this->magGrafiekZien($uid)) {
			return null;
		}
		$model = CiviSaldoModel::instance();
		$klant = $model->find('uid = ?', array($uid), null, null, 1)->fetch();
		if (!$klant) {
			return null;
		}
		$saldo = $klant->saldo;
		// Teken het huidige saldo
		$data = array(array($this->flotTime(getDateTime()), round($saldo / 100, 2)));
		$model = CiviBestellingModel::instance();
		$bestellingen = $model->find('uid = ? AND deleted = FALSE AND moment>(NOW() - INTERVAL ? DAY)', array($klant->uid, $timespan), null, 'moment DESC');
		foreach ($bestellingen as $bestelling) {
			$data[] = array($this->flotTime($bestelling->moment), round($saldo / 100, 2));
			$saldo += $bestelling->totaal;
		}
		if (!empty($data)) {
			// Dupliceer het eerste datapunt om grafiek te tekenen vanaf 0
			$row = end($data);
			array_push($data, array($row[0], 0));
		}
		return array(
			"label" => "CiviSaldo",
			"data" => array_reverse($data), // Keer de lijst om, flot laat anders veranderingen in de data 1-off zien
			"threshold" => array("below" => 0, "color" => "red"),
			"lines" => array("steps" => true)
		);
	}

	public function getDataPoints($uid, $timespan) {
		// array_filter haalt grafieken die niet gezien mogen worden of geen data hebben voor dit $timespan eruit.
		return array_filter(array(
			$this->getDataPointsForMaalCie($uid, $timespan),
			$this->getDataPointsForSocCie($uid, $timespan),
			$this->getDataPointsForCiviSaldo($uid, $timespan)
		));
	}

	public function magGrafiekZien($uid) {
		//mogen we uberhaupt een grafiek zien?
		return LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:SocCie,commissie:MaalCie');
	}
}
