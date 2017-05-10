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

		$oldData = $this->getDataPointsForOldMaalCie($uid, $timespan);

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

		if (!empty($data) && empty($oldData)) {
			// herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
			$row = end($data);
			$time = strtotime('-' . $timespan . ' days') + 3600;
			array_push($data, array($time * 1000, $row[1]));
		}

		return array(
			"label" => "MaalCie",
			"data" => array_merge($oldData, array_reverse($data)), // Keer de lijst om, flot laat anders veranderingen in de data 1-off zien
			"threshold" => array("below" => 0, "color" => "red"),
			"lines" => array("steps" => true)
		);
    }

    private function getDataPointsForOldMaalCie($uid, $timespan) {
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
			return $points;
		}

    public function getDataPointsForSocCie($uid, $timespan) {
        if (!$this->magGrafiekZien($uid, "soccie")) {
            return null;
        }
        $model = DynamicEntityModel::makeModel('socCieKlanten');
        $klant = $model->findSparse(array('socCieId', 'saldo'), 'stekUID = ?', array($uid), null, null, 1)->fetch();
        if (!$klant) {
            return null;
        }

        $saldo = $klant->saldo;
        $data = array(array('moment' => getDateTime(), 'saldo' => round($saldo / 100, 2)));
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

        $points = array();
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

    public function getDataPoints($uid, $timespan) {
        // array_filter haalt grafieken die niet gezien mogen worden eruit.
        return array_filter(array(
            $this->getDataPointsForMaalCie($uid, $timespan),
            $this->getDataPointsForSocCie($uid, $timespan)
        ));
    }

    public function magGrafiekZien($uid, $cie = null) {
        //mogen we uberhaupt een grafiek zien?
        if ($cie === null) {
            return LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:SocCie,commissie:MaalCie');
        }
        if (LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:' . $cie)) {
            return true;
        }
        return false;
    }
}
