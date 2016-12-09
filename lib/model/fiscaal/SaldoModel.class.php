<?php

class SaldoModel extends PersistenceModel {
    const ORM = 'Saldo';
    const DIR = 'fiscaal/';

    protected static $instance;

    public function getDataPointsForMaalCie($uid, $timespan) {
        if (!$this->magGrafiekZien($uid, "maalcie")) {
            return '';
        }
        $saldi = $this->find('uid = ? AND cie = "maalcie" AND moment > (NOW() - INTERVAL ? DAY)', array($uid, $timespan));

        $points = array();
        foreach ($saldi as $saldo) {
            $points[] = json_encode($saldo);
        }

        if (!empty($points)) {
            // herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
            $row = reset($points);
            $time = strtotime('-' . $timespan . ' days');
            $saldo = new Saldo();
            $saldo->saldo = $row->saldo;
            $saldo->moment = getDateTime($time + 3600);
            array_unshift($points, $saldo);
        }

        return '{
	"label": "maalcie", 
	"data": [ ' . implode(", ", $points) . ' ],
	"threshold": { "below": 0, "color": "red" },
	"lines": { "steps": true }
}';
    }

    public function getDataPointsForSocCie($uid, $timespan) {
        if (!$this->magGrafiekZien($uid, "soccie")) {
            return '';
        }
        $model = DynamicEntityModel::makeModel('socCieKlanten');
        $klant = $model->findSparse(array('socCieId', 'saldo'), 'stekUID = ?', array($uid), null, null, 1)->fetch();
        if (!$klant) {
            return '';
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
            $row = reset($data);
            $time = strtotime('-' . $timespan . ' days');
            array_unshift($data, array('moment' => getDateTime($time + 3600), 'saldo' => $row['saldo']));
        }

        $points = array();
        foreach ($data as $entry) {
            $saldo = new Saldo();
            $saldo->moment = $entry['moment'];
            $saldo->saldo = $entry['saldo'];
            $points[] = $saldo->jsonSerialize();
        }

        return '{
	"label": "soccie", 
	"data": [ ' . implode(", ", $points) . ' ],
	"threshold": { "below": 0, "color": "red" },
	"lines": { "steps": true }
}';
    }

    public function getDataPoints($uid, $timespan) {
        $series = array();

        $maalcieSaldi = $this->getDataPointsForMaalCie($uid, $timespan);
        if ($maalcieSaldi != '') {
            $series[] = $maalcieSaldi;
        }

        $soccieSaldi = $this->getDataPointsForSocCie($uid, $timespan);
        if ($soccieSaldi != '') {
            $series[] = $soccieSaldi;
        }

        return '[' . implode(', ', $series) . ']';
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